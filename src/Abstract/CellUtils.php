<?php

namespace PhpExcel\Abstract;

use InvalidArgumentException;

trait CellUtils
{
    use ColumnUtils;

    const SHEET_REG = '(\w+!)';
    const SINGLE_CELL_REG = '(\\$?[A-Z]+)(\\$?[0-9]+)';

    const CELL_REG = '~^'. self::SHEET_REG . '?' . self::SINGLE_CELL_REG .'$~i';
    const CELL_RANGE_REG = '~^'. self::SHEET_REG . '?' . self::SINGLE_CELL_REG . '.' . self::SINGLE_CELL_REG .'$~i';

    /**
     * @param string $address address such as A1, B2, C45, AE85...
     */
    protected function parseAddress(string $address): CellAddress
    {
        if (!preg_match(self::CELL_REG, $address, $matches)) 
            throw new InvalidArgumentException("Invalid address [$address]");

        list($_, $sheet, $column, $row) = $matches;
        $sheet = $sheet ? substr($sheet, 0, -1): null;
        $isColumnFixed = str_starts_with($column, '$');
        $isRowFixed = str_starts_with($row, '$');

        $column = $isColumnFixed ? substr($column, 1) : $column;
        $row = $isRowFixed ? substr($row, 1) : $row;

        return new CellAddress(
            $sheet,
            $column,
            $row,
            $isColumnFixed,
            $isRowFixed
        );
    }

    protected function parseRangeAddress(string $address): CellRangeAddress
    {
        list($startExpr, $endExpr) = explode(':', $address, 2);
        $startAddress = $this->parseAddress($startExpr);
        $endAddress = $this->parseAddress($endExpr);

        $endAddress->sheet = $startAddress->sheet;

        return new CellRangeAddress($startAddress->sheet, $startAddress, $endAddress);
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
        return preg_match(self::CELL_REG, $expression);
    }

    public function isCellRange(mixed $expression): bool {
        return preg_match(self::CELL_RANGE_REG, $expression);
    }

    public function getCellRangeSet(CellRangeAddress|string $range): array {
        if (is_string($range))
            $range = $this->parseRangeAddress($range);

        $startColumn = $range->start->column;
        $endColumn = $range->end->column;

        $startRow = $range->start->row;
        $endRow = $range->end->row;

        if ($endColumn < $startColumn)
            throw new InvalidArgumentException('End column must be greater than start column');

        $set = [];
        for ($column = $startColumn; $column <= $endColumn; $column = $this->nextColumn($column)) {
            for ($row = $startRow; $row <= $endRow; $row++) {
                $set[] = $column . $row;
            }
        }
        return $set;
    }
}