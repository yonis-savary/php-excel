<?php

namespace PhpExcel\Processing\Expressions;;

use Override;
use PhpExcel\Processing\Expressions\Functions\Abs;
use PhpExcel\Processing\Expressions\Functions\CustomFunctionHandler;
use PhpExcel\Processing\Expressions\Functions\IfCondition;
use PhpExcel\Processing\Expressions\Functions\Sum;
use PhpExcel\Xlsx\SpreadSheet;
use RuntimeException;

class CustomFunction extends Expression
{
    const SUPPORTED_FUNCTIONS = [
        'abs' => Abs::class,
        'if' => IfCondition::class,
        'sum' => Sum::class
    ];

    /** @var Expression[] */
    protected array $parameterExpressions = [];

    /** @var class-string<CustomFunctionHandler> */
    protected string $functionClass;

    public function __construct(
        protected string $functionName,
        Expression ...$parameterExpressions
    )
    {
        $this->functionName = strtolower($this->functionName); // normalize
        $this->parameterExpressions = $parameterExpressions;

        if (!array_key_exists($this->functionName, self::SUPPORTED_FUNCTIONS))
            throw new RuntimeException("Unsupported function [$functionName]");

        $this->functionClass = self::SUPPORTED_FUNCTIONS[$this->functionName];
    }

    #[Override]
	public function getValue(SpreadSheet $spreadSheet): mixed
    {
        return $this->functionClass::createAndHandle($spreadSheet, ...$this->parameterExpressions);
    }
}