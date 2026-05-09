<?php

namespace PhpExcel\Xlsx\Xl\Worksheets;

use Override;
use PhpExcel\Abstract\ArchiveFile;

class Worksheet extends ArchiveFile {

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
}