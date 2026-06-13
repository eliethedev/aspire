<?php

namespace App\AI\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentExtractorService
{
    public function extractText(string $filePath): string
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            Log::warning("DocumentExtractor: File not found or unreadable: {$filePath}");
            return '';
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        try {
            return match ($extension) {
                'docx' => $this->extractDocx($filePath),
                'pdf'  => $this->extractPdf($filePath),
                'pptx' => $this->extractPptx($filePath),
                'xlsx' => $this->extractXlsx($filePath),
                'txt', 'md', 'csv' => $this->extractPlainText($filePath),
                default => $this->extractFallback($filePath),
            };
        } catch (\Exception $e) {
            Log::error("DocumentExtractor: Extraction failed for {$filePath}: " . $e->getMessage());
            return '';
        }
    }

    protected function extractDocx(string $path): string
    {
        try {
            $phpWord = PhpWordIOFactory::load($path);
        } catch (\Exception $e) {
            return $this->extractDocxRaw($path);
        }

        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text .= $this->extractPhpWordElementText($element);
            }
        }

        return $this->cleanText($text);
    }

    protected function extractPhpWordElementText($element): string
    {
        if (method_exists($element, 'getText')) {
            return $element->getText() . "\n";
        }

        if (method_exists($element, 'getElements')) {
            $text = '';
            foreach ($element->getElements() as $child) {
                if (method_exists($child, 'getText')) {
                    $text .= $child->getText() . "\n";
                }
            }
            return $text;
        }

        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            $text = '';
            foreach ($element->getElements() as $part) {
                if ($part instanceof \PhpOffice\PhpWord\Element\Text) {
                    $text .= $part->getText();
                }
                if ($part instanceof \PhpOffice\PhpWord\Element\Link) {
                    $text .= $part->getText();
                }
            }
            return $text . "\n";
        }

        if ($element instanceof \PhpOffice\PhpWord\Element\Title) {
            return $element->getText() . "\n";
        }

        if ($element instanceof \PhpOffice\PhpWord\Element\ListItem) {
            return "- " . $element->getText() . "\n";
        }

        if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            return $this->extractPhpWordTable($element);
        }

        return '';
    }

    protected function extractPhpWordTable(\PhpOffice\PhpWord\Element\Table $table): string
    {
        $text = '';
        foreach ($table->getRows() as $row) {
            $cells = [];
            foreach ($row->getCells() as $cell) {
                $cellText = '';
                foreach ($cell->getElements() as $cellElement) {
                    if (method_exists($cellElement, 'getText')) {
                        $cellText .= $cellElement->getText() . ' ';
                    }
                }
                $cells[] = trim($cellText);
            }
            $text .= implode(' | ', $cells) . "\n";
        }
        return $text;
    }

    protected function extractDocxRaw(string $path): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            return '';
        }

        $xml = simplexml_load_string($xml);
        if (!$xml) {
            return '';
        }

        $namespaces = $xml->getNamespaces(true);
        $ns = $namespaces['w'] ?? '';

        $textParts = [];
        $paragraphs = $xml->children($ns)->body->children($ns)->p ?? [];
        foreach ($paragraphs as $paragraph) {
            $parts = [];
            $runs = $paragraph->children($ns)->r ?? [];
            foreach ($runs as $run) {
                $t = $run->children($ns)->t ?? null;
                if ($t !== null) {
                    $parts[] = (string)$t;
                }
            }
            if ($parts) {
                $textParts[] = implode('', $parts);
            }
        }

        return $this->cleanText(implode("\n", $textParts));
    }

    protected function extractPdf(string $path): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        return $this->cleanText($pdf->getText());
    }

    protected function extractPptx(string $path): string
    {
        $presentation = PresentationIOFactory::load($path);
        $text = '';

        foreach ($presentation->getAllSlides() as $slideIndex => $slide) {
            $text .= "--- Slide " . ($slideIndex + 1) . " ---\n";
            foreach ($slide->getShapeCollection() as $shape) {
                if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
                    $text .= $shape->getPlainText() . "\n\n";
                }
                if ($shape instanceof \PhpOffice\PhpPresentation\Shape\Table) {
                    $text .= $this->extractPptxTable($shape) . "\n";
                }
            }
        }

        return $this->cleanText($text);
    }

    protected function extractPptxTable(\PhpOffice\PhpPresentation\Shape\Table $table): string
    {
        $text = '';
        foreach ($table->getRows() as $row) {
            $cells = [];
            foreach ($row->getCells() as $cell) {
                $cells[] = $cell->getPlainText();
            }
            $text .= implode(' | ', $cells) . "\n";
        }
        return $text;
    }

    protected function extractXlsx(string $path): string
    {
        $spreadsheet = SpreadsheetIOFactory::load($path);
        $text = '';

        foreach ($spreadsheet->getWorksheetIterator() as $worksheetIndex => $worksheet) {
            $text .= "--- Sheet: " . $worksheet->getTitle() . " ---\n";
            foreach ($worksheet->getRowIterator() as $row) {
                $cellTexts = [];
                foreach ($row->getCellIterator() as $cell) {
                    $value = $cell->getValue();
                    if ($value !== null) {
                        $cellTexts[] = (string) $value;
                    }
                }
                if (!empty($cellTexts)) {
                    $text .= implode(' | ', $cellTexts) . "\n";
                }
            }
            $text .= "\n";
        }

        return $this->cleanText($text);
    }

    protected function extractPlainText(string $path): string
    {
        $content = file_get_contents($path);
        return $content !== false ? $this->cleanText($content) : '';
    }

    protected function extractFallback(string $path): string
    {
        $content = file_get_contents($path);
        return $content !== false ? $this->cleanText($content) : '';
    }

    protected function cleanText(string $text): string
    {
        $text = preg_replace('/[^\S\n]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim(mb_substr($text, 0, 15000));
    }
}
