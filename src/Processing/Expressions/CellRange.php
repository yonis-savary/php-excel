<?php

namespace PhpExcel\Processing\Expressions;;

use InvalidArgumentException;
use Override;
use PhpExcel\Abstract\CellRangeAddress;
use PhpExcel\Abstract\CellUtils;
use PhpExcel\Xlsx\SpreadSheet;
use PhpExcel\Xlsx\Xl\Worksheets\CellCollection;
use RuntimeException;

class CellRange extends Expression {
    use CellUtils;

    protected CellRangeAddress $address;

    public function __construct(
        protected string $expression
    )
    {
        if (!$this->isCellRange($expression))
            throw new InvalidArgumentException("Invalid expression given to cell range [$expression]");

        $this->address = $this->parseRangeAddress($expression);
    }

    #[Override]
    public function getValue(SpreadSheet $spreadSheet): CellCollection
    {
        $result = $spreadSheet->resolve($this->expression);
        if (! $result instanceof CellCollection)
            throw new RuntimeException("CellRange getValue expects a CellCollection, got " . $result::class);

        return $result;
    }
}