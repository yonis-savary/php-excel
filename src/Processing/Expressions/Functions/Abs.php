<?php

namespace PhpExcel\Processing\Expressions\Functions;

use Override;
use PhpExcel\Xlsx\SpreadSheet;

class Abs extends CustomFunctionHandler
{
    #[Override]
    public function handle(SpreadSheet $spreadSheet): mixed
    {
        $this->needNParameters(1);
        $firstParam = $this->parameters[0];
        $v = $firstParam->getValue($spreadSheet);
        return abs($v);
    }
}