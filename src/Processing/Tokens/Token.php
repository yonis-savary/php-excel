<?php

namespace PhpExcel\Processing\Tokens;

class Token {
    public function __construct(
        public readonly string $string
    )
    {}

    public function __toString()
    {
        return $this->string;
    }
}