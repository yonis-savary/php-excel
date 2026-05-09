<?php

namespace PhpExcel\Processing;

use PhpExcel\Processing\Tokens\Cell as TokensCell;
use PhpExcel\Xlsx\SpreadSheet;
use PhpExcel\Xlsx\Xl\Worksheets\Cell;
use RuntimeException;

class FormulaParser {


    public function __construct(
        protected SpreadSheet $spreadSheet
    ){}

    protected array $bracketStack = [];
    protected ?string $lastBracket = null;

    public function getCellExpression(Cell $cell): Expression {
        if (!$cell->formula)
            throw new RuntimeException("Could not resolve formula for non-formula cell " . $cell->region);

        return (new ExpressionSplitter($cell->formula))->getExpression();
    }
}