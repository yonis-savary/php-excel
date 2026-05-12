<?php

namespace PhpExcel\Abstract;

use InvalidArgumentException;

trait ColumnUtils
{
    public function nextColumn(string $column): string {
        if (!strlen($column))
            throw new InvalidArgumentException('Column must not be empty');

        $deductions = '';
        do {
            if (!strlen($column))
                return $deductions . 'A';

            $lastChar = substr($column, -1, 1);
            $column = substr($column, 0, -1);
            if ($lastChar === 'Z') {
                $deductions .= 'A';
                continue;
            }

            $nextChar = chr(ord($lastChar) + 1);
            return $column .  $nextChar . $deductions;
        } while (true);
    }
}