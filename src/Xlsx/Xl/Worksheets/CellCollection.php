<?php

namespace PhpExcel\Xlsx\Xl\Worksheets;

use Illuminate\Support\Collection;

class CellCollection
{
    public $cells = [];

    public function __construct(Cell ...$cells)
    {
        $this->cells = $cells;
    }

    public function addCell(Cell $cell) {
        $this->cells[] = $cell;
    }

    public function collect(): Collection
    {
        return collect($this->cells);
    }
}