<?php

namespace PhpExcel\Processing\Convertion;

use PhpExcel\Abstract\CellUtils;
use PhpExcel\Processing\Expressions\Cell;
use PhpExcel\Processing\Expressions\CellRange;
use PhpExcel\Processing\Expressions\Operations\Addition;
use PhpExcel\Processing\Expressions\Operations\Division;
use PhpExcel\Processing\Expressions\Operations\Multiplication;
use PhpExcel\Processing\Expressions\Operations\Subtraction;
use PhpExcel\Processing\Expressions\Comparisons\Equal;
use PhpExcel\Processing\Expressions\Comparisons\GreaterOrEqual;
use PhpExcel\Processing\Expressions\Comparisons\GreaterThan;
use PhpExcel\Processing\Expressions\Comparisons\LesserOrEqual;
use PhpExcel\Processing\Expressions\Comparisons\LesserThan;
use PhpExcel\Processing\Expressions\CustomFunction;
use PhpExcel\Processing\Expressions\Expression;
use PhpExcel\Processing\Expressions\RawValue;
use PhpExcel\Processing\Tokens\Token;
use PhpExcel\Processing\Tokens\TokenGroup;

class TokenConvertor
{
    use CellUtils;

    protected function convertFunction(TokenGroup $tokenGroup): Expression {
        $tokens = $tokenGroup->getTokens();
        $functionName = $tokens[0];
        $parameterTokens = array_slice($tokens, 1);

        $parameters = [];
        $currentParameterTokens = [];
        foreach ($parameterTokens as $tokenOrGroup) {
            if ($tokenOrGroup instanceof Token) {
                if ($tokenOrGroup->string === ';') {
                    $parameters[] = new TokenGroup(null, ...$currentParameterTokens);
                    $currentParameterTokens = [];
                    continue;
                }
            }
            $currentParameterTokens[] = $tokenOrGroup;
        }
        if (count($currentParameterTokens))
            $parameters[] = new TokenGroup(null, ...$currentParameterTokens);

        $parameters = collect($parameters)
            ->map(fn($paramTokenGroup) => self::convertGroup($paramTokenGroup))
            ->all();


        return new CustomFunction($functionName, ...$parameters);
    }

    public function convertGroup(TokenGroup $tokenGroup): Expression {
        switch ($tokenGroup->specialType) {
            case TokenGroup::TYPE_FUNCTION:
                return self::convertFunction($tokenGroup);
        }

        $tokens = $tokenGroup->getTokens();
        $i=count($tokens)-1;

        $restAsExpression = function() use (&$tokens, $i) {
            $group = new TokenGroup(null, ...array_slice($tokens, 0, $i-1));
            return self::convertGroup($group);
        };

        $lastExpression = null;

        for (; $i>=0; $i--) {
            $tokenOrGroup = $tokens[$i];

            if ($tokenOrGroup instanceof TokenGroup) {
                $lastExpression = self::convertGroup($tokenOrGroup);
                continue;
            }
            /** @var Token */
            $token = $tokenOrGroup;

            switch ((string) $token) {
                // Comparisons
                case '=': return new Equal($restAsExpression(), $lastExpression);
                case '>': return new GreaterThan($restAsExpression(), $lastExpression);
                case '<': return new LesserThan($restAsExpression(), $lastExpression);
                case '>=': return new GreaterOrEqual($restAsExpression(), $lastExpression);
                case '<=': return new LesserOrEqual($restAsExpression(), $lastExpression);
                // Operations
                case '+': return new Addition($restAsExpression(), $lastExpression);
                case '-': return new Subtraction($restAsExpression(), $lastExpression);
                case '*': return new Multiplication($restAsExpression(), $lastExpression);
                case '/': return new Division($restAsExpression(), $lastExpression);
            }

            $tokenValue = (string) $token;
            if ($this->isCellAddress($tokenValue))
                $lastExpression = new Cell($tokenValue);
            else if ($this->isCellRange($tokenValue))
                $lastExpression = new CellRange($tokenValue);
            else 
                $lastExpression = new RawValue($token->string);
        }
        return $lastExpression;
    }
}