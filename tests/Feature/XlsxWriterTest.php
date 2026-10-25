<?php

namespace Tests\Unit;

use App\Support\XlsxWriter;
use PHPUnit\Framework\TestCase;

class XlsxWriterTest extends TestCase
{
    public function test_it_writes_valid_uncompressed_openxml_parts_and_literal_text_cells(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'siswamart-xlsx-');
        try {
            (new XlsxWriter())->write($path, ['Nama', 'NISN'], [['=HYPERLINK("x")', '0012345678']], 'Rekapitulasi Penjual');
            $contents = file_get_contents($path);
            self::assertStringStartsWith("PK\x03\x04", $contents);
            self::assertStringContainsString('xl/workbook.xml', $contents);
            self::assertStringContainsString('xl/worksheets/sheet1.xml', $contents);
            self::assertStringContainsString('t="inlineStr"', $contents);
            self::assertStringContainsString('=HYPERLINK(&quot;x&quot;)', $contents);
            self::assertStringContainsString('0012345678', $contents);
            self::assertStringContainsString('A3:B4', $contents);
        } finally {
            @unlink($path);
        }
    }

    public function test_it_writes_a_header_only_sheet_for_empty_data(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'siswamart-xlsx-empty-');
        try {
            (new XlsxWriter())->write($path, ['Nama'], [], 'Rekapitulasi Penjual');
            $contents = file_get_contents($path);
            self::assertStringStartsWith("PK\x03\x04", $contents);
            self::assertStringContainsString('A3:A3', $contents);
        } finally {
            @unlink($path);
        }
    }
}
