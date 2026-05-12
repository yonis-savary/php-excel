<?php

namespace PhpExcel\Processing\Tokens;

use PhpExcel\Processing\StringStream;
use RuntimeException;

class Tokenisator {
    protected StringStream $stream;

    public static function tokenize(string $string): TokenGroup {
        return (new self($string))->getTokenGroup();
    }

    public function __construct(
        protected string $string
    )
    {
        $this->stream = new StringStream($string);
    }

    protected function crashForInvalidExpression() {
        throw new RuntimeException("Found invalid expression " . $this->string);
    }

    public function tokenizeString(string $startChar='"'): Token {
        $fullString = $startChar;

        $ignoreNextClosing = false;
        while (true) {
            $char = $this->stream->next();
            if (is_null($char))
                $this->crashForInvalidExpression();

            $fullString .= $char;
            $ignoreNextClosing = $char === '\\';
            if ($char === $startChar) {
                if ($ignoreNextClosing)
                    continue;

                return new Token($fullString);
            }
        }

    }

    public function tokenizeParenthesis(): TokenGroup {

        $parenthesisStack = ['('];

        $parenthesisContent = '';
        while (true) {
            $char = $this->stream->next();
            if (is_null($char))
                $this->crashForInvalidExpression();

            $parenthesisContent .= $char;
            switch ($char) {
                case ')':
                    array_pop($parenthesisStack);
                    break;
                case '(':
                    $parenthesisStack[] = '(';
                    break;
            }

            if (!count($parenthesisStack)) {
                $substring = substr($parenthesisContent, 0, -1);
                return (new Tokenisator($substring))->getTokenGroup();
            }
        }

    }

    public function getTokenGroup(): TokenGroup {

        $group = new TokenGroup;
        $currentExpression = "";

        $this->stream->rewind();

        $pushTokens = function(Token|TokenGroup ...$tokens) use (&$group, &$currentExpression) {
            $group->push(...$tokens);
            $currentExpression = "";
        };

        $flushCurrentExpression = function(int $cropper=0) use (&$currentExpression, $pushTokens) {
            $cropped = substr($currentExpression, 0, $cropper < 0 ? $cropper : null);
            if (strlen(trim($cropped))) {
                $pushTokens(new Token($cropped));
            } else {
            }
            $currentExpression = '';
        };

        while (true) {
            $char = $this->stream->next();
            if (is_null($char))
                break;

            if ($char === ' ') {
                if (trim($currentExpression))
                    $pushTokens(new Token($currentExpression));
                else
                    $currentExpression = '';
                continue;
            }

            $currentExpression .= $char;
            switch ($char) {
                case ";":
                    $flushCurrentExpression(-1);
                    $pushTokens(new Token($char));
                    break;
                case "+":
                case "-":
                case "/":
                case "*":
                case "=":
                    $flushCurrentExpression(-1);
                    $pushTokens(new Token($char));
                    break;
                case "<":
                case ">":
                    if ($_canBeEqual = $this->stream->eatIf('=')) {
                        $flushCurrentExpression(-1);
                        $pushTokens(new Token($char . "="));
                    } else {
                        $flushCurrentExpression(-1);
                        $pushTokens(new Token($char));
                    }
                    break;
                case '"':
                    $flushCurrentExpression(-1);
                    $stringToken = $this->tokenizeString($char);
                    $pushTokens($stringToken);
                    break;
                case '(':
                    $parenthesisToken = $this->tokenizeParenthesis();
                    if (strlen($currentExpression) > 1) { // Not just a parenthesis, a function.
                        $functionToken = new TokenGroup(TokenGroup::TYPE_FUNCTION);
                        $functionToken->push(new Token(substr($currentExpression, 0, -1)), ...$parenthesisToken->getTokens());
                        $pushTokens($functionToken);
                    } else {
                        $flushCurrentExpression(-1);
                        $pushTokens($parenthesisToken);
                    }
                    break;
            }
        }

        $flushCurrentExpression();

        return $group;
    }
}