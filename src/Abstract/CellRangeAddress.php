<?php

namespace PhpExcel\Abstract;

class CellRangeAddress
{
    public function __construct(
        public readonly ?string $sheet,
        public readonly ?CellAddress $start,
        public readonly ?CellAddress $end,
    )
    {
    }
}