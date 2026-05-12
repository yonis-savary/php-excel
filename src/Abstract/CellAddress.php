<?php

namespace PhpExcel\Abstract;

class CellAddress
{
    public function __construct(
        public ?string $sheet,
        public string $column,
        public int $row,
        public bool $isColumnFixed,
        public bool $isRowFixed,
    )
    {
    }

    public function toArray(): array {
        return [$this->column, $this->row, $this->sheet, $this->isColumnFixed, $this->isRowFixed];
    }
}