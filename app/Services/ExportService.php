<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxExporter;
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

    /** @return array<int, array{title:string, purpose:string, when_to_use:string, need_before:string, steps:array<int,string>, mistakes:array<int,string>, notes:string, raw_body:string}> */
    private function parseSops(array $sections): array
    {
        $sops = [];
        foreach ($sections as $section) {
            $body = (string) ($section['body'] ?? '');
            // Strip leading "SOP: Title" header if present
            $body = preg_replace('/^SOP:\s*.+\n/m', '', $body, 1);

            $purpose = $this->grabField($body, 'PURPOSE');
            $steps = $this->grabList($body, 'STEPS', '/^\s*\d+\.\s+/');
            $mistakes = $this->grabList($body, 'COMMON MISTAKES TO AVOID', '/^\s*-\s+/');

            $sops[] = [
                'title' => $section['title'] ?? 'SOP',
                'purpose' => $purpose,
                'when_to_use' => $this->grabField($body, 'WHEN TO USE(?: THIS SOP)?'),
                'need_before' => $this->grabField($body, 'WHAT YOU NEED BEFORE STARTING'),
                'steps' => $steps,
                'mistakes' => $mistakes,
                'notes' => $this->grabField($body, 'NOTES'),
                // Fallback: raw body rendered when specific labels not found
                'raw_body' => (! $purpose && empty($steps)) ? $body : '',
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
        // Plain PhpWord — NO addTitleStyle, NO addTitle (both cause broken DOCX on Word).
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);
        $phpWord->getSettings()->setThemeFontLang(new Language(Language::EN_US));

        // 1-inch margins (1440 twips) — KDP standard
        $section = $phpWord->addSection([
            'marginTop' => 1440,
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);

        // Title page
        $section->addText(
            $title,
            ['name' => 'Times New Roman', 'size' => 28, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 2400, 'spaceAfter' => 240]
        );
        if ($subtitle) {
            $section->addText(
                $subtitle,
                ['name' => 'Times New Roman', 'size' => 16, 'italic' => true],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 240]
            );
        }
        $section->addText(
            'by '.$author,
            ['name' => 'Times New Roman', 'size' => 14],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 720]
        );
        $section->addPageBreak();

        foreach ($chapters as $i => $chapter) {
            if ($i > 0) {
                $section->addPageBreak();
            }

            // Chapter heading as bold plain text (NOT addTitle — that causes corruption)
            $section->addText(
                $chapter['title'] ?? '',
                ['name' => 'Times New Roman', 'size' => 18, 'bold' => true],
                ['alignment' => Jc::CENTER, 'spaceBefore' => 240, 'spaceAfter' => 480]
            );

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

        // Use plain PhpWord — NO addTitleStyle, NO addTitle, NO addTOC.
        // Those three PhpWord features generate broken DOCX on many Word versions.
        // All headings are rendered as bold large addText() calls instead.
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop' => 1440,
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);

        // Cover page
        $section->addText(
            $title,
            ['name' => 'Calibri', 'size' => 32, 'bold' => true, 'color' => $brandColor],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 2880, 'spaceAfter' => 240]
        );
        if ($subtitle) {
            $section->addText(
                $subtitle,
                ['name' => 'Calibri', 'size' => 16, 'italic' => true, 'color' => '666666'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 240]
            );
        }
        $section->addText(
            'by '.$author,
            ['name' => 'Calibri', 'size' => 13, 'color' => '333333'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 480]
        );
        $section->addPageBreak();

        // Contents page (plain text — no TOC field codes)
        $section->addText(
            'Contents',
            ['name' => 'Calibri', 'size' => 20, 'bold' => true, 'color' => $brandColor],
            ['spaceAfter' => 240]
        );
        foreach ($chapters as $i => $chapter) {
            $section->addText(
                ($i + 1).'.  '.($chapter['title'] ?? ''),
                ['name' => 'Calibri', 'size' => 12, 'color' => $brandColor],
                ['spaceAfter' => 100]
            );
        }
        $section->addPageBreak();

        // Chapters — plain bold text for headings (no addTitle / heading styles)
        foreach ($chapters as $i => $chapter) {
            if ($i > 0) {
                $section->addPageBreak();
            }

            // Chapter heading as bold large text
            $section->addText(
                $chapter['title'] ?? '',
                ['name' => 'Calibri', 'size' => 18, 'bold' => true, 'color' => $brandColor],
                ['spaceBefore' => 240, 'spaceAfter' => 360]
            );

            foreach ($this->paragraphs($chapter['body']) as $para) {
                $section->addText($para, ['name' => 'Calibri', 'size' => 11], [
                    'alignment' => Jc::BOTH,
                    'spaceAfter' => 180,
                    'lineHeight' => 1.4,
                ]);
            }
        }

        // Footer — page numbers
        $footer = $section->addFooter();
        $footer->addPreserveText('{PAGE} / {NUMPAGES}', ['size' => 9, 'color' => '999999'], ['alignment' => Jc::CENTER]);

        $absolute = $this->ensurePath($relPath);
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($absolute);

        return $absolute;
    }

    // ── XLSX exports (PhpSpreadsheet) ─────────────────────────────────────────

    /**
     * Content Calendar XLSX — 5 sheets.
     *
     * @param  list<array{title:string,body:string}>  $sections
     */
    public function exportContentCalendarXlsx(string $relPath, string $title, array $sections): string
    {
        $byTitle = [];
        foreach ($sections as $s) {
            $byTitle[$s['title']] = $s['body'];
        }

        $ss = new Spreadsheet;
        $ss->getProperties()->setTitle($title)->setCreator('PublishBot');

        // ── Sheet 1: Overview ──────────────────────────────────────────────
        $sh = $ss->getActiveSheet()->setTitle('Overview');
        $sh->getColumnDimension('A')->setWidth(80);

        $row = 1;
        $sh->setCellValue("A{$row}", $title);
        $this->styleHeader($sh, "A{$row}:A{$row}");
        $sh->getStyle("A{$row}")->getFont()->setSize(16);
        $row++;

        $sh->setCellValue("A{$row}", '90-Day Content Calendar System');
        $this->styleBold($sh, "A{$row}:A{$row}");
        $row += 2;

        $sh->setCellValue("A{$row}", 'HOW TO USE THIS SPREADSHEET');
        $this->styleBold($sh, "A{$row}:A{$row}");
        $row++;

        $usageText = $byTitle['How to Use This Calendar'] ?? 'See PDF for full usage guide.';
        foreach (explode("\n", $usageText) as $line) {
            $sh->setCellValue("A{$row}", $line);
            $sh->getStyle("A{$row}")->getAlignment()->setWrapText(true);
            $row++;
        }
        $row++;

        $sh->setCellValue("A{$row}", 'YOUR CONTENT PILLARS');
        $this->styleBold($sh, "A{$row}:A{$row}");
        $row++;

        $pillarsText = $byTitle['Your Content Pillars'] ?? '';
        foreach (explode("\n", $pillarsText) as $line) {
            $sh->setCellValue("A{$row}", $line);
            $sh->getStyle("A{$row}")->getAlignment()->setWrapText(true);
            $row++;
        }

        // ── Sheet 2: 90-Day Calendar ───────────────────────────────────────
        $cal = $ss->createSheet()->setTitle('90-Day Calendar');
        $headers = ['Day', 'Week', 'Date (fill in)', 'Platform', 'Content Pillar', 'Content Type', 'Topic', 'Hook', 'Caption Notes', 'Hashtags', 'CTA', 'Repurpose To', 'Status', 'Notes'];
        $widths = [6,     8,     14,               16,         18,               18,              34,       38,      36,              22,         18,    22,            14,       24];

        foreach ($headers as $ci => $hdr) {
            $col = Coordinate::stringFromColumnIndex($ci + 1);
            $cal->setCellValue("{$col}1", $hdr);
            $cal->getColumnDimension($col)->setWidth($widths[$ci]);
        }
        $this->styleHeader($cal, 'A1:'.Coordinate::stringFromColumnIndex(count($headers)).'1');
        $cal->freezePane('A2');

        // Status dropdown
        $calRows = $this->parseCalendarEntries($sections);
        $statusCol = Coordinate::stringFromColumnIndex(13); // M
        $dv = new DataValidation;
        $dv->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_INFORMATION)
            ->setAllowBlank(false)
            ->setShowDropDown(false)
            ->setFormula1('"Not started,In progress,Scheduled,Posted"');

        for ($day = 1; $day <= 90; $day++) {
            $r = $day + 1;
            $week = 'Week '.(int) ceil($day / 7);
            $entry = $calRows[$day] ?? [];
            $altBg = ($day % 2 === 0);

            $values = [
                $day,
                $week,
                '',
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
            ];

            foreach ($values as $ci => $val) {
                $col = Coordinate::stringFromColumnIndex($ci + 1);
                $cal->setCellValue("{$col}{$r}", $val);
                $cal->getStyle("{$col}{$r}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                if ($altBg) {
                    $cal->getStyle("{$col}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F4FF');
                }
            }

            $cal->getRowDimension($r)->setRowHeight(40);

            // Apply dropdown to Status column
            $cloned = clone $dv;
            $cal->getCell("{$statusCol}{$r}")->setDataValidation($cloned);
        }
        $cal->getRowDimension(1)->setRowHeight(22);

        // ── Sheet 3: Caption Frameworks ────────────────────────────────────
        $cap = $ss->createSheet()->setTitle('Caption Frameworks');
        $cap->getColumnDimension('A')->setWidth(30);
        $cap->getColumnDimension('B')->setWidth(70);
        $cap->setCellValue('A1', 'Framework');
        $cap->setCellValue('B1', 'Description & Example');
        $this->styleHeader($cap, 'A1:B1');
        $cap->freezePane('A2');

        $captionText = $byTitle['Caption Frameworks'] ?? '';
        $capRow = 2;
        foreach ($this->parseNumberedItems($captionText) as $item) {
            $cap->setCellValue("A{$capRow}", $item['title']);
            $cap->setCellValue("B{$capRow}", $item['body']);
            $cap->getStyle("A{$capRow}:B{$capRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $cap->getRowDimension($capRow)->setRowHeight(60);
            $capRow++;
        }

        // ── Sheet 4: Hashtag Bank ──────────────────────────────────────────
        $hash = $ss->createSheet()->setTitle('Hashtag Bank');
        $hash->getColumnDimension('A')->setWidth(22);
        $hash->getColumnDimension('B')->setWidth(80);
        $hash->setCellValue('A1', 'Platform');
        $hash->setCellValue('B1', 'Hashtags');
        $this->styleHeader($hash, 'A1:B1');
        $hash->freezePane('A2');

        $hashText = $byTitle['Hashtag Strategy'] ?? $byTitle['Your Hashtag Strategy'] ?? '';
        $hashRow = 2;
        foreach (array_filter(explode("\n", $hashText)) as $line) {
            if (trim($line) === '') {
                continue;
            }

            // Lines like "Instagram: #tag1 #tag2" or "Platform: hashtags"
            if (preg_match('/^([A-Za-z\/ ]+):\s*(.+)$/', $line, $hm)) {
                $hash->setCellValue("A{$hashRow}", trim($hm[1]));
                $hash->setCellValue("B{$hashRow}", trim($hm[2]));
            } else {
                $hash->setCellValue("B{$hashRow}", trim($line));
            }
            $hash->getStyle("A{$hashRow}:B{$hashRow}")->getAlignment()->setWrapText(true);
            $hashRow++;
        }

        // ── Sheet 5: Repurposing Guide ─────────────────────────────────────
        $rep = $ss->createSheet()->setTitle('Repurposing Guide');
        $rep->getColumnDimension('A')->setWidth(30);
        $rep->getColumnDimension('B')->setWidth(70);
        $rep->setCellValue('A1', 'Original Content Idea');
        $rep->setCellValue('B1', 'How to Repurpose It');
        $this->styleHeader($rep, 'A1:B1');
        $rep->freezePane('A2');

        $repText = $byTitle['Repurposing Guide'] ?? '';
        $repRow = 2;
        foreach (array_filter(array_map('trim', preg_split('/\n{2,}/', $repText) ?: [])) as $block) {
            $rep->setCellValue("A{$repRow}", 'See block below');
            $rep->setCellValue("B{$repRow}", $block);
            $rep->getStyle("B{$repRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $rep->getRowDimension($repRow)->setRowHeight(80);
            $repRow++;
        }

        $ss->setActiveSheetIndex(1); // default to calendar sheet

        $absolute = $this->ensurePath($relPath);
        (new XlsxExporter($ss))->save($absolute);

        return $absolute;
    }

    /**
     * Excel Tracker XLSX — dynamic sheets from AI structure.
     *
     * @param  list<array{title:string,body:string}>  $sections
     * @param  array<mixed>  $structure  structure_output from StructureJob
     */
    public function exportExcelTrackerXlsx(string $relPath, string $title, array $sections, array $structure): string
    {
        $byTitle = [];
        foreach ($sections as $s) {
            $byTitle[$s['title']] = $s['body'];
        }

        $ss = new Spreadsheet;
        $ss->getProperties()->setTitle($title)->setCreator('PublishBot');

        // ── Sheet 1: How To Use ────────────────────────────────────────────
        $sh = $ss->getActiveSheet()->setTitle('How To Use');
        $sh->getColumnDimension('A')->setWidth(80);
        $sh->setCellValue('A1', $title);
        $this->styleHeader($sh, 'A1:A1');
        $sh->getStyle('A1')->getFont()->setSize(16);

        $row = 2;
        $usageText = $byTitle['How to Use This Tracker'] ?? 'See PDF for usage guide.';
        foreach (explode("\n", $usageText) as $line) {
            $sh->setCellValue("A{$row}", $line);
            $sh->getStyle("A{$row}")->getAlignment()->setWrapText(true);
            $row++;
        }

        // ── Sheet 2: Dashboard ─────────────────────────────────────────────
        $dash = $ss->createSheet()->setTitle('Dashboard');
        $dash->getColumnDimension('A')->setWidth(32);
        $dash->getColumnDimension('B')->setWidth(22);
        $dash->getColumnDimension('C')->setWidth(50);
        $dash->setCellValue('A1', 'Metric');
        $dash->setCellValue('B1', 'Value');
        $dash->setCellValue('C1', 'Notes / Formula');
        $this->styleHeader($dash, 'A1:C1');
        $dash->freezePane('A2');

        $dashText = $byTitle['Tracker Specification'] ?? '';
        $dashRow = 2;
        foreach (array_filter(array_map('trim', explode("\n", $dashText))) as $line) {
            if (preg_match('/^[A-Z\s]{3,}:/', $line)) {
                // Section header line
                $dash->setCellValue("A{$dashRow}", $line);
                $this->styleBold($dash, "A{$dashRow}:C{$dashRow}");
            } else {
                $dash->setCellValue("C{$dashRow}", $line);
            }
            $dash->getStyle("A{$dashRow}:C{$dashRow}")->getAlignment()->setWrapText(true);
            $dashRow++;
        }

        // ── Sheet 3+: Tracker sheets from structure ────────────────────────
        $structSheets = $structure['sheets'] ?? [];
        foreach ($structSheets as $sheetDef) {
            $sheetName = $this->safeSheetName($sheetDef['sheet_name'] ?? 'Tracker');
            if (in_array($sheetName, ['How To Use', 'Dashboard'])) {
                continue;
            }

            $ts = $ss->createSheet()->setTitle($sheetName);
            $columns = $sheetDef['columns'] ?? [];

            if (empty($columns)) {
                $ts->getColumnDimension('A')->setWidth(50);
                $ts->setCellValue('A1', $sheetName.' — configure columns as needed');
                $this->styleHeader($ts, 'A1:A1');

                continue;
            }

            // Header row
            foreach ($columns as $ci => $col) {
                $colLetter = Coordinate::stringFromColumnIndex($ci + 1);
                $ts->setCellValue("{$colLetter}1", $col['header'] ?? 'Column '.($ci + 1));
                $ts->getColumnDimension($colLetter)->setWidth(22);
            }
            $lastCol = Coordinate::stringFromColumnIndex(count($columns));
            $this->styleHeader($ts, "A1:{$lastCol}1");
            $ts->freezePane('A2');
            $ts->getRowDimension(1)->setRowHeight(22);

            // Dropdowns
            foreach ($columns as $ci => $col) {
                $opts = array_slice($col['dropdown_options'] ?? [], 0, 10);
                if (! $opts) {
                    continue;
                }

                $formula = '"'.implode(',', array_map(fn ($o) => str_replace('"', '""', $o), $opts)).'"';
                if (strlen($formula) > 255) {
                    continue; // Excel inline list limit
                }

                $colLetter = Coordinate::stringFromColumnIndex($ci + 1);
                for ($r = 2; $r <= 200; $r++) {
                    $dv = new DataValidation;
                    $dv->setType(DataValidation::TYPE_LIST)
                        ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                        ->setAllowBlank(true)
                        ->setShowDropDown(false)
                        ->setFormula1($formula);
                    $ts->getCell("{$colLetter}{$r}")->setDataValidation($dv);
                }
            }

            // 3 example rows (alt background)
            $exampleCount = min((int) ($sheetDef['example_rows'] ?? 3), 3);
            for ($e = 1; $e <= $exampleCount; $e++) {
                $r = $e + 1;
                foreach ($columns as $ci => $col) {
                    $colLetter = Coordinate::stringFromColumnIndex($ci + 1);
                    $val = match ($col['data_type'] ?? 'text') {
                        'number' => $e * 100,
                        'date' => date('Y-m-d', mktime(0, 0, 0, $e, 1, 2024)),
                        'dropdown' => $col['dropdown_options'][0] ?? 'Option 1',
                        default => '(Example '.$e.')',
                    };
                    $ts->setCellValue("{$colLetter}{$r}", $val);
                    $ts->getStyle("{$colLetter}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F4FF');
                    $ts->getStyle("{$colLetter}{$r}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                }
                $ts->getRowDimension($r)->setRowHeight(30);
            }

            // 50 empty data rows
            for ($r = $exampleCount + 2; $r <= $exampleCount + 51; $r++) {
                $ts->getRowDimension($r)->setRowHeight(25);
                foreach ($columns as $ci => $_) {
                    $colLetter = Coordinate::stringFromColumnIndex($ci + 1);
                    $ts->getStyle("{$colLetter}{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFD0CAEE');
                }
            }
        }

        $ss->setActiveSheetIndex(1);

        $absolute = $this->ensurePath($relPath);
        (new XlsxExporter($ss))->save($absolute);

        return $absolute;
    }

    // ── XLSX helpers ──────────────────────────────────────────────────────────

    /** Apply dark header style (brand bg, white bold text, centered). */
    private function styleHeader(mixed $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A0D33']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF6C3CE1']]],
        ]);
    }

    /** Apply bold style. */
    private function styleBold(mixed $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F4FF');
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
            foreach (array_filter(array_map('trim', preg_split('/\n{2,}/', $text) ?: [])) as $chunk) {
                $items[] = ['title' => '—', 'body' => $chunk];
            }
        }

        return $items;
    }

    /**
     * Parse calendar post entries from Month sections.
     * Returns array keyed by day number.
     */
    private function parseCalendarEntries(array $sections): array
    {
        $entries = [];

        foreach ($sections as $section) {
            if (! preg_match('/^Month\s+(\d+)/i', $section['title'] ?? '', $m)) {
                continue;
            }

            $monthNum = (int) $m[1];
            $dayOffset = ($monthNum - 1) * 30;
            $lines = explode("\n", $section['body'] ?? '');
            $localDay = 0;

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                if (preg_match('/^Day\s+(\d+)[:\|]?\s*/i', $line, $dm)) {
                    $localDay = (int) $dm[1];
                    $rest = trim(substr($line, strlen($dm[0])));
                    $day = $dayOffset + $localDay;
                    if ($day >= 1 && $day <= 90) {
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
