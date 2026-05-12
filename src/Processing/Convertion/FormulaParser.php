<?php

namespace PhpExcel\Processing\Convertion;

use InvalidArgumentException;
use PhpExcel\Processing\Expressions\Expression;
use PhpExcel\Processing\Tokens\Tokenizator;
use PhpExcel\Xlsx\Xl\Worksheets\Cell;

class FormulaParser {

    public static function expressionFromFormula(string $formula): Expression {
        $tokenizator = new Tokenizator($formula);
        $convertor = new TokenConvertor();
        return $convertor->convertGroup($tokenizator->getTokenGroup());
    }

    public function __invoke(Cell $cell): Expression
    {
        if (!$cell->formula)
            throw new InvalidArgumentException('given cell must have a formula');

        return self::expressionFromFormula($cell->formula);
    }
}