<?php

namespace PhpExcel\Xlsx\Xl\Worksheets;

use Override;
use PhpExcel\Abstract\ArchiveFile;
use PhpExcel\Abstract\CellUtils;

class Worksheet extends ArchiveFile 
{
    use CellUtils;

    /** @var Row[] */
    protected array $rows = [];

    /** @var array<string,Cell> */
    protected array $cellMap = [];

    #[Override]
    public function dataChanged()
    {
        $rows = $this->xml->xpath('/x:worksheet/x:sheetData/x:row');
        foreach ($rows as $rowXml) {
            $row = new Row($rowXml);
            $this->rows[$row->number] = $row;
        }

        $this->reloadIndex();
    }

    public function reloadIndex() {
        foreach ($this->rows as &$row) {
            foreach ($row->cells as &$cell) {
                $this->cellMap[$cell->region] = $cell;
            }
        }
    }

    public function getCell(string $cellName): ?Cell {
        return $this->cellMap[$cellName] ?? null;
    }

    public function write(string $cellName, string $valueOrFormula): Cell {
        list($column, $row) = $this->parseAddress($cellName)->toArray();
        if (!array_key_exists($row, $this->rows)) {
            $this->rows[$row] = new Row();
            $this->rows[$row]->number = $row;
        }

        $cell = $this->rows[$row]->write($column, $valueOrFormula);
        $this->cellMap[$cellName] = $cell;
        return $cell;
    }
}