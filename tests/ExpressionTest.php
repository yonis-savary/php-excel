<?php

namespace Tests;

use PhpExcel\Processing\Convertion\FormulaParser;
use PhpExcel\Xlsx\SpreadSheet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ExpressionTest extends TestCase
{
    public static function formulasExpectedResult() {
        return [
            'simple' => ['1 + 2', 3],
            'simple_short' => ['1+2', 3],
            'order_of_operations' => ['1 - 6 - 10', -15],
            'order_of_operations_with_brackets' => ['(5 * (120 - (20 - 5) - 20))', 425],
            'support_abs_function' => ['10 * ABS(10-20)', 100],
            'support_abs_function_reversed' => ['ABS(10-20) * 10', 100],
            'parse_string_value' => ['"Hello"', 'Hello'],
            'support_if' => ['IF(1<10;"yes";"no")', 'yes'],
            'support_if_else' => ['IF(10<1;"yes";"no")', 'no'],
            'support_greater_or_equal__greater' => ['IF(5>=1;"ok";"not ok")', "ok"],
            'support_greater_or_equal__equal' => ['IF(5>=5;"ok";"not ok")', "ok"],
            'support_nested_function' => ['IF(1<2; ABS(10-20); 0)', 10]
        ];
    }

    #[DataProvider('formulasExpectedResult')]
    public function testSplitWithResults(string $formula, mixed $expectedResult) {
        $expression =  FormulaParser::expressionFromFormula($formula);

        $this->assertEquals($expectedResult, $expression->getValue(new SpreadSheet()));
    }
}