<?php

namespace PhpExcel\Processing\Expressions\Functions;

use Override;
use PhpExcel\Processing\Expressions\CellRange;
use PhpExcel\Processing\Expressions\Expression;
use PhpExcel\Xlsx\SpreadSheet;
use PhpExcel\Xlsx\Xl\Worksheets\Cell;

class Sum extends CustomFunctionHandler
{
    /**
     * @var Expression[]
     */
    protected array $expressions = [];

    public function __construct(
        Expression ...$expressions
    )
    {
        $this->expressions = $expressions;
    }

    #[Override]
    public function handle(SpreadSheet $spreadSheet): mixed
    {
        $sum = 0;
        foreach ($this->expressions as $expression) {
            if ($expression instanceof CellRange) {
                $sum += $expression->getValue($spreadSheet)
                    ->collect()
                    ->map(fn(Cell $cell) => $spreadSheet->resolveValue($cell->region, $cell))
                    ->sum();
            } else {
                $sum += $expression->getValue($spreadSheet);
            }
        }
        return $sum;
    }
}