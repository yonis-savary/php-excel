<?php

namespace PhpExcel\Processing\Tokens;

use Override;
use PhpExcel\Processing\Expression;
use PhpExcel\Processing\ExpressionSplitter;
use PhpExcel\Xlsx\SpreadSheet;
use RuntimeException;

class CustomFunction extends Expression
{
    const SUPPORTED_FUNCTIONS = [
        'abs'
    ];

    protected string $functionName;
    protected Expression $subExpression;

    public function __construct(
        protected string $fullExpression
    )
    {
        list($functionName, $rest) = explode('(', $fullExpression, 2);
        $rest = substr($rest, 0, strlen($rest)-1);

        $this->subExpression = (new ExpressionSplitter($rest))->getExpression();
        $this->functionName = strtolower($functionName);

        if (!in_array($this->functionName, self::SUPPORTED_FUNCTIONS))
            throw new RuntimeException("Unsupported function [$functionName]");
    }

    #[Override]
	public function getValue(SpreadSheet $spreadSheet): mixed
    {
        $subExpressionValue = $this->subExpression->getValue($spreadSheet);
        switch ($this->functionName) {
            case 'abs':
                return abs($subExpressionValue);
        }
        throw new RuntimeException("Fatal: Unsupported function [$this->functionName]");
    }
}