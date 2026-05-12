<?php

namespace Tests;

use InvalidArgumentException;
use PhpExcel\Abstract\CellUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CellUtilsTest extends TestCase
{
    use CellUtils;

    public static function getInvalidAddresses() {
        return [
            'basic_set' => [[
                "958A",
                "9AA4",
                "A",
                "8",
                "Hello",
            ]]
        ];
    }

    public static function getValidAddressesAndResults() {
        return [
            'base' => ['A1', ['A', '1', null, false, false]],
            'base_extended' => ['AE85', ['AE', '85', null, false, false]],
            'base_long' => ['BLABLABLA123456', ['BLABLABLA', '123456', null, false, false]],
            'sheet_name' => ['SheetA!A1', ['A', '1', 'SheetA', false, false]],
            'fixed_row' => ['B$35', ['B', '35', null, false, true]],
            'fixed_column' => ['$B35', ['B', '35', null, true, false]],
            'fixed_both' => ['$B$35', ['B', '35', null, true, true]],
            'hybrid' => ['SheetC!$C$12', ['C', '12', 'SheetC', true, true]]
        ];
    }

    #[DataProvider('getInvalidAddresses')]
    public function testParseAddressForInvalid(array $invalids) {
        foreach ($invalids as $invalidAddress) {
            $this->expectException(InvalidArgumentException::class);
            $this->parseAddress($invalidAddress);
        }
    }

    #[DataProvider('getValidAddressesAndResults')]
    public function testParseAddressForValid(string $address, array $expectedResult) {
        $this->assertEquals($expectedResult, $this->parseAddress($address)->toArray());
    }

    public static function getInvalidRangeAddresses() {
        return [
            '1' => ["958A:"],
            '2' => [":9AA4"],
            '3' => ["A:5"],
            '4' => ["8:12"],
            '5' => ["Hello:Goodbye"],
        ];
    }

    public static function getValidAddressRangesAndResults() {
        return [
            'basic_set' => [
                'A1:B2',
                [
                    null,
                    ['A', '1', null, false, false],
                    ['B', '2', null, false, false]
                ]
            ],
            'sheet_name' => [
                'SheetB!A$5:$C8',
                [
                    'SheetB',
                    ['A', '5', 'SheetB', false, true],
                    ['C', '8', 'SheetB', true, false],
                ]
            ]
        ];
    }

    #[DataProvider('getInvalidRangeAddresses')]
    public function testParseRangeAddressForInvalid(string $invalidAddress) {
        $this->expectException(InvalidArgumentException::class);
        $this->parseAddress($invalidAddress);
    }


    #[DataProvider('getValidAddressRangesAndResults')]
    public function testParseAddressRangeForValid(string $address, array $expectedResult) {
        $range = $this->parseRangeAddress($address);
        list($expectedSheet, $expectedStart, $expectedEnd) = $expectedResult;

        $this->assertEquals($expectedSheet, $range->sheet);
        $this->assertEquals($expectedStart, $range->start->toArray());
        $this->assertEquals($expectedEnd, $range->end->toArray());
    }

    public static function getRangeAndSet() {
        return [
            'basic_range' => [
                'A1:C3',
                [
                    'A1', 'A2', 'A3',
                    'B1', 'B2', 'B3',
                    'C1', 'C2', 'C3',
                ]
            ]
        ];
    }

    #[DataProvider('getRangeAndSet')]
    public function testGetRangeSet(string $range, array $expectedSet) {
        $this->assertEquals(
            $expectedSet,
            $this->getCellRangeSet($range)
        );
    }
}