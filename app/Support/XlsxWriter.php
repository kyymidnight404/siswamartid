<?php

namespace App\Support;

/** Minimal OpenXML writer for a single-sheet .xlsx file (no optional extensions required). */
class XlsxWriter
{
    public function write(string $path, array $headers, array $rows, string $title): void
    {
        $lastColumn = $this->columnName(count($headers));
        $lastRow = count($rows) + 3;
        $sheetRows = [];
        $sheetRows[] = '<row r="1"><c r="A1" s="1" t="inlineStr"><is><t>' . $this->xml($title) . '</t></is></c></row>';
        $sheetRows[] = '<row r="2"><c r="A2" t="inlineStr"><is><t>' . $this->xml('Diekspor: ' . date('d/m/Y H:i')) . '</t></is></c></row>';
        $headerCells = '';
        foreach ($headers as $index => $header) {
            $ref = $this->columnName($index + 1) . '3';
            $headerCells .= '<c r="' . $ref . '" s="2" t="inlineStr"><is><t>' . $this->xml((string) $header) . '</t></is></c>';
        }
        $sheetRows[] = '<row r="3">' . $headerCells . '</row>';
        foreach ($rows as $rowIndex => $values) {
            $rowNumber = $rowIndex + 4;
            $cells = '';
            foreach (array_values($values) as $columnIndex => $value) {
                $ref = $this->columnName($columnIndex + 1) . $rowNumber;
                // inlineStr forces all external/user data to remain literal text (no formulas).
                $cells .= '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">' . $this->xml((string) ($value ?? '')) . '</t></is></c>';
            }
            $sheetRows[] = '<row r="' . $rowNumber . '">' . $cells . '</row>';
        }

        $widths = [8, 26, 28, 32, 14, 16, 20, 40, 18, 22, 22];
        $cols = '';
        foreach ($headers as $index => $_header) {
            $width = $widths[$index] ?? 22;
            $col = $index + 1;
            $cols .= '<col min="' . $col . '" max="' . $col . '" width="' . $width . '" customWidth="1"/>';
        }
        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="3" topLeftCell="A4" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>' . $cols . '</cols><sheetData>' . implode('', $sheetRows) . '</sheetData>'
            . '<autoFilter ref="A3:' . $lastColumn . max(3, $lastRow) . '"/><mergeCells count="1"><mergeCell ref="A1:' . $lastColumn . '1"/></mergeCells></worksheet>';

        $entries = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Rekap Penjual" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="16"/><color rgb="FF1F3550"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF3F6A96"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FF1F3550"/></left><right style="thin"><color rgb="FF1F3550"/></right><top style="thin"><color rgb="FF1F3550"/></top><bottom style="thin"><color rgb="FF1F3550"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>',
            'xl/worksheets/sheet1.xml' => $sheet,
        ];

        $this->writeZip($path, $entries);
    }

    private function writeZip(string $path, array $entries): void
    {
        $file = fopen($path, 'wb');
        if ($file === false) throw new \RuntimeException('Tidak dapat membuat file ekspor.');
        $central = '';
        $offset = 0;
        foreach ($entries as $name => $data) {
            $crc = crc32($data);
            $size = strlen($data);
            $nameLength = strlen($name);
            $local = pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 0, $crc, $size, $size, $nameLength, 0) . $name . $data;
            fwrite($file, $local);
            $central .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 0, $crc, $size, $size, $nameLength, 0, 0, 0, 0, 0, $offset) . $name;
            $offset += strlen($local);
        }
        $centralOffset = $offset;
        fwrite($file, $central);
        $centralSize = strlen($central);
        $count = count($entries);
        fwrite($file, pack('VvvvvVVv', 0x06054b50, 0, 0, $count, $count, $centralSize, $centralOffset, 0));
        fclose($file);
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) { $number--; $name = chr(65 + ($number % 26)) . $name; $number = intdiv($number, 26); }
        return $name;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
