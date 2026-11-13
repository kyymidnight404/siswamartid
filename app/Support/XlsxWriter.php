<?php

namespace App\Support;

use App\Exports\Concerns\StylesSheet;

/**
 * Pure PHP OpenXML (.xlsx) writer supporting single and multi-sheet workbooks,
 * rich styling (SiswaMart design system), number/date formatting, and native charts
 * without requiring PHP zip extension or external dependencies.
 */
class XlsxWriter
{
    use StylesSheet;

    /**
     * Backward-compatible single sheet write method.
     */
    public function write(string $path, array $headers, array $rows, string $title, array $widths = []): void
    {
        $sheet = [
            'name' => 'Rekap Penjual',
            'title' => $title,
            'metadata' => ['Diekspor: '.date('d/m/Y H:i').' WIB'],
            'headers' => $headers,
            'rows' => $rows,
            'widths' => $widths,
            'autoFilter' => true,
            'freezePane' => true,
        ];

        $this->writeWorkbook($path, [$sheet]);
    }

    /**
     * Write full workbook with one or multiple sheets.
     *
     * @param  array<int, array<string, mixed>>  $sheets
     */
    public function writeWorkbook(string $path, array $sheets): void
    {
        $entries = [];
        $sheetCount = count($sheets);

        // 1. Workbook XML & Relationships
        $workbookSheetsXml = '';
        $workbookRelsXml = '<Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';

        $hasAnyChart = false;
        $chartIndex = 1;

        foreach ($sheets as $idx => $sheetData) {
            $sheetId = $idx + 1;
            $sheetName = mb_substr((string) ($sheetData['name'] ?? ('Sheet'.$sheetId)), 0, 31);
            $sheetRelId = 'rIdSheet'.$sheetId;

            $workbookSheetsXml .= '<sheet name="'.$this->xml($sheetName).'" sheetId="'.$sheetId.'" r:id="'.$sheetRelId.'"/>';
            $workbookRelsXml .= '<Relationship Id="'.$sheetRelId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$sheetId.'.xml"/>';
            $contentTypesXml .= '<Override PartName="/xl/worksheets/sheet'.$sheetId.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';

            $withChart = ! empty($sheetData['chart']);
            if ($withChart) {
                $hasAnyChart = true;
            }

            // Build worksheet XML
            $sheetXml = $this->buildWorksheetXml($sheetData, $sheetId, $withChart ? $chartIndex : null);
            $entries['xl/worksheets/sheet'.$sheetId.'.xml'] = $sheetXml;

            if ($withChart) {
                // Drawing rels
                $entries['xl/worksheets/_rels/sheet'.$sheetId.'.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                    .'<Relationship Id="rIdDrawing'.$chartIndex.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing'.$chartIndex.'.xml"/>'
                    .'</Relationships>';

                // Drawing XML
                $entries['xl/drawings/drawing'.$chartIndex.'.xml'] = $this->buildDrawingXml($chartIndex);
                $entries['xl/drawings/_rels/drawing'.$chartIndex.'.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                    .'<Relationship Id="rIdChart'.$chartIndex.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/chart" Target="../charts/chart'.$chartIndex.'.xml"/>'
                    .'</Relationships>';

                // Chart XML
                $entries['xl/charts/chart'.$chartIndex.'.xml'] = $this->buildChartXml($sheetData['chart'], $sheetName);

                $contentTypesXml .= '<Override PartName="/xl/drawings/drawing'.$chartIndex.'.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';
                $contentTypesXml .= '<Override PartName="/xl/charts/chart'.$chartIndex.'.xml" ContentType="application/vnd.openxmlformats-officedocument.drawingml.chart+xml"/>';

                $chartIndex++;
            }
        }

        $contentTypesXml .= '</Types>';

        // Defined print titles if requested
        $definedNamesXml = '';
        foreach ($sheets as $idx => $sheetData) {
            $sheetId = $idx + 1;
            $sheetName = mb_substr((string) ($sheetData['name'] ?? ('Sheet'.$sheetId)), 0, 31);
            $headerRow = ! empty($sheetData['metadata']) ? (count((array) $sheetData['metadata']) + 2) : 2;
            $definedNamesXml .= '<definedName name="_xlnm.Print_Titles" localSheetId="'.$idx.'">\''.$this->xml($sheetName).'\'!$'.$headerRow.':$'.$headerRow.'</definedName>';
        }

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'.$workbookSheetsXml.'</sheets>'
            .($definedNamesXml !== '' ? '<definedNames>'.$definedNamesXml.'</definedNames>' : '')
            .'</workbook>';

        $entries['[Content_Types].xml'] = $contentTypesXml;
        $entries['_rels/.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
        $entries['xl/workbook.xml'] = $workbookXml;
        $entries['xl/_rels/workbook.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$workbookRelsXml
            .'</Relationships>';

        // Stylesheet with SiswaMart palette & number formats
        $entries['xl/styles.xml'] = $this->buildStylesXml();

        $this->writeZip($path, $entries);
    }

    /**
     * Build standard styles XML.
     */
    protected function buildStylesXml(): string
    {
        // Fills:
        // 0: none, 1: gray125, 2: #1F3550 (title), 3: #3F6A96 (header), 4: #F3F6FB (zebra), 5: #E3ECF5 (section/total), 6: #FFF0D4 (amber rank 1)
        // Fonts:
        // 0: Calibri 11 regular #1F3550
        // 1: Calibri 16 bold #FFFFFF
        // 2: Calibri 11 bold #FFFFFF
        // 3: Calibri 10 regular #6B7280 (Muted meta)
        // 4: Calibri 11 bold #1F3550
        // 5: Calibri 11 bold #1F3550 (Section / Total)
        // Number formats:
        // 164: "Rp" #,##0
        // 165: dd/mm/yyyy
        // 166: #,##0
        // 167: 0.0

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="4">'
            .'<numFmt numFmtId="164" formatCode="&quot;Rp&quot;\ #,##0"/>'
            .'<numFmt numFmtId="165" formatCode="dd/mm/yyyy"/>'
            .'<numFmt numFmtId="166" formatCode="#,##0"/>'
            .'<numFmt numFmtId="167" formatCode="0.0"/>'
            .'</numFmts>'
            .'<fonts count="6">'
            .'<font><sz val="11"/><color rgb="FF1F3550"/><name val="Calibri"/></font>' // 0: Normal
            .'<font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>' // 1: Title
            .'<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>' // 2: Header
            .'<font><sz val="10"/><color rgb="FF6B7280"/><name val="Calibri"/></font>' // 3: Muted meta
            .'<font><b/><sz val="11"/><color rgb="FF1F3550"/><name val="Calibri"/></font>' // 4: Bold main
            .'<font><b/><sz val="11"/><color rgb="FF1F3550"/><name val="Calibri"/></font>' // 5: Section label
            .'</fonts>'
            .'<fills count="7">'
            .'<fill><patternFill patternType="none"/></fill>' // 0
            .'<fill><patternFill patternType="gray125"/></fill>' // 1
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF1F3550"/><bgColor indexed="64"/></patternFill></fill>' // 2: Title bg
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF3F6A96"/><bgColor indexed="64"/></patternFill></fill>' // 3: Header bg
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF3F6FB"/><bgColor indexed="64"/></patternFill></fill>' // 4: Alternate bg (#F3F6FB)
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFE3ECF5"/><bgColor indexed="64"/></patternFill></fill>' // 5: Section bg
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFFFF0D4"/><bgColor indexed="64"/></patternFill></fill>' // 6: Amber rank 1
            .'</fills>'
            .'<borders count="2">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>' // 0: None
            .'<border>' // 1: Thin #D1D5DB
            .'<left style="thin"><color rgb="FFD1D5DB"/></left>'
            .'<right style="thin"><color rgb="FFD1D5DB"/></right>'
            .'<top style="thin"><color rgb="FFD1D5DB"/></top>'
            .'<bottom style="thin"><color rgb="FFD1D5DB"/></bottom>'
            .'<diagonal/>'
            .'</border>'
            .'</borders>'
            .'<cellStyleXfs count="1">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
            .'</cellStyleXfs>'
            .'<cellXfs count="28">'
            // 0: General normal left
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            // 1: Title (16pt bold white, navy fill, height 36, vertical center, indent 1)
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="left" indent="1"/></xf>'
            // 2: Header center (11pt bold white, header blue fill, wrapText, height 28, center)
            .'<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="center" wrapText="1"/></xf>'
            // 3: Meta line (Calibri 10 #6B7280, vertical center, indent 1)
            .'<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="left" indent="1"/></xf>'
            // 4: Data row white - text left (indent 1)
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="left" indent="1"/></xf>'
            // 5: Data row alternate - text left (indent 1)
            .'<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="left" indent="1"/></xf>'
            // 6: Data row white - number right (#,##0, indent 1)
            .'<xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="right" indent="1"/></xf>'
            // 7: Data row alternate - number right (#,##0, indent 1)
            .'<xf numFmtId="3" fontId="0" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="right" indent="1"/></xf>'
            // 8: Data row white - currency right ("Rp" #,##0, indent 1)
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="right" indent="1"/></xf>'
            // 9: Data row alternate - currency right ("Rp" #,##0, indent 1)
            .'<xf numFmtId="164" fontId="0" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="right" indent="1"/></xf>'
            // 10: Data row white - percent right (0%, indent 1)
            .'<xf numFmtId="9" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="right" indent="1"/></xf>'
            // 11: Data row alternate - percent right (0%, indent 1)
            .'<xf numFmtId="9" fontId="0" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="right" indent="1"/></xf>'
            // 12: Data row white - date center (dd/mm/yyyy)
            .'<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="center"/></xf>'
            // 13: Data row alternate - date center (dd/mm/yyyy)
            .'<xf numFmtId="165" fontId="0" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="center"/></xf>'
            // 14: Section Label row (bold, fill #E3ECF5, text #1F3550, indent 1)
            .'<xf numFmtId="0" fontId="4" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="left" indent="1"/></xf>'
            // 15: Rank 1 highlight (amber #FFF0D4, bold, center)
            .'<xf numFmtId="0" fontId="4" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="center"/></xf>'
            // 16: Data row white - center
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="center"/></xf>'
            // 17: Data row alternate - center
            .'<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="center"/></xf>'
            // 18: Total row - bold text left (indent 1)
            .'<xf numFmtId="0" fontId="4" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="left" indent="1"/></xf>'
            // 19: Total row - bold number right (#,##0, indent 1)
            .'<xf numFmtId="3" fontId="4" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="right" indent="1"/></xf>'
            // 20: Total row - bold percent right (0%, indent 1)
            .'<xf numFmtId="9" fontId="4" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="right" indent="1"/></xf>'
            // 21: Data row white - wrap text left (indent 1, wrapText 1)
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="left" indent="1" wrapText="1"/></xf>'
            // 22: Data row alternate - wrap text left (indent 1, wrapText 1)
            .'<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="left" indent="1" wrapText="1"/></xf>'
            // 23: Header text left (indent 1, for Metrik & Keterangan header in Ringkasan)
            .'<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="left" indent="1" wrapText="1"/></xf>'
            // 24: Data row white - number center (#,##0, center, for Nilai in Ringkasan)
            .'<xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="center"/></xf>'
            // 25: Data row alternate - number center (#,##0, center, for Nilai in Ringkasan)
            .'<xf numFmtId="3" fontId="0" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="center"/></xf>'
            // 26: Data row white - rating center (0.0, center)
            .'<xf numFmtId="167" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="center"/></xf>'
            // 27: Data row alternate - rating center (0.0, center)
            .'<xf numFmtId="167" fontId="0" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" horizontal="center"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    /**
     * Build single worksheet XML.
     */
    protected function buildWorksheetXml(array $data, int $sheetIndex, ?int $chartIndex): string
    {
        $headers = (array) ($data['headers'] ?? []);
        $rows = (array) ($data['rows'] ?? []);
        $title = (string) ($data['title'] ?? '');
        $metadata = (array) ($data['metadata'] ?? []);
        $widths = (array) ($data['widths'] ?? []);
        $autoFilter = ! empty($data['autoFilter']);
        $freezePane = $data['freezePane'] ?? true;
        $sheetName = (string) ($data['name'] ?? ('Sheet'.$sheetIndex));
        $isRingkasan = ($sheetName === 'Ringkasan');
        $columnCount = max(1, count($headers) ?: count($widths));
        $lastColumn = $this->columnName($columnCount);

        $sheetRows = [];
        $currentRowNumber = 1;

        // Row 1: Title (Merge across columns, height 36, font 16 bold, indent 1)
        if ($title !== '') {
            $sheetRows[] = '<row r="1" ht="36" customHeight="1"><c r="A1" s="1" t="inlineStr"><is><t>'.$this->xml($title).'</t></is></c></row>';
            $currentRowNumber = 2;
        }

        // Metadata rows: font 10, #6B7280, height 20; last row height 28 as spacer
        $metaCount = count($metadata);
        foreach ($metadata as $mIdx => $metaLine) {
            $isLastMeta = ($mIdx === $metaCount - 1);
            $metaHeight = $isLastMeta ? '28' : '20';
            $sheetRows[] = '<row r="'.$currentRowNumber.'" ht="'.$metaHeight.'" customHeight="1"><c r="A'.$currentRowNumber.'" s="3" t="inlineStr"><is><t>'.$this->xml((string) $metaLine).'</t></is></c></row>';
            $currentRowNumber++;
        }

        // Header row: height 28, bold, white, vertical center
        $headerRowNumber = $currentRowNumber;
        if (! empty($headers)) {
            $headerCells = '';
            foreach ($headers as $colIdx => $headerText) {
                $ref = $this->columnName($colIdx + 1).$headerRowNumber;
                if ($isRingkasan) {
                    // Ringkasan: Nilai (col 1) center, Metrik (col 0) & Keterangan (col 2) left with indent 1
                    $headerStyle = ($colIdx === 1) ? 2 : 23;
                } else {
                    // Data tables: center header
                    $headerStyle = 2;
                }
                $headerCells .= '<c r="'.$ref.'" s="'.$headerStyle.'" t="inlineStr"><is><t>'.$this->xml((string) $headerText).'</t></is></c>';
            }
            $sheetRows[] = '<row r="'.$headerRowNumber.'" ht="28" customHeight="1">'.$headerCells.'</row>';
            $currentRowNumber++;
        }

        // Data rows
        $dataStartRowNumber = $currentRowNumber;
        if (empty($rows) && ! empty($data['showEmptyRow'])) {
            $ref = 'A'.$currentRowNumber;
            $sheetRows[] = '<row r="'.$currentRowNumber.'" ht="24" customHeight="1"><c r="'.$ref.'" s="4" t="inlineStr"><is><t>Belum ada data pada periode ini</t></is></c></row>';
            $currentRowNumber++;
        } elseif (! empty($rows)) {
            foreach ($rows as $rIdx => $rowValues) {
                $isAlternate = ($rIdx % 2 === 1);
                $rowCells = '';
                $isSectionRow = false;
                $isTotalRow = false;
                $isHeaderRow = false;
                $isSpacerRow = false;

                // Check if row is a section divider, header, spacer, or total row
                if (isset($rowValues['__type'])) {
                    if ($rowValues['__type'] === 'section') {
                        $isSectionRow = true;
                    } elseif ($rowValues['__type'] === 'total') {
                        $isTotalRow = true;
                    } elseif ($rowValues['__type'] === 'header') {
                        $isHeaderRow = true;
                    } elseif ($rowValues['__type'] === 'spacer') {
                        $isSpacerRow = true;
                    }
                }

                $cellsList = array_values(array_filter($rowValues, fn ($k) => ! str_starts_with((string) $k, '__'), ARRAY_FILTER_USE_KEY));
                $rowLabel = isset($cellsList[0]) ? (is_array($cellsList[0]) ? ($cellsList[0]['value'] ?? '') : (string) $cellsList[0]) : '';

                $maxWrapLen = 0;
                foreach ($cellsList as $cIdx => $cellVal) {
                    $ref = $this->columnName($cIdx + 1).$currentRowNumber;
                    $colHeader = (string) ($headers[$cIdx] ?? '');
                    $rowCells .= $this->renderCellXml(
                        $ref,
                        $cellVal,
                        $isAlternate,
                        $isSectionRow,
                        $isTotalRow,
                        $cIdx,
                        $isRingkasan,
                        $colHeader,
                        $rowLabel,
                        $isHeaderRow,
                        $isSpacerRow
                    );

                    // Track wrap length for dynamic row height
                    if ($isRingkasan && $cIdx === 2) {
                        $strLen = mb_strlen(is_array($cellVal) ? ($cellVal['value'] ?? '') : (string) $cellVal);
                        if ($strLen > $maxWrapLen) {
                            $maxWrapLen = $strLen;
                        }
                    } elseif (! $isRingkasan) {
                        $isWrapCol = (is_array($cellVal) && ($cellVal['type'] ?? '') === 'wrap')
                            || stripos($colHeader, 'Ulasan') !== false
                            || stripos($colHeader, 'Komentar') !== false
                            || stripos($colHeader, 'Aktivitas') !== false;
                        if ($isWrapCol) {
                            $strLen = mb_strlen(is_array($cellVal) ? ($cellVal['value'] ?? '') : (string) $cellVal);
                            if ($strLen > $maxWrapLen) {
                                $maxWrapLen = $strLen;
                            }
                        }
                    }
                }

                // Calculate row height (default 24; expand if wrap text exceeds column width)
                if ($isHeaderRow) {
                    $rowHeight = '28';
                } elseif ($isSpacerRow) {
                    $rowHeight = '14';
                } elseif ($isSectionRow) {
                    $rowHeight = '22';
                } elseif ($isRingkasan && $maxWrapLen > 55) {
                    $rowHeight = (string) max(24, (int) (ceil($maxWrapLen / 50) * 18));
                } elseif (! $isRingkasan && $maxWrapLen > 48) {
                    $rowHeight = (string) max(24, (int) (ceil($maxWrapLen / 45) * 18));
                } else {
                    $rowHeight = '24';
                }

                $sheetRows[] = '<row r="'.$currentRowNumber.'" ht="'.$rowHeight.'" customHeight="1">'.$rowCells.'</row>';
                $currentRowNumber++;
            }
        }

        $lastDataRowNumber = empty($rows) ? $headerRowNumber : ($currentRowNumber - 1);

        // Column widths: Ringkasan [34, 18, 62], explicit widths, or auto-size between 12 and 60 (+ 2 padding)
        $colsXml = '';
        if ($isRingkasan) {
            $colsXml = '<col min="1" max="1" width="34" customWidth="1"/>'
                .'<col min="2" max="2" width="18" customWidth="1"/>'
                .'<col min="3" max="3" width="62" customWidth="1"/>';
        } elseif (! empty($widths)) {
            foreach ($widths as $colIdx => $w) {
                $colNumber = $colIdx + 1;
                $colsXml .= '<col min="'.$colNumber.'" max="'.$colNumber.'" width="'.(int) $w.'" customWidth="1"/>';
            }
        } else {
            foreach ($headers as $colIdx => $headerText) {
                $colNumber = $colIdx + 1;
                $maxLen = mb_strlen((string) $headerText);
                foreach ($rows as $rowValues) {
                    $cellsList = array_values(array_filter($rowValues, fn ($k) => ! str_starts_with((string) $k, '__'), ARRAY_FILTER_USE_KEY));
                    if (! isset($cellsList[$colIdx])) {
                        continue;
                    }
                    $cellLen = $this->estimateCellLength($cellsList[$colIdx]);
                    if ($cellLen > $maxLen) {
                        $maxLen = $cellLen;
                    }
                }
                $w = max(12, min(60, $maxLen + 2));
                $colsXml .= '<col min="'.$colNumber.'" max="'.$colNumber.'" width="'.$w.'" customWidth="1"/>';
            }
            if ($colsXml === '') {
                $colsXml = '<col min="1" max="10" width="22" customWidth="1"/>';
            }
        }

        // View options: hide gridlines, zoom 100%, freeze pane below header, active cell A1
        $sheetViewsXml = '<sheetViews><sheetView workbookViewId="0" showGridLines="0" zoomScale="100" zoomScaleNormal="100">';
        if ($freezePane && ! empty($headers)) {
            $freezeTopRow = $headerRowNumber + 1;
            $sheetViewsXml .= '<pane ySplit="'.$headerRowNumber.'" topLeftCell="A'.$freezeTopRow.'" activePane="bottomLeft" state="frozen"/>';
            $sheetViewsXml .= '<selection pane="bottomLeft" activeCell="A1" sqref="A1"/>';
        } else {
            $sheetViewsXml .= '<selection activeCell="A1" sqref="A1"/>';
        }
        $sheetViewsXml .= '</sheetView></sheetViews>';

        // Sheet tab color: #1F3550 (biru tua) for Ringkasan, #5B8AB5 (biru lebih muda) for others
        $tabColor = $isRingkasan ? '1F3550' : '5B8AB5';
        $sheetPrXml = '<sheetPr><tabColor rgb="FF'.$tabColor.'"/><pageSetUpPr fitToPage="1"/></sheetPr>';

        // AutoFilter on data table sheets only (not on Ringkasan)
        $autoFilterXml = '';
        if (! $isRingkasan && ! empty($headers)) {
            $autoFilterXml = '<autoFilter ref="A'.$headerRowNumber.':'.$lastColumn.max($headerRowNumber, $lastDataRowNumber).'"/>';
        }

        // Merges (Merge title row 1)
        $mergesXml = '';
        if ($title !== '') {
            if ($isRingkasan) {
                $mergesXml = '<mergeCells count="1"><mergeCell ref="A1:C1"/></mergeCells>';
            } elseif ($columnCount > 1) {
                $mergesXml = '<mergeCells count="1"><mergeCell ref="A1:'.$lastColumn.'1"/></mergeCells>';
            }
        }

        // Print setup: 0.5 inch margins, fit to 1 page wide (height automatic)
        $printOptionsXml = '<printOptions horizontalCentered="1"/>';
        $pageMarginsXml = '<pageMargins left="0.5" right="0.5" top="0.5" bottom="0.5" header="0.3" footer="0.3"/>';
        $orientation = (string) ($data['orientation'] ?? 'landscape');
        $pageSetupXml = '<pageSetup paperSize="9" orientation="'.$orientation.'" fitToWidth="1" fitToHeight="0"/>';

        // Drawing / Chart
        $drawingXml = '';
        if ($chartIndex !== null) {
            $drawingXml = '<drawing r:id="rIdDrawing'.$chartIndex.'"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .$sheetPrXml
            .$sheetViewsXml
            .'<cols>'.$colsXml.'</cols>'
            .'<sheetData>'.implode('', $sheetRows).'</sheetData>'
            .$autoFilterXml
            .$mergesXml
            .$printOptionsXml
            .$pageMarginsXml
            .$pageSetupXml
            .$drawingXml
            .'</worksheet>';
    }

    /**
     * Estimate visual display length of a cell value for column auto-sizing.
     */
    protected function estimateCellLength(mixed $cell): int
    {
        if (is_array($cell)) {
            $type = $cell['type'] ?? 'string';
            $val = $cell['value'] ?? '';
            if ($type === 'date') {
                return 10;
            }
            if ($type === 'currency') {
                return strlen(number_format((float) $val, 0, ',', '.')) + 4;
            }
            if ($type === 'percent') {
                return 6;
            }
            if ($type === 'number') {
                return strlen(number_format((float) $val, 0, ',', '.'));
            }

            return mb_strlen((string) $val);
        }
        if (is_int($cell) || is_float($cell)) {
            return strlen(number_format($cell, 0, ',', '.'));
        }

        return mb_strlen((string) ($cell ?? ''));
    }

    /**
     * Render individual cell XML based on type, alignment, and styling.
     */
    protected function renderCellXml(
        string $ref,
        mixed $cellVal,
        bool $isAlternate,
        bool $isSection,
        bool $isTotal,
        int $colIdx = 0,
        bool $isRingkasan = false,
        string $colHeader = '',
        string $rowLabel = '',
        bool $isHeader = false,
        bool $isSpacer = false
    ): string {
        // 0. Spacer Row
        if ($isSpacer) {
            return '<c r="'.$ref.'" s="0"/>';
        }

        // 0.5 Header Row (Sub-header in table)
        if ($isHeader) {
            $text = is_array($cellVal) ? ($cellVal['value'] ?? '') : (string) $cellVal;

            return '<c r="'.$ref.'" s="2" t="inlineStr"><is><t xml:space="preserve">'.$this->xml($text).'</t></is></c>';
        }

        // 1. Ringkasan Sheet
        if ($isRingkasan) {
            // Col A (Metrik): rata kiri, vertical center, indent 1
            if ($colIdx === 0) {
                $text = is_array($cellVal) ? ($cellVal['value'] ?? '') : (string) $cellVal;
                $styleId = $isAlternate ? 5 : 4;

                return '<c r="'.$ref.'" s="'.$styleId.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml($text).'</t></is></c>';
            }

            // Col B (Nilai): SEMUA sel rata TENGAH (horizontal center), vertical center
            if ($colIdx === 1) {
                if (is_array($cellVal)) {
                    $type = $cellVal['type'] ?? 'string';
                    $val = $cellVal['value'] ?? '';

                    if ($type === 'date') {
                        $styleId = $isAlternate ? 13 : 12;
                        if ($val === null || $val === '') {
                            return '<c r="'.$ref.'" s="'.$styleId.'" t="inlineStr"><is><t>-</t></is></c>';
                        }

                        return '<c r="'.$ref.'" s="'.$styleId.'"><v>'.(int) $val.'</v></c>';
                    }

                    if ($type === 'number') {
                        $isRating = stripos($rowLabel, 'Rating') !== false;
                        $styleId = $isRating ? ($isAlternate ? 27 : 26) : ($isAlternate ? 25 : 24);

                        return '<c r="'.$ref.'" s="'.$styleId.'"><v>'.(float) $val.'</v></c>';
                    }

                    if ($type === 'currency') {
                        $styleId = $isAlternate ? 9 : 8;

                        return '<c r="'.$ref.'" s="'.$styleId.'"><v>'.(float) $val.'</v></c>';
                    }

                    if ($type === 'percent') {
                        $styleId = $isAlternate ? 17 : 16;

                        return '<c r="'.$ref.'" s="'.$styleId.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string) $val).'</t></is></c>';
                    }

                    // Default string in Nilai
                    $styleId = $isAlternate ? 17 : 16;

                    return '<c r="'.$ref.'" s="'.$styleId.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string) $val).'</t></is></c>';
                }

                if (is_int($cellVal) || is_float($cellVal)) {
                    $isRating = stripos($rowLabel, 'Rating') !== false;
                    $styleId = $isRating ? ($isAlternate ? 27 : 26) : ($isAlternate ? 25 : 24);

                    return '<c r="'.$ref.'" s="'.$styleId.'"><v>'.$cellVal.'</v></c>';
                }

                // Raw string in Nilai (e.g. "+9 dari 7 hari sebelumnya", "-", etc.)
                $str = (string) ($cellVal ?? '-');
                $styleId = $isAlternate ? 17 : 16;

                return '<c r="'.$ref.'" s="'.$styleId.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml($str).'</t></is></c>';
            }

            // Col C (Keterangan): rata kiri, vertical center, indent 1, wrap text aktif
            $text = is_array($cellVal) ? ($cellVal['value'] ?? '') : (string) $cellVal;
            $styleId = $isAlternate ? 22 : 21;

            return '<c r="'.$ref.'" s="'.$styleId.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml($text).'</t></is></c>';
        }

        // 2. Section Header Row
        if ($isSection) {
            $text = is_array($cellVal) ? ($cellVal['value'] ?? '') : (string) $cellVal;

            return '<c r="'.$ref.'" s="14" t="inlineStr"><is><t xml:space="preserve">'.$this->xml($text).'</t></is></c>';
        }

        // 3. Total Row
        if ($isTotal) {
            if (is_array($cellVal)) {
                $type = $cellVal['type'] ?? 'number';
                $val = $cellVal['value'] ?? 0;
                if ($type === 'percent') {
                    return '<c r="'.$ref.'" s="20"><v>'.(float) $val.'</v></c>';
                }

                return '<c r="'.$ref.'" s="19"><v>'.(float) $val.'</v></c>';
            }
            if (is_numeric($cellVal)) {
                return '<c r="'.$ref.'" s="19"><v>'.(float) $cellVal.'</v></c>';
            }

            return '<c r="'.$ref.'" s="18" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string) $cellVal).'</t></is></c>';
        }

        // 4. Array Structured Cell
        if (is_array($cellVal)) {
            $type = $cellVal['type'] ?? 'string';
            $val = $cellVal['value'] ?? '';
            $highlight = $cellVal['highlight'] ?? null;

            if ($highlight === 'rank1') {
                return '<c r="'.$ref.'" s="15" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string) $val).'</t></is></c>';
            }

            if ($type === 'date') {
                $styleId = $isAlternate ? 13 : 12;
                if ($val === null || $val === '') {
                    return '<c r="'.$ref.'" s="'.$styleId.'" t="inlineStr"><is><t>-</t></is></c>';
                }

                return '<c r="'.$ref.'" s="'.$styleId.'"><v>'.(int) $val.'</v></c>';
            }

            if ($type === 'currency') {
                $styleId = $isAlternate ? 9 : 8;

                return '<c r="'.$ref.'" s="'.$styleId.'"><v>'.(float) $val.'</v></c>';
            }

            if ($type === 'percent') {
                $styleId = $isAlternate ? 11 : 10;

                return '<c r="'.$ref.'" s="'.$styleId.'"><v>'.(float) $val.'</v></c>';
            }

            if ($type === 'number') {
                $isRating = stripos($colHeader, 'Rating') !== false;
                $styleId = $isRating ? ($isAlternate ? 27 : 26) : ($isAlternate ? 7 : 6);

                return '<c r="'.$ref.'" s="'.$styleId.'"><v>'.(float) $val.'</v></c>';
            }

            if ($type === 'center') {
                $styleId = $isAlternate ? 17 : 16;

                return '<c r="'.$ref.'" s="'.$styleId.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string) $val).'</t></is></c>';
            }

            if ($type === 'wrap') {
                $styleId = $isAlternate ? 22 : 21;

                return '<c r="'.$ref.'" s="'.$styleId.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string) $val).'</t></is></c>';
            }

            $styleId = $isAlternate ? 5 : 4;

            return '<c r="'.$ref.'" s="'.$styleId.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string) $val).'</t></is></c>';
        }

        // 5. Scalar Raw Cell
        if (is_int($cellVal) || is_float($cellVal)) {
            $isRating = stripos($colHeader, 'Rating') !== false;
            $styleId = $isRating ? ($isAlternate ? 27 : 26) : ($isAlternate ? 7 : 6);

            return '<c r="'.$ref.'" s="'.$styleId.'"><v>'.$cellVal.'</v></c>';
        }

        // String / default
        $str = (string) ($cellVal ?? '-');
        if (stripos($colHeader, 'Rating') !== false) {
            $styleId = $isAlternate ? 17 : 16;
        } elseif (stripos($colHeader, 'Status') !== false || stripos($colHeader, 'Peringkat') !== false) {
            $styleId = $isAlternate ? 17 : 16;
        } elseif (stripos($colHeader, 'Ulasan') !== false || stripos($colHeader, 'Komentar') !== false || stripos($colHeader, 'Aktivitas') !== false) {
            $styleId = $isAlternate ? 22 : 21;
        } else {
            $styleId = $isAlternate ? 5 : 4;
        }

        // Force inlineStr so values like =1+1 or -promo are literal text and never executed as formula
        return '<c r="'.$ref.'" s="'.$styleId.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml($str).'</t></is></c>';
    }

    /**
     * Build Drawing XML container for native chart.
     */
    protected function buildDrawingXml(int $chartIndex): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
            .'<xdr:twoCellAnchor>'
            .'<xdr:from><xdr:col>4</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>2</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:from>'
            .'<xdr:to><xdr:col>11</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>16</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:to>'
            .'<xdr:graphicFrame macro="">'
            .'<xdr:nvGraphicFramePr><xdr:cNvPr id="2" name="Chart '.$chartIndex.'"/><xdr:cNvGraphicFramePr/></xdr:nvGraphicFramePr>'
            .'<xdr:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/></xdr:xfrm>'
            .'<a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/chart">'
            .'<c:chart xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:id="rIdChart'.$chartIndex.'"/>'
            .'</a:graphicData></a:graphic>'
            .'</xdr:graphicFrame>'
            .'<xdr:clientData/>'
            .'</xdr:twoCellAnchor>'
            .'</xdr:wsDr>';
    }

    /**
     * Build native Bar/Column Chart XML with SiswaMart palette (#3F6A96).
     */
    protected function buildChartXml(array $chartConfig, string $sheetName): string
    {
        $categoriesRange = $chartConfig['categories'] ?? "'$sheetName'!\$A\$4:\$A\$10";
        $valuesRange = $chartConfig['values'] ?? "'$sheetName'!\$C\$4:\$C\$10";
        $title = $chartConfig['title'] ?? 'Views Per Hari';
        $pointCount = (int) ($chartConfig['count'] ?? 7);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<c:chartSpace xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<c:chart>'
            .'<c:title><c:tx><c:rich><a:bodyPr/><a:lstStyle/><a:p><a:r><a:rPr b="1" sz="1200"><a:solidFill><a:srgbClr val="1F3550"/></a:solidFill></a:rPr><a:t>'.$this->xml($title).'</a:t></a:r></a:p></c:rich></c:tx><c:layout/></c:title>'
            .'<c:plotArea>'
            .'<c:layout/>'
            .'<c:barChart>'
            .'<c:barDir val="col"/>'
            .'<c:grouping val="standard"/>'
            .'<c:varyColors val="0"/>'
            .'<c:ser>'
            .'<c:idx val="0"/>'
            .'<c:order val="0"/>'
            .'<c:tx><c:v>Views</c:v></c:tx>'
            .'<c:spPr><a:solidFill><a:srgbClr val="3F6A96"/></a:solidFill><a:ln><a:solidFill><a:srgbClr val="1F3550"/></a:solidFill></a:ln></c:spPr>'
            .'<c:cat><c:strRef><c:f>'.$categoriesRange.'</c:f></c:strRef></c:cat>'
            .'<c:val><c:numRef><c:f>'.$valuesRange.'</c:f></c:numRef></c:val>'
            .'</c:ser>'
            .'<c:axId val="111111"/>'
            .'<c:axId val="222222"/>'
            .'</c:barChart>'
            .'<c:catAx>'
            .'<c:axId val="111111"/>'
            .'<c:scaling><c:orientation val="minMax"/></c:scaling>'
            .'<c:delete val="0"/>'
            .'<c:axPos val="b"/>'
            .'<c:tickLblPos val="nextTo"/>'
            .'<c:crossAx val="222222"/>'
            .'<c:crosses val="autoZero"/>'
            .'<c:auto val="1"/>'
            .'<c:lblAlgn val="ctr"/>'
            .'<c:lblOffset val="100"/>'
            .'</c:catAx>'
            .'<c:valAx>'
            .'<c:axId val="222222"/>'
            .'<c:scaling><c:orientation val="minMax"/></c:scaling>'
            .'<c:delete val="0"/>'
            .'<c:axPos val="l"/>'
            .'<c:majorGridlines/>'
            .'<c:tickLblPos val="nextTo"/>'
            .'<c:crossAx val="111111"/>'
            .'<c:crosses val="autoZero"/>'
            .'</c:valAx>'
            .'</c:plotArea>'
            .'<c:legend><c:legendPos val="r"/><c:layout/></c:legend>'
            .'<c:plotVisOnly val="1"/>'
            .'</c:chart>'
            .'</c:chartSpace>';
    }

    /**
     * Minimal pure-PHP ZIP compressor writing uncompressed or store entries.
     */
    protected function writeZip(string $path, array $entries): void
    {
        $file = fopen($path, 'wb');
        if ($file === false) {
            throw new \RuntimeException('Tidak dapat membuat file ekspor.');
        }

        $central = '';
        $offset = 0;

        foreach ($entries as $name => $data) {
            $crc = crc32($data);
            $size = strlen($data);
            $nameLength = strlen($name);

            // Local file header (PK\x03\x04)
            // Local file header fields: signature, version, flags, method,
            // modification time/date, CRC, compressed/uncompressed sizes,
            // then filename and extra-field lengths.
            $local = pack('VvvvvvVVVvv', 0x04034B50, 20, 0, 0, 0, 0, $crc, $size, $size, $nameLength, 0).$name.$data;
            fwrite($file, $local);

            // Central directory entry (PK\x01\x02)
            $central .= pack('VvvvvvvVVVvvvvvVV', 0x02014B50, 20, 20, 0, 0, 0, 0, $crc, $size, $size, $nameLength, 0, 0, 0, 0, 0, $offset).$name;
            $offset += strlen($local);
        }

        // End of central directory record (PK\x05\x06)
        $centralOffset = $offset;
        fwrite($file, $central);
        $centralSize = strlen($central);
        $count = count($entries);
        fwrite($file, pack('VvvvvVVv', 0x06054B50, 0, 0, $count, $count, $centralSize, $centralOffset, 0));
        fclose($file);
    }

    /**
     * Convert 1-based column number to Excel column letters (A, B, ..., Z, AA, ...).
     */
    protected function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    /**
     * Escape XML special characters.
     */
    protected function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
