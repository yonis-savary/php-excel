<?php

namespace PhpExcel\Processing\Expressions;;

use Override;
use PhpExcel\Abstract\CellUtils;
use PhpExcel\Xlsx\SpreadSheet;

class RawValue extends Expression
{
    use CellUtils;

    public function __construct(
        protected mixed $value
    )
    {
        $this->value = $this->autoTypeValue($value);
    }

    #[Override]
    public function getValue(SpreadSheet $spreadSheet): mixed
    {
        return $this->value;
    }
}