<?php

namespace Tests\Spreadsheet;

use PhpExcel\Xlsx\SpreadSheet;
use PHPUnit\Framework\TestCase;

class SheetSelectionTest extends TestCase
{
    public function testWriteAndRead() {
        $s = new SpreadSheet();
        $s->createSheet('First');
        $s->createSheet('Second');

        $s->write('A1', 'foo');
        $this->assertEquals('foo', $s->query('A1'));

        $s->selectSheet('Second');

        $s->write('A1', 'bar');
        $this->assertEquals('bar', $s->query('A1'));

        $this->assertEquals('foo', $s->query('First!A1'));

    }
}