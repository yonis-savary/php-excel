<?php

namespace PhpExcel\Processing;

use PhpExcel\Xlsx\SpreadSheet;

abstract class Expression
{
    abstract public function getValue(SpreadSheet $spreadSheet): mixed;
}