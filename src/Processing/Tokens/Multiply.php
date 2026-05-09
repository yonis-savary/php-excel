<?php

namespace PhpExcel\Processing\Tokens;

use Override;
use PhpExcel\Processing\Expression;
use PhpExcel\Xlsx\SpreadSheet;

class Multiply extends Expression {
    public function __construct(
        protected Expression $exA,
        protected Expression $exB,
    )
    {}

    #[Override]
    public function getValue(SpreadSheet $spreadSheet): mixed
    {
        return $this->exA->getValue($spreadSheet)  * $this->exB->getValue($spreadSheet);
    }
}