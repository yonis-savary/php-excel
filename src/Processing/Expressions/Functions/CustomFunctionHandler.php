<?php

namespace PhpExcel\Processing\Expressions\Functions;

use PhpExcel\Processing\Expressions\Expression;
use PhpExcel\Xlsx\SpreadSheet;
use RuntimeException;

abstract class CustomFunctionHandler
{
    /** @var Expression[] */
    protected array $parameters = [];

    public static function createAndHandle(SpreadSheet $spreadSheet, Expression ...$parameters): mixed {
        $instance = new static(...$parameters);
        return $instance->handle($spreadSheet);
    }

    public function __construct(Expression ...$parameters)
    {
        $this->parameters = $parameters;
    }

    abstract public function handle(SpreadSheet $spreadsheet): mixed;

    protected function needAtLeastNParameters(int $n) {
        if (count($this->parameters) < $n)
            throw new RuntimeException("Error, the function " . basename(static::class) . " needs at least $n parameters");
    }

    protected function needNParameters(int $n) {
        if (count($this->parameters) != $n)
            throw new RuntimeException("Error, the function " . basename(static::class) . " needs at least $n parameters");
    }

}