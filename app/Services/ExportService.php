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
    public function exportPremiumPdf(string $relPath, string $title, string $author, ?string $subtitle, array $chapters, string $brandColor = '#6C3CE1', ?string $productType = null, ?string $tagline = null): string
    {
        $pdf = Pdf::loadView('exports.premium-pdf', [
            'title' => $title,
            'author' => $author,
            'subtitle' => $subtitle,
            'chapters' => $chapters,
            'brandColor' => $brandColor,
            'productType' => $productType,
            'tagline' => $tagline,
        ])->setPaper('a4');

        $absolute = $this->ensurePath($relPath);
        $pdf->save($absolute);

        return $absolute;
    }

    /**
     * Render the dedicated digital-product PDF template based on product type.
     *
     * @param  array<int, array{title:string, body:string}>  $sections  Sections as produced by ContentJob (one per category / SOP / sequence).
     */
    public function exportDigitalProductPdf(string $relPath, string $productType, string $title, string $author, ?string $tagline, array $sections, string $brandColor = '#6C3CE1', ?string $productTypeLabel = null): string
    {
        [$view, $data] = match ($productType) {
            'prompt_library' => ['exports.prompt-library-pdf', ['categories' => $this->parsePromptCategories($sections)]],
            'sop_pack' => ['exports.sop-pack-pdf', ['sops' => $this->parseSops($sections)]],
            'email_sequence_vault' => ['exports.email-vault-pdf', ['sequences' => $this->parseSequences($sections)]],
            default => ['exports.premium-pdf', ['chapters' => $sections]],
        };

        $payload = array_merge([
            'title' => $title,
            'author' => $author,
            'subtitle' => null,
            'tagline' => $tagline,
            'brandColor' => $brandColor,
            'productType' => $productTypeLabel,
        ], $data);

        $pdf = Pdf::loadView($view, $payload)->setPaper('a4');

        $absolute = $this->ensurePath($relPath);
        $pdf->save($absolute);

        return $absolute;
    }

    /** @return array<int, array{name:string, prompts:array<int, array{title:string, use_when:string, the_prompt:string, tip:string}>}> */
    private function parsePromptCategories(array $sections): array
    {
        $cats = [];
        foreach ($sections as $section) {
            $prompts = [];
            $body = (string) ($section['body'] ?? '');

            if (preg_match_all('/^PROMPT\s+\d+:\s*(.+?)$/m', $body, $titleMatches, PREG_OFFSET_CAPTURE)) {
                $count = count($titleMatches[0]);
                for ($i = 0; $i < $count; $i++) {
                    $promptTitle = trim($titleMatches[1][$i][0]);
                    $start = $titleMatches[0][$i][1] + strlen($titleMatches[0][$i][0]);
                    $end = $i + 1 < $count ? $titleMatches[0][$i + 1][1] : strlen($body);
                    $block = trim(substr($body, $start, $end - $start));

                    $prompts[] = [
                        'title' => $promptTitle,
                        'use_when' => $this->grabField($block, 'USE WHEN'),
                        'the_prompt' => $this->grabField($block, 'THE PROMPT'),
                        'tip' => $this->grabField($block, 'TIP'),
                    ];
                }
            }

            $cats[] = [
                'name' => $section['title'] ?? 'Category',
                'prompts' => $prompts,
            ];
        }

        return $cats;
    }

    /** @return array<int, array{title:string, purpose:string, when_to_use:string, need_before:string, steps:array<int,string>, mistakes:array<int,string>, notes:string}> */
    private function parseSops(array $sections): array
    {
        $sops = [];
        foreach ($sections as $section) {
            $body = (string) ($section['body'] ?? '');
            // Strip leading "SOP: Title" header if present
            $body = preg_replace('/^SOP:\s*.+\n/m', '', $body, 1);

            $steps = $this->grabList($body, 'STEPS', '/^\s*\d+\.\s+/');
            $mistakes = $this->grabList($body, 'COMMON MISTAKES TO AVOID', '/^\s*-\s+/');

            $sops[] = [
                'title' => $section['title'] ?? 'SOP',
                'purpose' => $this->grabField($body, 'PURPOSE'),
                'when_to_use' => $this->grabField($body, 'WHEN TO USE(?: THIS SOP)?'),
                'need_before' => $this->grabField($body, 'WHAT YOU NEED BEFORE STARTING'),
                'steps' => $steps,
                'mistakes' => $mistakes,
                'notes' => $this->grabField($body, 'NOTES'),
            ];
        }

        return $sops;
    }

    /** @return array<int, array{name:string, trigger:string, goal:string, emails:array<int, array{n:int, of:int, timing:string, subject:string, preview:string, body:string, cta:string}>}> */
    private function parseSequences(array $sections): array
    {
        $sequences = [];
        foreach ($sections as $section) {
            $body = (string) ($section['body'] ?? '');
            $emails = [];

            // Match "...— Email N of X" markers
            if (preg_match_all('/^.+?Email\s+(\d+)\s+of\s+(\d+)\s*$/mi', $body, $hdr, PREG_OFFSET_CAPTURE)) {
                $count = count($hdr[0]);
                for ($i = 0; $i < $count; $i++) {
                    $n = (int) $hdr[1][$i][0];
                    $of = (int) $hdr[2][$i][0];
                    $start = $hdr[0][$i][1] + strlen($hdr[0][$i][0]);
                    $end = $i + 1 < $count ? $hdr[0][$i + 1][1] : strlen($body);
                    $block = trim(substr($body, $start, $end - $start));

                    $emails[] = [
                        'n' => $n,
                        'of' => $of,
                        'timing' => $this->grabField($block, 'SEND TIMING'),
                        'subject' => $this->grabField($block, 'SUBJECT LINE'),
                        'preview' => $this->grabField($block, 'PREVIEW TEXT'),
                        'body' => $this->grabField($block, 'BODY'),
                        'cta' => $this->grabField($block, 'CTA'),
                    ];
                }
            }

            $sequences[] = [
                'name' => $section['title'] ?? 'Sequence',
                'trigger' => '',
                'goal' => '',
                'emails' => $emails,
            ];
        }

        return $sequences;
    }

    /** Grab the text following a labelled field up to the next ALL-CAPS LABEL: or end. */
    private function grabField(string $body, string $label): string
    {
        // Label may have escaped regex chars
        $pattern = '/^'.$label.':\s*\n?(.*?)(?=^[A-Z][A-Z \-]{2,}:\s*$|\z)/sm';
        if (preg_match($pattern, $body, $m)) {
            return trim(preg_replace("/\s*\n?-{3,}\s*$/", '', $m[1]));
        }

        return '';
    }

    /** Grab a labelled list whose lines match $linePattern. */
    private function grabList(string $body, string $label, string $linePattern): array
    {
        $raw = $this->grabField($body, $label);
        if (! $raw) {
            return [];
        }
        $lines = preg_split("/\r\n|\n/", $raw);
        $items = [];
        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '') {
                continue;
            }
            $items[] = trim(preg_replace($linePattern, '', $trim));
        }

        return $items;
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
