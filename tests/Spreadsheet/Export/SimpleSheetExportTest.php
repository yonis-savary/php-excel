<?php

namespace Tests\Spreadsheet\Export;

use PhpExcel\Xlsx\SpreadSheet;
use PHPUnit\Framework\TestCase;
use Tests\Utils\TestSpreadsheets;

class SimpleSheetExportTest extends TestCase
{
    use TestSpreadsheets;

    public function testSimpleSheetExport() {
        $s = new SpreadSheet();
        $s->writeAssoc([
            'A1' => '5',
            'A2' => '8',
            'A3' => '=A1+A2',
        ]);

        $this->assertEquals($this->readFixture('SimpleSheet'), $s->toArray());
    }
}