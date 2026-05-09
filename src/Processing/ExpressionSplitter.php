<?php

namespace PhpExcel\Processing;

use PhpExcel\Processing\Tokens\Addition;
use PhpExcel\Processing\Tokens\Cell;
use PhpExcel\Processing\Tokens\CustomFunction;
use PhpExcel\Processing\Tokens\Division;
use PhpExcel\Processing\Tokens\Multiply;
use PhpExcel\Processing\Tokens\RawValue;
use PhpExcel\Processing\Tokens\Subtraction;
use RuntimeException;

class ExpressionSplitter
{
    protected int $i = 0;
    protected string $string;
    protected array $tokens;
    protected array $bracketStack = [];

    public function __construct(string $string)
    {
        $this->string = $string;
        $this->tokens = str_split($this->string);
        $this->bracketStack = [];
    }

    protected function getSubSplitterExpression(string $substring): Expression {
        $subsplitter = new self($substring);
        return $subsplitter->getExpression();
    }

    protected function getBracketContent(): string {
        $subExpression = "";

        for (; $this->i>=0; $this->i--) {
            $token = $this->token();
            $subExpression = $token . $subExpression;
            switch ($token) {
                case ')':
                    $this->bracketStack[] = ')';
                    break;
                case '(':
                    array_pop($this->bracketStack);
                    if (!count($this->bracketStack)) {
                        // A  function name could be present before
                        $n = $this->i - 1;
                        $gotFunction = false;
                        while ($char = $this->tokens[$n] ?? false) {
                            if (!(ctype_alnum($char) || $char === '_'))
                                break;
                            $gotFunction = true;
                            $subExpression = $char . $subExpression;
                            $n-=1;
                        }
                        if ($gotFunction) {
                            $this->i = $n;
                            return $subExpression;
                        }

                        return substr($subExpression, 1, strlen($subExpression)-2);
                    }
                    break;
            }
        }
        throw new RuntimeException("invalid expression found " . $this->string);
    }

    protected function getBracketSubExpression(): Expression {
        $bracketContent = $this->getBracketContent();
        return preg_match('~^[a-z0-9_]+\(~i', $bracketContent)
            ? new CustomFunction($bracketContent)
            : $this->getSubSplitterExpression($bracketContent);
    }

    protected function token(): string {
        return $this->string[$this->i];
    }

    protected function getRestExpression(): Expression {
        return (new self(substr($this->string, 0, $this->i)))->getExpression();
    }

    public function transformToExpression(string|Expression $value) {
        if ($value instanceof Expression)
            return $value;

        if (preg_match("~^(\w+!)?[A-Z]+[0-9]+$~i", $value)) {
            // A cell reference
            return new Cell($value);
        }
        return new RawValue($value);
    }

    public function getExpression(): Expression
    {
        $currentExpression = '';

        for ($this->i=count($this->tokens)-1; $this->i>=0; $this->i--) {
            $token = $this->token();
            if ($token === ' ')
                continue;

            switch ($token) {
                case ')': 
                    $currentExpression = $this->getBracketSubExpression();
                    break;
                case '+': return new Addition($this->getRestExpression(), $this->transformToExpression($currentExpression));
                case '-': return new Subtraction($this->getRestExpression(), $this->transformToExpression($currentExpression));
                case '*': return new Multiply($this->getRestExpression(), $this->transformToExpression($currentExpression));
                case '/': return new Division($this->getRestExpression(), $this->transformToExpression($currentExpression));
                default:
                    $currentExpression = $token . $currentExpression;
                    break;
            }
        }

        return $this->transformToExpression($currentExpression);
    }
}