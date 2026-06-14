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
            // Original 3 types — specialised parsers
            'prompt_library' => ['exports.prompt-library-pdf', ['categories' => $this->parsePromptCategories($sections)]],
            'sop_pack' => ['exports.sop-pack-pdf', ['sops' => $this->parseSops($sections)]],
            'email_sequence_vault' => ['exports.email-vault-pdf', ['sequences' => $this->parseSequences($sections)]],
            // New 8 types — sections passed directly (JSON already decoded by parseSections)
            'content_calendar_system' => ['exports.content-calendar-pdf', ['sections' => $sections]],
            'excel_tracker' => ['exports.excel-tracker-guide-pdf', ['sections' => $sections]],
            'notion_business_os' => ['exports.notion-os-pdf', ['sections' => $sections]],
            'website_copy_pack' => ['exports.website-copy-pdf', ['sections' => $sections]],
            'brand_messaging_system' => ['exports.brand-messaging-pdf', ['sections' => $sections]],
            'sales_funnel_copy' => ['exports.sales-funnel-pdf', ['sections' => $sections]],
            'niche_research_report' => ['exports.niche-research-pdf', ['sections' => $sections]],
            'buyer_persona_pack' => ['exports.buyer-persona-pdf', ['sections' => $sections]],
            // Fallback
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

    // ── XLSX exports ──────────────────────────────────────────────────────────

    /**
     * Content Calendar XLSX — 5 sheets.
     *
     * @param  list<array{title:string,body:string}>  $sections
     */
    public function exportContentCalendarXlsx(string $relPath, string $title, array $sections): string
    {
        $w = new XlsxWriter;

        // Index sections by title
        $byTitle = [];
        foreach ($sections as $s) {
            $byTitle[$s['title']] = $s['body'];
        }

        // Sheet 1 — Overview
        $w->addSheet('Overview');
        $w->writeRow([$title], style: 'header', colWidths: [40, 60]);
        $w->writeRow(['90-Day Content Calendar System'], style: 'bold');
        $w->writeRow(['']);
        $w->writeRow(['HOW TO USE THIS SPREADSHEET'], style: 'bold');
        $usageText = $byTitle['How to Use This Calendar'] ?? 'See PDF for full usage guide.';
        foreach ($this->chunkText($usageText, 800) as $chunk) {
            $w->writeRow([$chunk]);
        }
        $w->writeRow(['']);
        $w->writeRow(['YOUR CONTENT PILLARS'], style: 'bold');
        $pillarsText = $byTitle['Your Content Pillars'] ?? '';
        foreach ($this->chunkText($pillarsText, 600) as $chunk) {
            $w->writeRow([$chunk]);
        }

        // Sheet 2 — 90-Day Calendar
        $w->addSheet('90-Day Calendar');
        $calHeaders = ['Day', 'Week', 'Date (fill in)', 'Platform', 'Content Pillar', 'Content Type', 'Topic', 'Hook', 'Caption Notes', 'Hashtags', 'CTA', 'Repurpose To', 'Status', 'Notes'];
        $w->writeRow($calHeaders, style: 'header', colWidths: [6, 8, 14, 14, 16, 16, 28, 32, 32, 20, 18, 18, 14, 20]);
        $w->addDropdown(12, 2, 91, ['Not started', 'In progress', 'Scheduled', 'Posted']);

        // Parse post entries from Month sections
        $calRows = $this->parseCalendarEntries($sections);

        // Fill 90 rows — use parsed data where available, blank template otherwise
        for ($day = 1; $day <= 90; $day++) {
            $week = 'Week '.ceil($day / 7);
            $entry = $calRows[$day] ?? [];
            $style = ($day % 2 === 0) ? 'alt' : 'normal';
            $w->writeRow([
                $day,
                $week,
                '',  // buyer fills date
                $entry['platform'] ?? '',
                $entry['content_pillar'] ?? '',
                $entry['content_type'] ?? '',
                $entry['topic'] ?? '',
                $entry['hook'] ?? '',
                $entry['caption_notes'] ?? '',
                $entry['hashtags'] ?? '',
                $entry['cta'] ?? '',
                $entry['repurpose_to'] ?? '',
                'Not started',
                '',
            ], style: $style);
        }

        // Sheet 3 — Caption Frameworks
        $w->addSheet('Caption Frameworks');
        $w->writeRow(['Caption Frameworks — 10 Proven Structures', ''], style: 'header', colWidths: [30, 60]);
        $captionText = $byTitle['Caption Frameworks'] ?? '';
        $w->writeRow(['Framework', 'Full Description & Example'], style: 'bold');
        foreach ($this->parseNumberedItems($captionText) as $item) {
            $w->writeRow([$item['title'], $item['body']], style: 'normal');
        }

        // Sheet 4 — Hashtag Bank
        $w->addSheet('Hashtag Bank');
        $w->writeRow(['Platform', 'Hashtags'], style: 'header', colWidths: [20, 80]);
        $hashText = $byTitle['Hashtag Strategy'] ?? $byTitle['Your Hashtag Strategy'] ?? '';
        foreach ($this->chunkText($hashText, 400) as $chunk) {
            $w->writeRow(['All Platforms', $chunk]);
        }

        // Sheet 5 — Repurposing Guide
        $w->addSheet('Repurposing Guide');
        $w->writeRow(['Repurposing Guide — One Idea, 5 Platforms', ''], style: 'header', colWidths: [30, 70]);
        $repurposeText = $byTitle['Repurposing Guide'] ?? '';
        $w->writeRow(['Original Idea / Platform', 'How to Repurpose It'], style: 'bold');
        foreach ($this->chunkText($repurposeText, 500) as $chunk) {
            $w->writeRow(['See PDF', $chunk]);
        }

        $absolute = $this->ensurePath($relPath);

        return $w->save($absolute);
    }

    /**
     * Excel Tracker XLSX — dynamic sheets from structure.
     *
     * @param  list<array{title:string,body:string}>  $sections
     * @param  array<mixed>  $structure  structure_output from StructureJob
     */
    public function exportExcelTrackerXlsx(string $relPath, string $title, array $sections, array $structure): string
    {
        $w = new XlsxWriter;

        $byTitle = [];
        foreach ($sections as $s) {
            $byTitle[$s['title']] = $s['body'];
        }

        // Sheet 1 — How To Use
        $w->addSheet('How To Use');
        $w->writeRow([$title], style: 'header', colWidths: [50, 60]);
        $w->writeRow(['']);
        $usageText = $byTitle['How to Use This Tracker'] ?? 'See PDF for usage guide.';
        foreach ($this->chunkText($usageText, 700) as $chunk) {
            $w->writeRow([$chunk]);
        }

        // Sheet 2 — Dashboard
        $w->addSheet('Dashboard');
        $w->writeRow(['Metric', 'Value', 'Notes'], style: 'header', colWidths: [30, 20, 40]);
        $dashText = $byTitle['Tracker Specification'] ?? '';
        foreach ($this->chunkText($dashText, 300) as $chunk) {
            $w->writeRow(['—', '', $chunk]);
        }

        // Sheet 3+ from structure sheets
        $sheets = $structure['sheets'] ?? [];
        foreach ($sheets as $sheet) {
            $sheetName = $this->safeSheetName($sheet['sheet_name'] ?? 'Sheet');
            if (in_array($sheetName, ['How To Use', 'Dashboard'])) {
                continue;
            }

            $w->addSheet($sheetName);
            $columns = $sheet['columns'] ?? [];

            if (empty($columns)) {
                $w->writeRow([$sheetName.' — configure columns as needed'], style: 'header', colWidths: [40]);
                $w->writeRow(['This sheet is ready for your data.']);

                continue;
            }

            // Header row
            $headers = array_column($columns, 'header');
            $colWidths = array_fill(0, count($headers), 20);
            $w->writeRow($headers, style: 'header', colWidths: $colWidths);

            // Add dropdowns where defined
            foreach ($columns as $ci => $col) {
                $opts = $col['dropdown_options'] ?? [];
                if ($opts && count($opts) > 0) {
                    $optStr = implode(',', array_slice($opts, 0, 10));
                    if (strlen($optStr) <= 250) {
                        $w->addDropdown($ci, 2, 200, array_slice($opts, 0, 10));
                    }
                }
            }

            // 3 example rows
            $exampleCount = $sheet['example_rows'] ?? 3;
            for ($e = 1; $e <= min($exampleCount, 3); $e++) {
                $row = [];
                foreach ($columns as $col) {
                    $row[] = match ($col['data_type'] ?? 'text') {
                        'number' => $e * 100,
                        'date' => '2024-0'.($e).'-01',
                        'dropdown' => $col['dropdown_options'][0] ?? 'Option 1',
                        default => '(Example '.($e).')',
                    };
                }
                $w->writeRow($row, style: 'alt');
            }

            // Empty data rows
            for ($r = 0; $r < 20; $r++) {
                $w->writeRow(array_fill(0, count($headers), ''));
            }
        }

        $absolute = $this->ensurePath($relPath);

        return $w->save($absolute);
    }

    // ── XLSX helpers ──────────────────────────────────────────────────────────

    /** Split long text into chunks that fit in a cell. */
    private function chunkText(string $text, int $maxLen = 500): array
    {
        $text = trim($text);
        if ($text === '') {
            return [''];
        }

        $paragraphs = preg_split("/\n\s*\n/", $text) ?: [$text];
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if ($para === '') {
                continue;
            }

            if (strlen($current) + strlen($para) > $maxLen && $current !== '') {
                $chunks[] = trim($current);
                $current = $para;
            } else {
                $current .= ($current ? "\n\n" : '').$para;
            }
        }

        if (trim($current) !== '') {
            $chunks[] = trim($current);
        }

        return $chunks ?: [''];
    }

    /** Parse numbered items from text like "1. Title\n..." */
    private function parseNumberedItems(string $text): array
    {
        $items = [];
        if (preg_match_all('/^\d+[\.\)]\s+(.+?)(?=^\d+[\.\)]|\z)/ms', $text, $matches)) {
            foreach ($matches[1] as $block) {
                $block = trim($block);
                $lines = explode("\n", $block, 2);
                $items[] = ['title' => trim($lines[0]), 'body' => trim($lines[1] ?? '')];
            }
        } else {
            foreach ($this->chunkText($text, 300) as $chunk) {
                $items[] = ['title' => '—', 'body' => $chunk];
            }
        }

        return $items;
    }

    /**
     * Try to parse calendar post entries from Month sections.
     * Returns array keyed by day number.
     */
    private function parseCalendarEntries(array $sections): array
    {
        $entries = [];
        $dayOffset = 0;

        foreach ($sections as $section) {
            if (! preg_match('/^Month\s+(\d+)/i', $section['title'] ?? '', $m)) {
                continue;
            }

            $monthNum = (int) $m[1];
            $dayOffset = ($monthNum - 1) * 30;
            $body = $section['body'] ?? '';

            // Try to match lines like "Day N |..." or "Day N:" or numbered lists
            $lines = explode("\n", $body);
            $localDay = 0;

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                // Pattern: "Day X" or "Day X:"
                if (preg_match('/^Day\s+(\d+)[:\|]?\s*/i', $line, $dm)) {
                    $localDay = (int) $dm[1];
                    $rest = trim(substr($line, strlen($dm[0])));
                    $day = $dayOffset + $localDay;
                    if ($day >= 1 && $day <= 90) {
                        // Try to parse pipe-separated fields
                        $parts = array_map('trim', explode('|', $rest));
                        $entries[$day] = [
                            'platform' => $parts[0] ?? '',
                            'content_pillar' => $parts[1] ?? '',
                            'content_type' => $parts[2] ?? '',
                            'topic' => $parts[3] ?? $rest,
                            'hook' => $parts[4] ?? '',
                            'caption_notes' => $parts[5] ?? '',
                            'hashtags' => $parts[6] ?? '',
                            'cta' => $parts[7] ?? '',
                            'repurpose_to' => $parts[8] ?? '',
                        ];
                    }
                } elseif ($localDay > 0 && isset($entries[$dayOffset + $localDay])) {
                    // Append to topic of current day
                    $entries[$dayOffset + $localDay]['topic'] .= ' '.$line;
                }
            }
        }

        return $entries;
    }

    /** Sanitise sheet name for Excel (max 31 chars, no special chars). */
    private function safeSheetName(string $name): string
    {
        $name = preg_replace('/[\/\\\\?\*\[\]:]/', '', $name) ?? $name;

        return mb_substr(trim($name), 0, 31);
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
