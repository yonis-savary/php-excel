<?php

namespace PhpExcel;

use PhpExcel\Xlsx\SpreadSheet;
use PhpExcel\Xlsx\Xl\Worksheets\Cell;
use PhpExcel\Xlsx\Xl\Worksheets\CellCollection;
use PhpExcel\Xlsx\Xl\Worksheets\Worksheet;
use RuntimeException;

class CellResolver {
    public function __construct(
        protected SpreadSheet $spreadSheet
    )
    {}

    protected function resolveCellCollection(Worksheet $worksheet, string $expression): CellCollection {
        throw new RuntimeException("Not implemented yet.");
    }

    public function resolve(string $expression): Cell|CellCollection {
        $sheet = $this->spreadSheet->activeWorksheet;

        if (preg_match("~^.+!.+$~", $expression)) {
            list($sheetName, $cellName) = explode('!', $expression);
        } else {
            $sheetName = null;
            $cellName = $expression;
        }

        if ($sheetName) {
            if (! $sheet = $this->spreadSheet->getSheet($sheetName))
                throw new RuntimeException("Requested sheet [$sheetName] not found");
        }

        if (preg_match("~^.+:.+$~", $cellName))
            return $this->resolveCellCollection($sheet, $cellName);

        if ($cell = $sheet->getCell($cellName))
            return $cell;

        throw new RuntimeException("$expression cell not found");
    }
}