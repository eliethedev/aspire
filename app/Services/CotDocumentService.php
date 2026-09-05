<?php

namespace App\Services;

use App\Models\CotIndicatorVersion;
use App\Models\Observation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class CotDocumentService
{
    public function __construct()
    {
        Settings::setOutputEscapingEnabled(true);
    }

    public const DISK = 'public';

    public const DIRECTORY = 'cot_documents';

    /**
     * Validate that the observation has everything needed to build a COT document.
     *
     * @return array<int, string> list of human-readable errors (empty when OK)
     */
    public function canGenerate(Observation $observation): array
    {
        $errors = [];

        if (!$observation->observee) {
            $errors[] = 'The teacher being observed is missing from this observation.';
        }

        if (!$observation->observer) {
            $errors[] = 'The observer record is missing from this observation.';
        }

        if (!$observation->cotRatings()->exists()) {
            $errors[] = 'No COT ratings have been recorded for this observation yet.';
        }

        return $errors;
    }

    /**
     * The sanitized base filename (without extension) for a COT document.
     */
    public function filenameFor(Observation $observation): string
    {
        $name = $this->sanitize($this->teacherName($observation));
        $schoolYear = $this->sanitize($observation->school_year ?? 'No-SY');
        $date = $observation->observation_date?->format('Y-m-d') ?? 'No-Date';

        return sprintf('COT_%s_%s_%s', $name, $schoolYear, $date);
    }

    /**
     * Sanitize a value for use inside a filename.
     */
    public function sanitize(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '-', trim($value)) ?? 'Teacher';
        return trim($value, '-') ?: 'Teacher';
    }

    /**
     * Generate the DOCX COT document for a completed observation.
     *
     * Persists the file to storage and records its path on the observation
     * (regenerating safely overwrites the previous file). Returns the stored path.
     *
     * @throws \RuntimeException when required data is missing
     */
    public function generateDocument(Observation $observation): string
    {
        $errors = $this->canGenerate($observation);
        if ($errors) {
            throw new \RuntimeException(implode(' ', $errors));
        }

        $phpWord = $this->buildDocx($observation);
        $filename = $this->filenameFor($observation) . '.docx';

        $tempPath = tempnam(sys_get_temp_dir(), 'cot_doc_');
        IOFactory::createWriter($phpWord, 'Word2007')->save($tempPath);

        try {
            $path = Storage::disk(self::DISK)->putFileAs(self::DIRECTORY, $tempPath, $filename);
        } finally {
            @unlink($tempPath);
        }

        $observation->update([
            'cot_document_path' => $path,
            'cot_document_generated_at' => now(),
        ]);

        return $path;
    }

    /**
     * Check whether a generated COT document currently exists for the observation.
     */
    public function hasDocument(Observation $observation): bool
    {
        return !empty($observation->cot_document_path)
            && Storage::disk(self::DISK)->exists($observation->cot_document_path);
    }

    /**
     * Get the disk path of the generated document, or null.
     */
    public function documentPath(Observation $observation): ?string
    {
        return $this->hasDocument($observation) ? $observation->cot_document_path : null;
    }

    /**
     * Build the data array shared by the DOCX writer, PDF and HTML preview.
     */
    public function viewData(Observation $observation): array
    {
        $observation->loadMissing([
            'observee.user', 'observee.school',
            'observer', 'cotRatings.aiFeedback', 'postConference',
            'cotIndicatorVersion',
        ]);

        $ratings = $observation->cotRatings;

        $rated = $ratings->filter(fn ($r) => !$r->not_observed && $r->rating !== null);
        $total = $rated->sum('rating');
        $ratedCount = $rated->count();
        $average = $observation->overall_score !== null
            ? (float) $observation->overall_score
            : ($ratedCount > 0 ? round($total / $ratedCount, 2) : 0);
        $percentage = $ratedCount > 0 ? round(($average / 6) * 100, 2) : 0;

        return [
            'observation' => $observation,
            'school_name' => $this->schoolName($observation),
            'teacher_name' => $this->teacherName($observation),
            'teacher_position' => $this->positionOf($observation->observee),
            'observer_name' => $this->observerName($observation),
            'observer_position' => $this->positionOf($observation->observer),
            'date_label' => $observation->observation_date?->format('F d, Y') ?? '____________',
            'time_label' => $this->timeLabel($observation),
            'grade_section' => $observation->grade_level ?: '____________',
            'subject' => $observation->subject ?: '____________',
            'school_year' => $observation->school_year ?: '____________',
            'quarter' => $observation->quarter ?: '____________',
            'observation_number' => $observation->observation_number ?: '____________',
            'framework_label' => $this->frameworkLabel($observation),
            'grouped_ratings' => $this->groupByDomain($ratings),
            'ratings' => $ratings,
            'post_conference' => $observation->postConference,
            'ai_feedbacks' => $ratings
                ->map(fn ($r) => $r->aiFeedback)
                ->filter(fn ($f) => $f && $f->status === 'published'),
            'total' => $total,
            'average' => $average,
            'percentage' => $percentage,
            'rated_count' => $ratedCount,
        ];
    }

    /**
     * Build the Word2007 (DOCX) document replicating the official COT layout.
     */
    public function buildDocx(Observation $observation): PhpWord
    {
        $data = $this->viewData($observation);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(9);

        $section = $phpWord->addSection([
            'orientation' => 'portrait',
            'paperSize' => 'A4',
            // Closely follows the supplied official Teacher I-III COT sheet.
            'marginTop' => 300,
            'marginBottom' => 650,
            'marginLeft' => 720,
            'marginRight' => 720,
        ]);

        $this->addOfficialCotHeader($section, $data['school_year']);
        $this->addObservationInfo($section, $data, false);
        $this->addOfficialDirections($section);
        $this->addOfficialRatingTable($section, $data['ratings']);
        $this->addOtherCommentsBlock($section);
        $this->addOfficialSignatureBlock($section, $data);

        return $phpWord;
    }

    /**
     * Download filename for a blank COT template of the given version.
     */
    public function templateFilename(CotIndicatorVersion $version): string
    {
        $label = $this->sanitize($version->label ?: 'COT');
        $sy = $this->sanitize($version->school_year ?: 'No-SY');

        return sprintf('COT-Template_%s_SY%s', $label, $sy);
    }

    /**
     * Build a blank, printable COT template (Word2007) for the given indicator
     * version. Only the indicator list is filled in — all other fields are left
     * blank for manual completion.
     */
    public function buildTemplateDocx(CotIndicatorVersion $version): PhpWord
    {
        $indicators = $version->indicators()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(9);

        $section = $phpWord->addSection([
            'orientation' => 'portrait',
            'paperSize' => 'A4',
            'marginTop' => 300,
            'marginBottom' => 650,
            'marginLeft' => 720,
            'marginRight' => 720,
        ]);

        $this->addOfficialCotHeader($section, $version->school_year ?: '____________');
        $this->addObservationInfo($section, [
            'teacher_name' => '______________________________________',
            'teacher_position' => '________________',
            'date_label' => '__________________________',
            'time_label' => '',
            'grade_section' => '',
            'subject' => '__________________________________________________________',
            'quarter' => '',
            'school_year' => $version->school_year ?: '________________',
            'observation_number' => '',
        ], true);
        $this->addOfficialDirections($section);
        $this->addOfficialTemplateRatingTable($section, $indicators);
        $this->addOtherCommentsBlock($section);
        $this->addOfficialTemplateSignatureBlock($section);

        return $phpWord;
    }

    /**
     * Build the PDF version of the COT document.
     */
    public function generatePdf(Observation $observation): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('reports.cot-document', $this->viewData($observation))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('isFontSubsettingEnabled', true);
    }


    private function addOfficialCotHeader(\PhpOffice\PhpWord\Element\Section $section, string $schoolYear): void
    {
        $center = ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0];

        $logoPath = public_path('images/kagawaran ng edukasyon logo.png');
        if (file_exists($logoPath)) {
            $section->addImage($logoPath, [
                'width' => 80,
                'height' => 80,
                'alignment' => Jc::CENTER,
            ]);
        }

        $section->addText('PERFORMANCE MANAGEMENT AND EVALUATION SYSTEM (PMES)', ['name' => 'Arial', 'size' => 11, 'bold' => true], $center);
        $section->addText('FOR TEACHERS', ['name' => 'Arial', 'size' => 10, 'bold' => true], $center);
        $section->addText('(SY ' . $schoolYear . ')', ['name' => 'Arial', 'size' => 10, 'bold' => true], $center);
        $section->addTextBreak(1);
        $section->addText('TEACHER I-III', ['name' => 'Arial', 'size' => 13, 'bold' => true], $center);
        $section->addText('CLASSROOM OBSERVATION TOOL (COT) –', ['name' => 'Arial', 'size' => 12, 'bold' => true], $center);
        $section->addText('RATING SHEET', ['name' => 'Arial', 'size' => 12, 'bold' => true], $center);
        $section->addTextBreak(1);
    }

    private function addObservationInfo(\PhpOffice\PhpWord\Element\Section $section, array $data, bool $blank): void
    {
        $table = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'cellDefaultHeight' => 200,
        ]);
        $table->addRow();
        $table->addCell(7000, ['borderSize' => 0])->addText(
            'OBSERVER: ' . ($blank ? '_______________________________________________' : ($data['observer_name'] ?? '_______________________________________________')),
            ['name' => 'Arial', 'size' => 9]
        );
        $table->addCell(6000, ['borderSize' => 0])->addText(
            'DATE: ' . ($blank ? '__________________________' : ($data['date_label'] ?? '__________________________')),
            ['name' => 'Arial', 'size' => 9]
        );

        $table->addRow();
        $table->addCell(7000, ['borderSize' => 0])->addText(
            'TEACHER OBSERVED: ' . ($blank ? '______________________________________' : ($data['teacher_name'] ?? '______________________________________')),
            ['name' => 'Arial', 'size' => 9]
        );
        $table->addCell(6000, ['borderSize' => 0])->addText(
            'QUARTER: ' . ($blank ? '____________' : ($data['quarter'] ?? '____________')),
            ['name' => 'Arial', 'size' => 9]
        );

        $table->addRow();
        $table->addCell(14000, ['borderSize' => 0])->addText(
            'SUBJECT & GRADE LEVEL TAUGHT: ' . ($blank ? '______________________________________________________________________' : trim(($data['subject'] ?? '') . ' — ' . ($data['grade_section'] ?? ''))),
            ['name' => 'Arial', 'size' => 9]
        );

        $table->addRow();
        $table->addCell(14000, ['borderSize' => 0])->addText(
            'OBSERVATION:  1st □     2nd □',
            ['name' => 'Arial', 'size' => 9]
        );
        $section->addTextBreak(1);
    }

    private function addOfficialDirections(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $section->addText('DIRECTIONS FOR THE OBSERVERS:', ['name' => 'Arial', 'size' => 9, 'bold' => true]);
        $directions = [
            '1. Rate each item on the checklist according to how well the teacher performed during the classroom observation. Mark the appropriate column with a (✓) symbol.',
            '2. For indicators not applicable for the classroom observation period, place ‘N/A’.',
            '3. Each indicator is assessed on an individual basis, regardless of its relationship to other indicators.',
            '4. For schools with only one observer, this form will serve as the final rating sheet.',
        ];
        foreach ($directions as $direction) {
            $section->addText($direction, ['name' => 'Arial', 'size' => 8.5], ['spaceAfter' => 0]);
        }
        $section->addTextBreak(1);
    }

    private function addOfficialRatingTable(\PhpOffice\PhpWord\Element\Section $section, $ratings): void
    {
        $widths = [5800, 620, 620, 620, 620, 620, 620, 2200];
        $table = $section->addTable($this->gridStyle());
        $headers = ['INDICATORS', '2', '3', '4', '5', '6', 'NO*', 'COMMENTS'];
        $headerStyle = ['name' => 'Arial', 'size' => 8.5, 'bold' => true];
        $center = ['alignment' => Jc::CENTER, 'valign' => 'center'];

        $table->addRow();
        foreach ($headers as $i => $label) {
            $cell = $table->addCell($widths[$i], array_merge($this->cellStyle(), ['valign' => 'center']));
            $cell->addText($label, $headerStyle, $center);
        }

        foreach ($ratings as $rating) {
            $table->addRow();
            $indicatorStyle = $rating->isNotApplicable()
                    ? array_merge($this->cellStyle(), ['shading' => ['fill' => 'F2F2F2']])
                    : $this->cellStyle();
            $indicatorCell = $table->addCell($widths[0], $indicatorStyle);
            $run = $indicatorCell->addTextRun(['name' => 'Arial', 'size' => 8.2]);
            $run->addText($rating->indicator . ' ', ['size' => 8.2]);
            $run->addText('(' . $rating->indicator_code . ')', ['size' => 8.2]);
            if ($rating->isNotApplicable()) {
                $run->addText(' (Not Applicable)', ['italic' => true, 'size' => 8]);
            }

            foreach ([2, 3, 4, 5, 6] as $value) {
                $marked = !$rating->not_observed && !$rating->not_applicable && (int) $rating->rating === $value;
                $table->addCell($widths[1], $this->cellStyle())
                    ->addText($marked ? '✓' : '', ['name' => 'Arial', 'size' => 9, 'bold' => true], $center);
            }

            $noLabel = $rating->isNotApplicable() ? 'N/A' : ($rating->not_observed ? '✓' : '');
            $table->addCell($widths[6], $this->cellStyle())
                ->addText($noLabel, ['name' => 'Arial', 'size' => 9, 'bold' => true], $center);

            $commentText = $rating->comments ?? '';
            $table->addCell($widths[7], $this->cellStyle())
                ->addText($commentText, ['name' => 'Arial', 'size' => 7.5, 'italic' => true], ['valign' => 'top']);
        }
    }

    private function addOfficialTemplateRatingTable(\PhpOffice\PhpWord\Element\Section $section, $indicators): void
    {
        $widths = [5800, 620, 620, 620, 620, 620, 620, 2200];
        $table = $section->addTable($this->gridStyle());
        $headers = ['INDICATORS', '2', '3', '4', '5', '6', 'NO*', 'COMMENTS'];
        $headerStyle = ['name' => 'Arial', 'size' => 8.5, 'bold' => true];
        $center = ['alignment' => Jc::CENTER, 'valign' => 'center'];

        $table->addRow();
        foreach ($headers as $i => $label) {
            $table->addCell($widths[$i], array_merge($this->cellStyle(), ['shading' => ['fill' => 'C6EFCE'], 'valign' => 'center']))
                ->addText($label, $headerStyle, $center);
        }

        foreach ($indicators as $indicator) {
            $table->addRow();
            $indicatorCell = $table->addCell($widths[0], $this->cellStyle());
            $indicatorCell->addText(
                $indicator->description . ' (' . $indicator->code . ')',
                ['name' => 'Arial', 'size' => 8.2]
            );
            foreach ([2, 3, 4, 5, 6] as $value) {
                $table->addCell($widths[1], $this->cellStyle());
            }
            $table->addCell($widths[6], $this->cellStyle());
            $table->addCell($widths[7], $this->cellStyle());
        }
    }

    private function addOtherCommentsBlock(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $section->addTextBreak(1);
        $section->addText('OTHER COMMENTS:', ['name' => 'Arial', 'size' => 9, 'bold' => true]);
        $section->addText('');
        $section->addText('__________________________________________________________________________________________', ['name' => 'Arial', 'size' => 8.5]);
        $section->addTextBreak(1);
        $section->addText('* NO stands for Not Observed which automatically gets a rating of 2. \'N/A\' means the indicator is Not Applicable and is excluded from the overall rating.', ['name' => 'Arial', 'size' => 8, 'italic' => true]);
        $section->addTextBreak(1);
    }

    private function addOfficialSignatureBlock(\PhpOffice\PhpWord\Element\Section $section, array $data): void
    {
        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $table->addRow();
        $pairs = [
            ['observer' => $data['observer_name'] ?? '', 'label' => 'Signature over Printed Name of the Observer'],
            ['observer' => $data['teacher_name'] ?? '', 'label' => 'Signature over Printed Name of the Teacher'],
        ];
        foreach ($pairs as $pair) {
            $cell = $table->addCell(7000, ['valign' => 'center']);
            $cell->addText('____________________________________', ['name' => 'Arial', 'size' => 9], ['alignment' => Jc::CENTER]);
            if ($pair['observer']) {
                $cell->addText($pair['observer'], ['name' => 'Arial', 'size' => 8.5], ['alignment' => Jc::CENTER]);
            }
            $cell->addText($pair['label'], ['name' => 'Arial', 'size' => 8, 'italic' => true], ['alignment' => Jc::CENTER]);
        }
    }

    private function addOfficialTemplateSignatureBlock(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $table->addRow();
        foreach (['Signature over Printed Name of the Observer', 'Signature over Printed Name of the Teacher'] as $label) {
            $cell = $table->addCell(7000, ['valign' => 'center']);
            $cell->addText('____________________________________', ['name' => 'Arial', 'size' => 9], ['alignment' => Jc::CENTER]);
            $cell->addText($label, ['name' => 'Arial', 'size' => 8, 'italic' => true], ['alignment' => Jc::CENTER]);
        }
    }
    private function addInfoTable(\PhpOffice\PhpWord\Element\Section $section, array $data): void
    {
        $table = $section->addTable($this->gridStyle());
        $table->addRow();

        $this->infoCell($table, 'Name of Teacher:', $data['teacher_name']);
        $this->infoCell($table, 'Position:', $data['teacher_position'] ?: '____________');

        $table->addRow();
        $this->infoCell($table, 'Date of Observation:', $data['date_label']);
        $this->infoCell($table, 'Time:', $data['time_label'] ?: '____________');

        $table->addRow();
        $this->infoCell($table, 'Subject:', $data['subject']);
        $this->infoCell($table, 'Grade & Section:', $data['grade_section']);

        $table->addRow();
        $this->infoCell($table, 'School:', $data['school_name']);
        $this->infoCell($table, 'Quarter:', 'Quarter ' . $data['quarter']);

        $table->addRow();
        $this->infoCell($table, 'School Year:', $data['school_year']);
        $this->infoCell($table, 'Observation No.:', 'Observation ' . $data['observation_number']);

        $table->addRow();
        $this->infoCell($table, 'Name of Observer:', $data['observer_name']);
        $this->infoCell($table, 'Observer Position:', $data['observer_position'] ?: '____________');
    }

    private function infoCell(\PhpOffice\PhpWord\Element\Table $table, string $label, string $value): void
    {
        $table->addCell(2500, array_merge($this->cellStyle(), ['shading' => ['fill' => 'F2F2F2']]))
            ->addText($label, ['name' => 'Times New Roman', 'size' => 10, 'bold' => true]);
        $table->addCell(5300, $this->cellStyle())
            ->addText($value ?: '____________', ['name' => 'Times New Roman', 'size' => 10]);
    }

    private function addBlankInfoTable(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $table = $section->addTable($this->gridStyle());
        $table->addRow();

        $this->blankInfoCell($table, 'Name of Teacher:', '');
        $this->blankInfoCell($table, 'Position:', '');

        $table->addRow();
        $this->blankInfoCell($table, 'Date of Observation:', '');
        $this->blankInfoCell($table, 'Time:', '');

        $table->addRow();
        $this->blankInfoCell($table, 'Subject:', '');
        $this->blankInfoCell($table, 'Grade & Section:', '');

        $table->addRow();
        $this->blankInfoCell($table, 'School:', '');
        $this->blankInfoCell($table, 'Quarter:', '');

        $table->addRow();
        $this->blankInfoCell($table, 'School Year:', '');
        $this->blankInfoCell($table, 'Observation No.:', '');
    }

    private function blankInfoCell(\PhpOffice\PhpWord\Element\Table $table, string $label, string $value): void
    {
        $table->addCell(2500, array_merge($this->cellStyle(), ['shading' => ['fill' => 'F2F2F2']]))
            ->addText($label, ['name' => 'Times New Roman', 'size' => 10, 'bold' => true]);
        $table->addCell(5300, $this->cellStyle())
            ->addText($value ?: '____________', ['name' => 'Times New Roman', 'size' => 10]);
    }

    private function addTemplateRatingTable(
        \PhpOffice\PhpWord\Element\Section $section,
        array $groups,
        array $scaleValues
    ): void {
        $widths = [500, 4700, 700, 700, 700, 700, 700, 700, 5500];
        $headerStyle = ['name' => 'Times New Roman', 'size' => 9, 'bold' => true];
        $center = ['alignment' => Jc::CENTER];

        $table = $section->addTable($this->gridStyle());

        $table->addRow();
        $headers = ['#', 'PPST Indicators'];
        foreach ($scaleValues as $value) {
            $headers[] = (string) $value;
        }
        $headers[] = 'NO';
        $headers[] = 'Comments';

        foreach ($headers as $i => $label) {
            $style = array_merge($this->cellStyle(), ['shading' => ['fill' => 'C6EFCE'], 'valign' => 'center']);
            $table->addCell($widths[$i], $style)->addText($label, $headerStyle, $center);
        }

        $index = 0;
        foreach ($groups as $group) {
            $table->addRow();
            $groupCell = $table->addCell(array_sum($widths), array_merge($this->cellStyle(), [
                'gridSpan' => count($widths),
                'shading' => ['fill' => 'A9D18E'],
            ]));
            $groupCell->addText($group['domain'], ['name' => 'Times New Roman', 'size' => 9, 'bold' => true]);

            foreach ($group['items'] as $indicator) {
                $index++;
                $table->addRow();

                $table->addCell($widths[0], $this->cellStyle())->addText((string) $index, $headerStyle, $center);

                $indicatorCell = $table->addCell($widths[1], $this->cellStyle());
                $run = $indicatorCell->addTextRun(['name' => 'Times New Roman', 'size' => 9]);
                $run->addText($indicator->code . '. ', ['bold' => true, 'size' => 9]);
                $run->addText($indicator->description);

                foreach ($scaleValues as $j => $value) {
                    $table->addCell($widths[2 + $j], $this->cellStyle())
                        ->addText('', ['name' => 'Times New Roman', 'size' => 9], $center);
                }

                $noColumnIndex = 2 + count($scaleValues);
                $table->addCell($widths[$noColumnIndex], $this->cellStyle())
                    ->addText('', ['name' => 'Times New Roman', 'size' => 9], $center);

                $table->addCell($widths[count($widths) - 1], $this->cellStyle());
            }
        }
    }

    private function addTemplateSignatureBlock(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $section->addTextBreak(2);

        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $table->addRow();

        foreach (['Observed By', 'Observed'] as $role) {
            $cell = $table->addCell(7800, ['valign' => 'center']);
            $cell->addText('_______________________________________', ['name' => 'Times New Roman', 'size' => 10]);
            $cell->addText('', ['name' => 'Times New Roman', 'size' => 11, 'bold' => true], ['alignment' => Jc::CENTER]);
            $cell->addText($role . ' — Signature over Printed Name', ['name' => 'Times New Roman', 'size' => 9, 'italic' => true], ['alignment' => Jc::CENTER]);
            $cell->addText('', ['name' => 'Times New Roman', 'size' => 9], ['alignment' => Jc::CENTER]);
        }
    }

    private function groupIndicatorsByDomain($indicators): array
    {
        $groups = [];
        foreach ($indicators as $indicator) {
            $key = $indicator->domain ?: 'Other';
            $groups[$key][] = $indicator;
        }

        $result = [];
        foreach ($groups as $domain => $items) {
            $result[] = ['domain' => $domain, 'items' => $items];
        }

        return $result;
    }

    private function addRatingTable(\PhpOffice\PhpWord\Element\Section $section, array $data): void
    {
        $widths = [500, 4700, 700, 700, 700, 700, 700, 700, 5500];
        $headerStyle = ['name' => 'Times New Roman', 'size' => 9, 'bold' => true];
        $center = ['alignment' => Jc::CENTER];

        $table = $section->addTable($this->gridStyle());

        $table->addRow();
        $headers = ['#', 'PPST Indicators', '6', '5', '4', '3', '2', 'NO', 'Comments'];
        foreach ($headers as $i => $label) {
            $style = array_merge($this->cellStyle(), ['shading' => ['fill' => 'C6EFCE'], 'valign' => 'center']);
            $table->addCell($widths[$i], $style)->addText($label, $headerStyle, $center);
        }

        $index = 0;
        foreach ($data['grouped_ratings'] as $group) {
            $table->addRow();
            $groupCell = $table->addCell(array_sum($widths), array_merge($this->cellStyle(), [
                'gridSpan' => count($widths),
                'shading' => ['fill' => 'A9D18E'],
            ]));
            $groupCell->addText($group['domain'], ['name' => 'Times New Roman', 'size' => 9, 'bold' => true]);

            foreach ($group['items'] as $rating) {
                $index++;
                $table->addRow();

                $table->addCell($widths[0], $this->cellStyle())->addText((string) $index, $headerStyle, $center);

                $indicatorStyle = $rating->isNotApplicable()
                    ? array_merge($this->cellStyle(), ['shading' => ['fill' => 'F2F2F2']])
                    : $this->cellStyle();
                $indicatorCell = $table->addCell($widths[1], $indicatorStyle);
                $run = $indicatorCell->addTextRun(['name' => 'Times New Roman', 'size' => 9]);
                $run->addText($rating->indicator_code . '. ', ['bold' => true, 'size' => 9]);
                $run->addText($rating->indicator);
                if ($rating->isNotApplicable()) {
                    $run->addText(' (Not Applicable)', ['italic' => true, 'size' => 8]);
                }

                foreach ([6, 5, 4, 3, 2] as $value) {
                    $marked = !$rating->not_observed && !$rating->not_applicable && (int) $rating->rating === $value;
                    $style = $this->cellStyle();
                    if ($marked) {
                        $style['shading'] = ['fill' => 'C6EFCE'];
                    }
                    $cell = $table->addCell($widths[2 + (6 - $value)], $style);
                    $cell->addText($marked ? 'X' : '', ['name' => 'Times New Roman', 'size' => 9, 'bold' => true], $center);
                }

                $noLabel = $rating->isNotApplicable() ? 'N/A' : ($rating->not_observed ? 'X' : '');
                $noCell = $table->addCell($widths[7], $this->cellStyle());
                $noCell->addText($noLabel, ['name' => 'Times New Roman', 'size' => 9, 'bold' => true], $center);

                $commentsCell = $table->addCell($widths[8], $this->cellStyle());
                if ($rating->comments) {
                    $commentsCell->addText($rating->comments, ['name' => 'Times New Roman', 'size' => 9]);
                }
            }
        }
    }

    private function addSummary(\PhpOffice\PhpWord\Element\Section $section, array $data): void
    {
        $paragraph = $section->addTextRun();
        $paragraph->addText('Total: ', ['name' => 'Times New Roman', 'size' => 11, 'bold' => true]);
        $paragraph->addText(number_format($data['total'], 1) . '      ', ['name' => 'Times New Roman', 'size' => 11]);
        $paragraph->addText('Average: ', ['name' => 'Times New Roman', 'size' => 11, 'bold' => true]);
        $paragraph->addText(number_format($data['average'], 2) . ' / 6.00      ', ['name' => 'Times New Roman', 'size' => 11]);
        $paragraph->addText('Percentage: ', ['name' => 'Times New Roman', 'size' => 11, 'bold' => true]);
        $paragraph->addText(number_format($data['percentage'], 2) . '%', ['name' => 'Times New Roman', 'size' => 11]);
    }

    private function addPostConferenceSection(\PhpOffice\PhpWord\Element\Section $section, array $data): void
    {
        $pc = $data['post_conference'];
        $sections = [];

        if ($pc && $pc->conference_date) {
            $sections[] = ['Conference Date', $pc->conference_date->format('F d, Y')];
        }
        if ($pc && $pc->ai_comparison) {
            $sections[] = ['AI Comparison (Plan vs Actual)', is_array($pc->ai_comparison) ? json_encode($pc->ai_comparison) : $pc->ai_comparison];
        }
        if ($pc && $pc->feedback) {
            $sections[] = ['Feedback', $pc->feedback];
        }
        if ($pc && $pc->areas_for_improvement) {
            $sections[] = ['Areas for Improvement', $pc->areas_for_improvement];
        }
        if ($pc && $pc->prioritized_next_steps) {
            $sections[] = ['Prioritized Next Steps', $pc->prioritized_next_steps];
        }
        if ($pc && $pc->supervisor_notes) {
            $sections[] = ['Supervisor\'s Notes', $pc->supervisor_notes];
        }

        if (!$sections) {
            return;
        }

        $section->addText('POST-OBSERVATION CONFERENCE SUMMARY', ['name' => 'Times New Roman', 'size' => 11, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 80]);

        $table = $section->addTable($this->gridStyle());
        foreach ($sections as [$label, $value]) {
            $table->addRow();
            $table->addCell(3500, array_merge($this->cellStyle(), ['shading' => ['fill' => 'F2F2F2']]))
                ->addText($label, ['name' => 'Times New Roman', 'size' => 10, 'bold' => true]);
            $table->addCell(12100, $this->cellStyle())->addText($value, ['name' => 'Times New Roman', 'size' => 10]);
        }
    }

    private function addAiSection(\PhpOffice\PhpWord\Element\Section $section, array $data): void
    {
        if ($data['ai_feedbacks']->isEmpty()) {
            return;
        }

        $section->addText(
            'AI-GENERATED FEEDBACK (Reference Only — Does Not Replace Official Ratings)',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 80]
        );

        $table = $section->addTable($this->gridStyle());
        foreach ($data['ai_feedbacks'] as $feedback) {
            $table->addRow();
            $indicator = $feedback->cotRating?->indicator ?? '';
            $table->addCell(4500, array_merge($this->cellStyle(), ['shading' => ['fill' => 'FCE5CD']]))
                ->addText(($feedback->cotRating?->indicator_code ?? '') . '. ' . $indicator, ['name' => 'Times New Roman', 'size' => 9]);
            $table->addCell(11100, $this->cellStyle())->addText(
                $feedback->analysis ?: implode("\n", $feedback->recommendations ?? []),
                ['name' => 'Times New Roman', 'size' => 9]
            );
        }
    }

    private function addSignatureBlock(\PhpOffice\PhpWord\Element\Section $section, array $data): void
    {
        $section->addTextBreak(2);

        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $table->addRow();

        foreach ([
            ['Observed By', $data['observer_name'], $data['observer_position']],
            ['Observed', $data['teacher_name'], $data['teacher_position']],
        ] as [$role, $name, $position]) {
            $cell = $table->addCell(7800, ['valign' => 'center']);
            $cell->addText('_______________________________________', ['name' => 'Times New Roman', 'size' => 10]);
            $cell->addText($name, ['name' => 'Times New Roman', 'size' => 11, 'bold' => true], ['alignment' => Jc::CENTER]);
            $cell->addText($role . ' — Signature over Printed Name', ['name' => 'Times New Roman', 'size' => 9, 'italic' => true], ['alignment' => Jc::CENTER]);
            if ($position) {
                $cell->addText($position, ['name' => 'Times New Roman', 'size' => 9], ['alignment' => Jc::CENTER]);
            }
        }
    }

    private function gridStyle(): array
    {
        return [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMarginTop' => 40,
            'cellMarginBottom' => 40,
            'cellMarginLeft' => 80,
            'cellMarginRight' => 80,
        ];
    }

    private function cellStyle(): array
    {
        return ['borderSize' => 6, 'borderColor' => '000000'];
    }

    private function groupByDomain($ratings): array
    {
        $groups = [];
        foreach ($ratings as $rating) {
            $key = $rating->domain ?: 'Other';
            $groups[$key][] = $rating;
        }

        $result = [];
        foreach ($groups as $domain => $items) {
            $result[] = ['domain' => $domain, 'items' => $items];
        }

        return $result;
    }

    private function teacherName(Observation $observation): string
    {
        $observee = $observation->observee;
        if (!$observee) {
            return 'Unknown Teacher';
        }
        if (method_exists($observee, 'user')) {
            return $observee->user?->name ?? 'Unknown Teacher';
        }
        return $observee->name ?? 'Unknown Teacher';
    }

    private function observerName(Observation $observation): string
    {
        $observer = $observation->observer;
        if (!$observer) {
            return 'Unknown Observer';
        }
        if (method_exists($observer, 'user')) {
            return $observer->user?->name ?? 'Unknown Observer';
        }
        return $observer->name ?? 'Unknown Observer';
    }

    private function schoolName(Observation $observation): string
    {
        $observee = $observation->observee;
        if ($observee && method_exists($observee, 'school')) {
            if ($school = $observee->school) {
                return $school->name;
            }
        }
        return 'School Not Specified';
    }

    private function positionOf($model): ?string
    {
        if (!$model) {
            return null;
        }
        if (!empty($model->position)) {
            return $model->position;
        }
        return null;
    }

    private function frameworkLabel(Observation $observation): string
    {
        $version = $observation->cotIndicatorVersion;
        if ($version) {
            $parts = array_filter([
                $version->framework,
                $version->ratee_role ?: $version->ratee_position,
            ]);
            if ($parts) {
                return implode(' — ', $parts);
            }
        }

        return config('cot.default_label', 'Highly Proficient Teachers');
    }

    private function timeLabel(Observation $observation): string
    {
        $start = $observation->start_time ? \Illuminate\Support\Carbon::parse($observation->start_time)->format('h:i A') : null;
        $end = $observation->end_time ? \Illuminate\Support\Carbon::parse($observation->end_time)->format('h:i A') : null;

        if ($start && $end) {
            return $start . ' — ' . $end;
        }

        return $start ?: $end ?: '____________';
    }

    // ─── EPOC Document Generation ────────────────────────────────────────────

    public function generateEpocDocument(Observation $observation): string
    {
        $epoc = $observation->epocEvaluation;
        if (!$epoc) {
            throw new \RuntimeException('No EPOC evaluation found for this observation.');
        }

        $phpWord = $this->buildEpocDocx($observation);
        $filename = 'EPOC-' . $this->sanitize($this->teacherName($observation)) . '-' . ($observation->observation_date?->format('Y-m-d') ?? 'nodate') . '.docx';

        $tempPath = tempnam(sys_get_temp_dir(), 'epoc_doc_');
        IOFactory::createWriter($phpWord, 'Word2007')->save($tempPath);

        try {
            $path = Storage::disk(self::DISK)->putFileAs(self::DIRECTORY, $tempPath, $filename);
        } finally {
            @unlink($tempPath);
        }

        return $path;
    }

    public function buildEpocDocx(Observation $observation): PhpWord
    {
        $epoc = $observation->epocEvaluation;
        $observation->loadMissing(['observee.user', 'observee.school', 'observer', 'schoolHead']);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(9);

        $section = $phpWord->addSection([
            'orientation' => 'portrait',
            'paperSize' => 'A4',
            'marginTop' => 300,
            'marginBottom' => 650,
            'marginLeft' => 720,
            'marginRight' => 720,
        ]);

        // Header
        $center = ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0];
        $logoPath = public_path('images/kagawaran ng edukasyon logo.png');
        if (file_exists($logoPath)) {
            $section->addImage($logoPath, [
                'width' => 80, 'height' => 80,
                'alignment' => Jc::CENTER,
                'spaceAfter' => 40,
            ]);
        }

        $titleStyle = ['name' => 'Arial', 'size' => 12, 'bold' => true];
        $subtitleStyle = ['name' => 'Arial', 'size' => 9, 'bold' => true];
        $smallStyle = ['name' => 'Arial', 'size' => 8, 'italic' => true];

        $section->addText('Republic of the Philippines', $titleStyle, $center);
        $section->addText('Department of Education', $subtitleStyle, $center);
        $section->addText('Enhanced Post-Observation Conference (EPOC) Evaluation', $titleStyle, $center);
        $section->addText('Post Observation Conference Practices', $subtitleStyle, $center);
        $section->addText('', null, $center);

        // Info table
        $schoolYear = $observation->school_year ?: '____________';
        $dateLabel = $observation->observation_date?->format('F d, Y') ?? '____________';
        $teacherName = $this->teacherName($observation);
        $schoolHeadName = $epoc->school_head_name ?? $observation->schoolHead?->name ?? '____________';
        $schoolName = $this->schoolName($observation);

        $infoStyle = ['name' => 'Arial', 'size' => 9];
        $infoTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 60]);
        $infoRows = [
            ['School:', $schoolName, 'School Year:', $schoolYear],
            ['Teacher:', $teacherName, 'Date of Observation:', $dateLabel],
            ['School Head:', $schoolHeadName, '', ''],
        ];
        foreach ($infoRows as $row) {
            $infoTable->addRow();
            $infoTable->addCell(2000, ['bold' => true])->addText($row[0], $infoStyle);
            $infoTable->addCell(3500)->addText($row[1], $infoStyle);
            $infoTable->addCell(2200, ['bold' => true])->addText($row[2], $infoStyle);
            $infoTable->addCell(2500)->addText($row[3], $infoStyle);
        }

        $section->addText('');

        // Rating guide
        $section->addText('Guide to an Enhanced Post Observation Conference', $subtitleStyle);
        $section->addText('Carnegie Foundation for the Advancement of Teaching', $smallStyle);
        $section->addText('');

        $section->addText('Rating Scale:', $subtitleStyle);
        $section->addText('5 - Always | 4 - Often | 3 - Sometimes | 2 - Seldom | 1 - Never', $infoStyle);
        $section->addText('');

        // EPOC criteria
        $epocDomains = [
            'Establishing a Warm and Clear Opening of the Post Observation Conference' => [
                'Instructional Leader acknowledges teacher\'s time (Thanks the teacher for allowing him/her to observe a class)',
                'Instructional Leader states the purpose of the conversation',
                'Talks in a voice that is warm, friendly and sincere',
            ],
            'Focus on what\'s going well' => [
                'Congratulates teachers for doing a job well (cite specific instances or teacher behavior/activities that are worth mentioning. Refer to the STAR notes)',
                'Asks the teacher to clearly state the objectives of the lesson',
                'Paraphrases and affirms the teacher\'s lesson objective (Asks what the pupils are able to demonstrate at the end of the lesson)',
                'Asks the teacher what she did to teach the lesson',
                'Asks teacher what made him/her happy about the delivery of the lesson. The IL listens intently to what the teacher is saying',
                'The IL affirms what the teacher considered as things that went well in the delivery of the lesson',
                'The IL extends the positive focus in addition to what the teacher identified as what went well, citing additional specific things referring to the STAR notes',
            ],
            'Identify Challenges Facing the Teacher' => [
                'The IL asks the teacher to tell which part of the lesson she thinks did not go well',
                'The IL paraphrases teacher\'s message to check whether they have the same understanding',
                'The IL enables the teacher to tell additional parts that did not go well by citing specific instances recorded in the STAR notes',
                'The IL avoids diversion & stays focused on the issues/data/documentation at hand when teacher makes caustic statements',
                'The IL is able to verify the teacher\'s perception about the identified areas for improvement',
            ],
            'Generating Ideas for Addressing Teacher\'s Challenges' => [
                'The IL guides the teacher in identifying possible strategies in addressing the challenges',
                'The IL helps solve the problem by offering ideas for improvement if and when the teacher is not able to do so',
                'The IL connects the teacher to available and appropriate resources to help address the challenges',
                'The IL avoids compromising statements that provide an excuse for poor performance',
            ],
            'Prioritizing the Next Steps' => [
                'The Teacher and the principal reviews ideas for improvement and assign priority to possible options',
            ],
            'Ending the Post Observation Conference' => [
                'The IL makes the teacher agree on the next steps by asking the teacher to choose whose help he/she would want to ask to assist in improving the identified challenges',
                'The IL enables the teacher to make a commitment regarding the next steps identified',
                'The IL thanks the teacher for the conversation',
            ],
        ];

        $itemNum = 1;
        foreach ($epocDomains as $domain => $items) {
            // Domain header
            $section->addText('');
            $domainStyle = ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => '375623'];
            $section->addText($domain, $domainStyle);

            // Create rating table for this domain
            $table = $section->addTable([
                'borderSize' => 4,
                'borderColor' => 'AAAAAA',
                'cellMargin' => 40,
                'width' => 9500,
            ]);

            // Header row
            $headerStyle = ['name' => 'Arial', 'size' => 8, 'bold' => true, 'color' => 'FFFFFF'];
            $headerBg = '375623';
            $table->addRow();
            $table->addCell(800, ['bgColor' => $headerBg])->addText('#', $headerStyle, ['alignment' => Jc::CENTER]);
            $table->addCell(4500, ['bgColor' => $headerBg])->addText('Indicator', $headerStyle, ['alignment' => Jc::CENTER]);
            foreach ([5, 4, 3, 2, 1] as $val) {
                $table->addCell(800, ['bgColor' => $headerBg])->addText((string) $val, $headerStyle, ['alignment' => Jc::CENTER]);
            }

            // Item rows
            foreach ($items as $itemText) {
                // Find existing rating for this indicator
                $existingRating = $epoc->ratings->first(fn($r) => $r->indicator === $itemText);
                $selectedRating = $existingRating?->rating;

                $table->addRow();
                $cellStyle = ['name' => 'Arial', 'size' => 8];
                $table->addCell(800)->addText((string) $itemNum, $cellStyle, ['alignment' => Jc::CENTER]);
                $table->addCell(5000)->addText($itemText, $cellStyle);

                foreach ([5, 4, 3, 2, 1] as $val) {
                    $isSelected = $selectedRating == $val;
                    $cellStyle2 = ['name' => 'Arial', 'size' => 8, 'bold' => $isSelected];
                    $table->addCell(800, $isSelected ? ['bgColor' => 'C6EFCE'] : [])->addText(
                        $isSelected ? "[$val]" : (string) $val,
                        $cellStyle2,
                        ['alignment' => Jc::CENTER]
                    );
                }

                $itemNum++;
            }
        }

        // Narrative Observation
        if ($epoc->narrative_observation) {
            $section->addText('');
            $section->addText('Narrative Observation', $subtitleStyle);
            $section->addText($epoc->narrative_observation, $infoStyle);
        }

        // Agreement
        if ($epoc->agreement) {
            $section->addText('');
            $section->addText('Agreement', $subtitleStyle);
            $section->addText($epoc->agreement, $infoStyle);
        }

        // Overall Score
        if ($epoc->overall_score) {
            $section->addText('');
            $section->addText('Overall EPOC Score: ' . number_format($epoc->overall_score, 1) . ' / 5', $subtitleStyle);
        }

        // Signature block
        $section->addText('');
        $section->addText('');
        $sigTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $sigTable->addRow();
        $pairs = [
            ['name' => $this->observerName($observation), 'label' => 'Signature over Printed Name of the Supervisor'],
            ['name' => $schoolHeadName, 'label' => 'Signature over Printed Name of the School Head'],
        ];
        foreach ($pairs as $pair) {
            $cell = $sigTable->addCell(4500, ['valign' => 'center']);
            $cell->addText('____________________________________', ['name' => 'Arial', 'size' => 9], ['alignment' => Jc::CENTER]);
            if ($pair['name']) {
                $cell->addText($pair['name'], ['name' => 'Arial', 'size' => 8.5], ['alignment' => Jc::CENTER]);
            }
            $cell->addText($pair['label'], ['name' => 'Arial', 'size' => 8, 'italic' => true], ['alignment' => Jc::CENTER]);
        }

        return $phpWord;
    }
}