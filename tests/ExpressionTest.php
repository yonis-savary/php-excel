<?php

namespace Tests;

use PhpExcel\Processing\ExpressionSplitter;
use PhpExcel\Xlsx\SpreadSheet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ExpressionTest extends TestCase
{
    public static function formulasExpectedResult() {
        return [
            'simple' => ['1 + 2', 3],
            'order_of_operations' => ['1 - 6 - 10', -15],
            'order_of_operations_with_brackets' => ['(5 * (120 - (20 - 5) - 20))', 425],
            'support_abs_function' => ['10 * ABS(10-20)', 100],
            'support_abs_function_reversed' => ['ABS(10-20) * 10', 100],
        ];
    }

    #[DataProvider('formulasExpectedResult')]
    public function testSplitWithResults(string $formula, float $expectedResult) {
        $expression = (new ExpressionSplitter($formula))->getExpression();

        $this->assertEquals($expectedResult, $expression->getValue(new SpreadSheet()));
    }
}