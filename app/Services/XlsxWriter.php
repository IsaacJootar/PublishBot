<?php

namespace App\Services;

use ZipArchive;

/**
 * Lightweight pure-PHP XLSX writer using ZipArchive.
 * No external dependencies — works with the built-in zip extension.
 *
 * Style indices: 0=normal  1=header(dark)  2=alt(light)  3=bold
 */
class XlsxWriter
{
    /** @var list<array{name:string,rows:list<array{cells:list<mixed>,style:string}>,colWidths:list<float>,validations:list<array>}> */
    private array $sheets = [];

    private int $cur = -1;

    private array $strings = [];

    private array $stringList = [];

    private const HDR_BG = 'FF1A0D33';

    private const HDR_FG = 'FFFFFFFF';

    private const ALT_BG = 'FFF5F4FF';

    // ── Public API ────────────────────────────────────────────────────────────

    public function addSheet(string $name): self
    {
        $this->sheets[] = ['name' => $name, 'rows' => [], 'colWidths' => [], 'validations' => []];
        $this->cur = count($this->sheets) - 1;

        return $this;
    }

    /**
     * Write one row.
     *
     * @param  list<mixed>  $cells
     * @param  'header'|'alt'|'bold'|'normal'  $style
     * @param  list<float>  $colWidths  Set column widths (applied once per sheet)
     */
    public function writeRow(array $cells, string $style = 'normal', array $colWidths = []): self
    {
        if ($this->cur < 0) {
            $this->addSheet('Sheet1');
        }

        if ($colWidths) {
            $this->sheets[$this->cur]['colWidths'] = $colWidths;
        }

        $this->sheets[$this->cur]['rows'][] = ['cells' => $cells, 'style' => $style];

        return $this;
    }

    /**
     * Add an inline-list dropdown to a column range.
     *
     * @param  list<string>  $options  Max ~200 chars total
     */
    public function addDropdown(int $colIdx, int $firstRow, int $lastRow, array $options): self
    {
        if ($this->cur < 0) {
            return $this;
        }

        $this->sheets[$this->cur]['validations'][] = [
            'col' => $colIdx,
            'first' => $firstRow,
            'last' => $lastRow,
            'opts' => $options,
        ];

        return $this;
    }

    /** Write file and return absolute path. */
    public function save(string $path): string
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Index all strings first
        $this->strings = [];
        $this->stringList = [];
        foreach ($this->sheets as $sheet) {
            foreach ($sheet['rows'] as $row) {
                foreach ($row['cells'] as $v) {
                    if (is_string($v) && $v !== '' && ! is_numeric($v)) {
                        $this->sid($v);
                    }
                }
            }
        }

        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $this->xmlContentTypes());
        $zip->addFromString('_rels/.rels', $this->xmlRootRels());
        $zip->addFromString('xl/workbook.xml', $this->xmlWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xmlWorkbookRels());
        $zip->addFromString('xl/styles.xml', $this->xmlStyles());
        $zip->addFromString('xl/sharedStrings.xml', $this->xmlSharedStrings());
        foreach ($this->sheets as $i => $sheet) {
            $zip->addFromString("xl/worksheets/sheet{$i}.xml", $this->xmlWorksheet($sheet));
        }
        $zip->close();

        return $path;
    }

    // ── XML builders ──────────────────────────────────────────────────────────

    private function xmlContentTypes(): string
    {
        $sheets = '';
        foreach ($this->sheets as $i => $_) {
            $sheets .= '<Override PartName="/xl/worksheets/sheet'.$i.'.xml"'
                .' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            .$sheets.'</Types>';
    }

    private function xmlRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1"'
            .' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"'
            .' Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function xmlWorkbook(): string
    {
        $sheets = '';
        foreach ($this->sheets as $i => $sheet) {
            $name = htmlspecialchars($sheet['name'], ENT_XML1);
            $sheets .= '<sheet name="'.$name.'" sheetId="'.($i + 1).'" r:id="rId'.($i + 1).'"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'.$sheets.'</sheets>'
            .'</workbook>';
    }

    private function xmlWorkbookRels(): string
    {
        $rels = '';
        foreach ($this->sheets as $i => $_) {
            $rels .= '<Relationship Id="rId'.($i + 1).'"'
                .' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
                .' Target="worksheets/sheet'.$i.'.xml"/>';
        }
        $n = count($this->sheets) + 1;
        $rels .= '<Relationship Id="rId'.$n.'"'
            .' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings"'
            .' Target="sharedStrings.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$rels.'</Relationships>';
    }

    private function xmlStyles(): string
    {
        // 0=normal  1=header  2=alt  3=bold
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="3">'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><color rgb="'.self::HDR_FG.'"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="4">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="'.self::HDR_BG.'"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="'.self::ALT_BG.'"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="2">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border>'
            .'<left style="thin"><color rgb="FFD0CAEE"/></left>'
            .'<right style="thin"><color rgb="FFD0CAEE"/></right>'
            .'<top style="thin"><color rgb="FFD0CAEE"/></top>'
            .'<bottom style="thin"><color rgb="FFD0CAEE"/></bottom>'
            .'<diagonal/></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="4">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment wrapText="1" vertical="top"/></xf>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0"><alignment wrapText="1" vertical="top"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="1" xfId="0"><alignment wrapText="1" vertical="top"/></xf>'
            .'</cellXfs>'
            .'</styleSheet>';
    }

    private function xmlSharedStrings(): string
    {
        $n = count($this->stringList);
        $items = '';
        foreach ($this->stringList as $s) {
            $items .= '<si><t xml:space="preserve">'.htmlspecialchars($s, ENT_XML1).'</t></si>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .' count="'.$n.'" uniqueCount="'.$n.'">'.$items.'</sst>';
    }

    private function xmlWorksheet(array $sheet): string
    {
        // Column widths
        $cols = '';
        if ($sheet['colWidths']) {
            $cols = '<cols>';
            foreach ($sheet['colWidths'] as $i => $w) {
                $cols .= '<col min="'.($i + 1).'" max="'.($i + 1).'" width="'.$w.'" customWidth="1"/>';
            }
            $cols .= '</cols>';
        }

        // Rows
        $rowsXml = '';
        foreach ($sheet['rows'] as $rowIdx => $row) {
            $r = $rowIdx + 1;
            $s = match ($row['style']) {
                'header' => 1, 'alt' => 2, 'bold' => 3, default => 0
            };
            $ht = $row['style'] === 'header' ? ' ht="22" customHeight="1"' : ' ht="40" customHeight="1"';
            $rowsXml .= '<row r="'.$r.'"'.$ht.'>';
            foreach ($row['cells'] as $ci => $cell) {
                $rowsXml .= $this->cellXml($this->col($ci).$r, $cell, $s);
            }
            $rowsXml .= '</row>';
        }

        // Data validations
        $dvXml = '';
        if (! empty($sheet['validations'])) {
            $dvXml = '<dataValidations count="'.count($sheet['validations']).'">';
            foreach ($sheet['validations'] as $v) {
                $c = $this->col($v['col']);
                $sqref = $c.$v['first'].':'.$c.$v['last'];
                $list = implode(',', $v['opts']);
                $dvXml .= '<dataValidation type="list" showDropDown="0" sqref="'.$sqref.'">'
                    .'<formula1>"'.htmlspecialchars($list, ENT_XML1).'"</formula1>'
                    .'</dataValidation>';
            }
            $dvXml .= '</dataValidations>';
        }

        $freeze = '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .$cols
            .'<sheetViews><sheetView workbookViewId="0">'.$freeze.'</sheetView></sheetViews>'
            .'<sheetData>'.$rowsXml.'</sheetData>'
            .$dvXml
            .'</worksheet>';
    }

    // ── Cell helpers ──────────────────────────────────────────────────────────

    private function cellXml(string $ref, mixed $cell, int $s): string
    {
        if ($cell === null || $cell === '') {
            return '<c r="'.$ref.'" s="'.$s.'"/>';
        }

        if (is_numeric($cell) && ! is_string($cell)) {
            return '<c r="'.$ref.'" s="'.$s.'"><v>'.((float) $cell).'</v></c>';
        }

        return '<c r="'.$ref.'" t="s" s="'.$s.'"><v>'.$this->sid((string) $cell).'</v></c>';
    }

    private function sid(string $v): int
    {
        if (! isset($this->strings[$v])) {
            $this->strings[$v] = count($this->stringList);
            $this->stringList[] = $v;
        }

        return $this->strings[$v];
    }

    private function col(int $idx): string
    {
        $out = '';
        $n = $idx + 1;
        while ($n > 0) {
            $n--;
            $out = chr(65 + ($n % 26)).$out;
            $n = intdiv($n, 26);
        }

        return $out;
    }
}
