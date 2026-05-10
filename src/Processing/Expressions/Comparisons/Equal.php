<?php

namespace PhpExcel\Processing\Expressions\Comparisons;

use Override;
use PhpExcel\Processing\Expressions\Expression;
use PhpExcel\Xlsx\SpreadSheet;

class Equal extends Expression {
    public function __construct(
        protected Expression $exA,
        protected Expression $exB,
    )
    {}

    #[Override]
    public function getValue(SpreadSheet $spreadSheet): mixed
    {
        return $this->exA->getValue($spreadSheet) == $this->exB->getValue($spreadSheet);
    }
}