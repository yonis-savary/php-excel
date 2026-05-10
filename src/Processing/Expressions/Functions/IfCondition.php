<?php

namespace PhpExcel\Processing\Expressions\Functions;

use PhpExcel\Xlsx\SpreadSheet;
use Override;

class IfCondition extends CustomFunctionHandler
{
    #[Override]
    public function handle(SpreadSheet $spreadsheet): mixed
    {
        $this->needNParameters(3);

        list($condition, $onTrue, $onFalse) = $this->parameters;
        return (bool) $condition->getValue($spreadsheet)
            ? $onTrue->getValue($spreadsheet)
            : $onFalse->getValue($spreadsheet);
    }
}