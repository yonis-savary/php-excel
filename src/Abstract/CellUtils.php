<?php

namespace PhpExcel\Abstract;

use InvalidArgumentException;

trait CellUtils
{
    /**
     * @param string $address address such as A1, B2, C45, AE85...
     * @return array{string,int,string} As (Column, Row, Sheet)
     */
    protected function parseAddress(string $address): array
    {
        if (!preg_match("~^(\w+!)?([A-Z]+)([0-9]+)$~i", $address, $matches)) 
            throw new InvalidArgumentException("Invalid address [$address]");

        return [$matches[2], $matches[3], $matches[1] ? substr($matches[1], 0, -1) : null];
    }

    public function autoTypeValue(mixed $value): mixed {
        $loweredValue = strtolower($value);
        if (is_numeric($value)) {
            return (float) $value;
        }
        else if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            return substr($value, 1, -1);
        }
        else if (in_array($loweredValue, ['true', 'false'])) {
            return $loweredValue === 'true';
        }

        return $value;
    }

    public function isCellAddress(mixed $expression): bool {
        return preg_match("~^(\w+!)?([A-Z]+)([0-9]+)$~i", $expression);
    }
}