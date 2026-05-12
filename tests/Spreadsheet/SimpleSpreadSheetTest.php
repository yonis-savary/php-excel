<?php

namespace Tests\Spreadsheet;

use PhpExcel\Xlsx\SpreadSheet;
use PHPUnit\Framework\TestCase;

class SimpleSpreadSheetTest extends TestCase
{
    public function testWriteAndRead() {
        $s = new SpreadSheet();
        $s->write('A1', 'Hello');

        $this->assertEquals('Hello', $s->query('A1'));
    }

    public function testReWriteAndRead() {
        $s = new SpreadSheet();

        $s->write('A1', '1');
        $this->assertEquals('1', $s->query('A1'));

        $s->write('A1', '2');
        $this->assertEquals('2', $s->query('A1'));
    }

    public function testWriteFormulaAndRead() {
        $s = new SpreadSheet();
        $s->write('A1', '3');
        $s->write('B1', '5');

        $s->write('C1', '=A1+B1');
        $this->assertEquals('8', $s->query('C1'));

        $s->write('C1', '=$A1+B$1');
        $this->assertEquals('8', $s->query('C1'));
    }

    public function testSumOrRange() {
        $s = new SpreadSheet();
        $s->writeAssoc([
            'A1' => 2,
            'A2' => 3,
            'A3' => 10,
            'A4' => '=SUM(A1:A3)'
        ]);

        $this->assertEquals(15, $s->query('A4'));
    }
}