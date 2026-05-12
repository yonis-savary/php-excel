<?php

namespace Tests;

use PhpExcel\Abstract\ColumnUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ColumnUtilsTest extends TestCase
{
    use ColumnUtils;

    public static function getNextColumnTestSet(){
        return [
            'increment' => [[
                'A' => 'B',
                'B' => 'C',
                'Z' => 'AA',
                'AA' => 'AB',
                'AZ' => 'BA',
                'ZZ' => 'AAA',
            ]]
        ];
    }

    #[DataProvider('getNextColumnTestSet')]
    public function testNextColumn(array $set) {
        foreach ($set as $value => $expectedResult) {
            $this->assertEquals($expectedResult, $this->nextColumn($value));
        }
    }
}