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
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'paperSize' => 'A4',
            'marginTop' => 500,
            'marginBottom' => 500,
            'marginLeft' => 600,
            'marginRight' => 600,
        ]);

        $bold = ['name' => 'Times New Roman', 'bold' => true];
        $smallItalic = ['name' => 'Times New Roman', 'size' => 10, 'italic' => true];
        $center = ['alignment' => Jc::CENTER];

        $section->addText('Republic of the Philippines', $bold, $center);
        $section->addText('Department of Education', $bold, $center);
        $section->addText($data['school_name'], $bold, array_merge($center, ['spaceAfter' => 200]));
        $section->addText('CLASSROOM OBSERVATION TOOL', $bold + ['size' => 16], $center);
        $section->addText('(for ' . ($data['framework_label'] ?: 'the PPST') . ')', $smallItalic, $center);

        $this->addInfoTable($section, $data);
        $section->addText('', [], ['spaceAfter' => 120]);

        $this->addRatingTable($section, $data);
        $section->addText('', [], ['spaceAfter' => 120]);

        $this->addSummary($section, $data);
        $this->addPostConferenceSection($section, $data);
        $this->addAiSection($section, $data);
        $this->addSignatureBlock($section, $data);

        $footer = $section->addFooter();
        $footer->addText(
            'COT Document — ' . $data['teacher_name'] . ' — generated by ASPIRE on ' . now()->format('F d, Y'),
            ['name' => 'Times New Roman', 'size' => 8],
            $center
        );

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
        $indicators = $version->indicators()->get();

        $grouped = $this->groupIndicatorsByDomain($indicators);
        $scaleValues = array_keys($version->ratingScale());
        $scaleValues = $scaleValues ?: [6, 5, 4, 3, 2];

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'paperSize' => 'A4',
            'marginTop' => 500,
            'marginBottom' => 500,
            'marginLeft' => 600,
            'marginRight' => 600,
        ]);

        $bold = ['name' => 'Times New Roman', 'bold' => true];
        $smallItalic = ['name' => 'Times New Roman', 'size' => 10, 'italic' => true];
        $center = ['alignment' => Jc::CENTER];

        $section->addText('Republic of the Philippines', $bold, $center);
        $section->addText('Department of Education', $bold, $center);
        $section->addText('', $bold, $center);
        $section->addText('CLASSROOM OBSERVATION TOOL', $bold + ['size' => 16], $center);
        $section->addText('(for ' . ($version->framework ?: 'the PPST') . ')', $smallItalic, $center);

        $this->addBlankInfoTable($section);
        $section->addText('', [], ['spaceAfter' => 120]);

        $this->addTemplateRatingTable($section, $grouped, $scaleValues);
        $section->addText('', [], ['spaceAfter' => 120]);

        $this->addTemplateSignatureBlock($section);

        $footer = $section->addFooter();
        $footer->addText(
            'COT Template — ' . $version->label . ' — generated by ASPIRE',
            ['name' => 'Times New Roman', 'size' => 8],
            $center
        );

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
            $style = array_merge($this->cellStyle(), ['shading' => ['fill' => 'D9E2F3'], 'valign' => 'center']);
            $table->addCell($widths[$i], $style)->addText($label, $headerStyle, $center);
        }

        $index = 0;
        foreach ($groups as $group) {
            $table->addRow();
            $groupCell = $table->addCell(array_sum($widths), array_merge($this->cellStyle(), [
                'gridSpan' => count($widths),
                'shading' => ['fill' => 'B4C7E7'],
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
            $style = array_merge($this->cellStyle(), ['shading' => ['fill' => 'D9E2F3'], 'valign' => 'center']);
            $table->addCell($widths[$i], $style)->addText($label, $headerStyle, $center);
        }

        $index = 0;
        foreach ($data['grouped_ratings'] as $group) {
            $table->addRow();
            $groupCell = $table->addCell(array_sum($widths), array_merge($this->cellStyle(), [
                'gridSpan' => count($widths),
                'shading' => ['fill' => 'B4C7E7'],
            ]));
            $groupCell->addText($group['domain'], ['name' => 'Times New Roman', 'size' => 9, 'bold' => true]);

            foreach ($group['items'] as $rating) {
                $index++;
                $table->addRow();

                $table->addCell($widths[0], $this->cellStyle())->addText((string) $index, $headerStyle, $center);

                $indicatorCell = $table->addCell($widths[1], $this->cellStyle());
                $run = $indicatorCell->addTextRun(['name' => 'Times New Roman', 'size' => 9]);
                $run->addText($rating->indicator_code . '. ', ['bold' => true, 'size' => 9]);
                $run->addText($rating->indicator);

                foreach ([6, 5, 4, 3, 2] as $value) {
                    $marked = !$rating->not_observed && (int) $rating->rating === $value;
                    $style = $this->cellStyle();
                    if ($marked) {
                        $style['shading'] = ['fill' => 'D9E2F3'];
                    }
                    $cell = $table->addCell($widths[2 + (6 - $value)], $style);
                    $cell->addText($marked ? 'X' : '', ['name' => 'Times New Roman', 'size' => 9, 'bold' => true], $center);
                }

                $noCell = $table->addCell($widths[7], $this->cellStyle());
                $noCell->addText($rating->not_observed ? 'X' : '', ['name' => 'Times New Roman', 'size' => 9, 'bold' => true], $center);

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
}
