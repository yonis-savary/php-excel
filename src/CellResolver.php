<?php

namespace PhpExcel;

use PhpExcel\Abstract\CellRangeAddress;
use PhpExcel\Abstract\CellUtils;
use PhpExcel\Xlsx\SpreadSheet;
use PhpExcel\Xlsx\Xl\Worksheets\Cell;
use PhpExcel\Xlsx\Xl\Worksheets\CellCollection;
use PhpExcel\Xlsx\Xl\Worksheets\Worksheet;
use RuntimeException;

class CellResolver
{
    use CellUtils;

    public function __construct(
        protected SpreadSheet $spreadSheet
    )
    {}

    protected function resolveCellCollection(Worksheet $worksheet, string|CellRangeAddress $range): CellCollection {
        if (is_string($range))
            $range = $this->parseRangeAddress($range);

        $cells = collect($this->getCellRangeSet($range))
            ->map(fn($address) => $worksheet->getCell($address))
            ->filter()
            ->all();

        return new CellCollection(...$cells);
    }

    public function resolve(string $expression): Cell|CellCollection {
        $sheet = $this->spreadSheet->activeWorksheet;

        if (preg_match("~^.+!.+$~", $expression)) {
            list($sheetName, $cellName) = explode('!', $expression);
        } else {
            $sheetName = null;
            $cellName = $expression;
        }

        $sheet = $sheetName
            ? $this->spreadSheet->getSheet($sheetName)
            : $this->spreadSheet->getActiveSheet();
        if (! $sheet)
            throw new RuntimeException("Requested sheet [$sheetName] not found");

        if ($this->isCellRange($expression))
            return $this->resolveCellCollection($sheet, $cellName);

        list($col, $row) = $this->parseAddress($cellName)->toArray();
        $cellAddress = $col . $row;

        if ($cell = $sheet->getCell($cellAddress))
            return $cell;

        throw new RuntimeException("$expression cell not found");
    }
}