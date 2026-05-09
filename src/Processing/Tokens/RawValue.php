<?php

namespace PhpExcel\Processing\Tokens;

use Override;
use PhpExcel\Processing\Expression;
use PhpExcel\Xlsx\SpreadSheet;

class RawValue extends Expression
{
    public function __construct(
        protected mixed $value
    )
    {
        if (is_numeric($value))
            $value = (float) $value;
    }

    #[Override]
    public function getValue(SpreadSheet $spreadSheet): mixed
    {
        return $this->value;
    }
}