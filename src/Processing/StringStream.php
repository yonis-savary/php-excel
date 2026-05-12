<?php

namespace PhpExcel\Processing;

class StringStream {
    protected array $chars = [];
    protected int $size = 0;
    protected int $i = 0;

    public function __construct(
        protected string $string
    )
    {
        $this->size = strlen($string);
        $this->chars = str_split($string);
    }

    public function next(): ?string {
        return $this->i >= $this->size
            ? null
            : $this->chars[$this->i++];
    }

    public function stepBack(int $n=1) {
        $this->i -= $n;
    }

    public function rewind() {
        $this->i = 0;
    }

    public function peek(): ?string {
        return $this->chars[$this->i];
    }

    public function eatIf(string $expected): ?string {
        $actual = $this->peek();
        if ($expected === $actual)
            return $this->next();

        return null;
    }
}