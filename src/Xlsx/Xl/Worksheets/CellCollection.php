<?php

namespace PhpExcel\Xlsx\Xl\Worksheets;

class CellCollection
{
    public $cells = [];

    public function addCell(Cell &$cell) {
        $this->cells[] = $cell;
    }
}