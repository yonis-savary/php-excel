<?php

namespace PhpExcel\Processing\Tokens;

use Override;
use PhpExcel\Processing\Expression;
use PhpExcel\Xlsx\SpreadSheet;

class Cell extends Expression {
    public function __construct(
        protected string $expression
    )
    {
    }

    #[Override]
    public function getValue(SpreadSheet $spreadSheet): mixed
    {
        return $spreadSheet->resolveValue($this->expression);
    }
}