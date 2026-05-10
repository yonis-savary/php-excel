<?php

namespace PhpExcel\Processing\Expressions;;

use Override;
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