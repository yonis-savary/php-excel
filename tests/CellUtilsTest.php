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
            'basic_set' => [[
                'A1' => ['A', '1', null],
                'AE85' => ['AE', '85', null],
                'BLABLABLA123456' => ['BLABLABLA', '123456', null],
                'SheetA!A1' => ['A', '1', 'SheetA'],
            ]]
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
    public function testParseAddressForValid(array $addresses) {
        foreach ($addresses as $address => $expectedResult) {
            $this->assertEquals($expectedResult, $this->parseAddress($address));
        }
    }
    
}