<?php

namespace PhpExcel\Processing\Expressions;;

use Override;
use PhpExcel\Processing\Expressions\Functions\Abs;
use PhpExcel\Processing\Expressions\Functions\IfCondition;
use PhpExcel\Xlsx\SpreadSheet;
use RuntimeException;

class CustomFunction extends Expression
{
    const SUPPORTED_FUNCTIONS = [
        'abs',
        'if'
    ];

    /** @var Expression[] */
    protected array $parameterExpressions = [];

    public function __construct(
        protected string $functionName,
        Expression ...$parameterExpressions
    )
    {
        $this->functionName = strtolower($this->functionName); // normalize
        $this->parameterExpressions = $parameterExpressions;

        if (!in_array($this->functionName, self::SUPPORTED_FUNCTIONS))
            throw new RuntimeException("Unsupported function [$functionName]");
    }

    #[Override]
	public function getValue(SpreadSheet $spreadSheet): mixed
    {
        switch ($this->functionName) {
            case 'abs': return Abs::createAndHandle($spreadSheet, ...$this->parameterExpressions);
            case 'if': return IfCondition::createAndHandle($spreadSheet, ...$this->parameterExpressions);
        }
        throw new RuntimeException("Fatal: Unsupported function [$this->functionName]");
    }
}