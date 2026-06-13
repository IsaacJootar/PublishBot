<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Language;

class ExportService
{
    /**
     * @param  array<int, array{title:string, body:string}>  $chapters
     */
    public function exportPremiumPdf(string $relPath, string $title, string $author, ?string $subtitle, array $chapters, string $brandColor = '#6C3CE1'): string
    {
        $pdf = Pdf::loadView('exports.premium-pdf', [
            'title' => $title,
            'author' => $author,
            'subtitle' => $subtitle,
            'chapters' => $chapters,
            'brandColor' => $brandColor,
        ])->setPaper('a4');

        $absolute = $this->ensurePath($relPath);
        $pdf->save($absolute);

        return $absolute;
    }

    /**
     * Strict KDP-ready DOCX.
     * - Times New Roman 12pt
     * - 1 inch margins
     * - Chapter titles as Heading 1, new page per chapter
     * - No headers/footers
     *
     * @param  array<int, array{title:string, body:string}>  $chapters
     */
    public function exportKdpDocx(string $relPath, string $title, string $author, ?string $subtitle, array $chapters): string
    {
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);
        $phpWord->getSettings()->setThemeFontLang(new Language(Language::EN_US));

        // 1-inch margins (1440 twips)
        $section = $phpWord->addSection([
            'marginTop' => 1440,
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);

        // Heading 1 = chapter title
        $phpWord->addTitleStyle(1, ['name' => 'Times New Roman', 'size' => 18, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 480]);

        // Title page
        $section->addText($title, ['name' => 'Times New Roman', 'size' => 28, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceBefore' => 2400]);
        if ($subtitle) {
            $section->addText($subtitle, ['name' => 'Times New Roman', 'size' => 16, 'italic' => true], ['alignment' => Jc::CENTER, 'spaceBefore' => 240]);
        }
        $section->addText('by '.$author, ['name' => 'Times New Roman', 'size' => 14], ['alignment' => Jc::CENTER, 'spaceBefore' => 720]);
        $section->addPageBreak();

        foreach ($chapters as $i => $chapter) {
            if ($i > 0) {
                $section->addPageBreak();
            }
            $section->addTitle($chapter['title'], 1);

            foreach ($this->paragraphs($chapter['body']) as $para) {
                $section->addText($para, ['name' => 'Times New Roman', 'size' => 12], [
                    'alignment' => Jc::BOTH,
                    'spaceAfter' => 200,
                    'lineHeight' => 1.5,
                ]);
            }
        }

        $absolute = $this->ensurePath($relPath);
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($absolute);

        return $absolute;
    }

    /**
     * Master editable DOCX with cover, TOC, and styled chapters.
     *
     * @param  array<int, array{title:string, body:string}>  $chapters
     */
    public function exportMasterDocx(string $relPath, string $title, string $author, ?string $subtitle, array $chapters, string $brandColor = '6C3CE1'): string
    {
        $brandColor = ltrim($brandColor, '#');

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $phpWord->addTitleStyle(1, ['name' => 'Calibri', 'size' => 20, 'bold' => true, 'color' => $brandColor], ['spaceBefore' => 480, 'spaceAfter' => 240]);

        $section = $phpWord->addSection([
            'marginTop' => 1440,
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);

        // Cover
        $section->addText($title, ['name' => 'Calibri', 'size' => 36, 'bold' => true, 'color' => $brandColor], ['alignment' => Jc::CENTER, 'spaceBefore' => 2880]);
        if ($subtitle) {
            $section->addText($subtitle, ['name' => 'Calibri', 'size' => 18, 'italic' => true, 'color' => '666666'], ['alignment' => Jc::CENTER, 'spaceBefore' => 300]);
        }
        $section->addText('by '.$author, ['name' => 'Calibri', 'size' => 14, 'color' => '333333'], ['alignment' => Jc::CENTER, 'spaceBefore' => 720]);
        $section->addPageBreak();

        // TOC
        $section->addText('Contents', ['name' => 'Calibri', 'size' => 22, 'bold' => true, 'color' => $brandColor], ['spaceAfter' => 360]);
        $section->addTOC(['name' => 'Calibri', 'size' => 12]);
        $section->addPageBreak();

        // Chapters
        foreach ($chapters as $i => $chapter) {
            if ($i > 0) {
                $section->addPageBreak();
            }
            $section->addTitle($chapter['title'], 1);

            foreach ($this->paragraphs($chapter['body']) as $para) {
                $section->addText($para, ['name' => 'Calibri', 'size' => 11], [
                    'alignment' => Jc::BOTH,
                    'spaceAfter' => 180,
                    'lineHeight' => 1.4,
                ]);
            }
        }

        // Footer with page numbers
        $footer = $section->addFooter();
        $footer->addPreserveText('{PAGE} / {NUMPAGES}', ['size' => 9, 'color' => '999999'], ['alignment' => Jc::CENTER]);

        $absolute = $this->ensurePath($relPath);
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($absolute);

        return $absolute;
    }

    /** Split a chapter body into paragraphs by blank lines. */
    private function paragraphs(string $body): array
    {
        $body = preg_replace("/\r\n|\r/", "\n", $body);
        $parts = preg_split("/\n\s*\n/", trim($body));

        return array_values(array_filter(array_map('trim', $parts ?: []), fn ($p) => $p !== ''));
    }

    /** Make sure parent directory exists; return absolute path. */
    private function ensurePath(string $relPath): string
    {
        $absolute = storage_path('app/'.$relPath);
        $dir = dirname($absolute);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $absolute;
    }
}
