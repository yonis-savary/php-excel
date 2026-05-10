<?php

namespace PhpExcel\Processing\Tokens;

class TokenGroup
{
    const TYPE_FUNCTION = 'function';

    protected array $tokens = [];
    
    public static function wrap(TokenGroup|Token $tokenOrGroup): TokenGroup {
        return $tokenOrGroup instanceof TokenGroup
            ? $tokenOrGroup
            : new TokenGroup(null, $tokenOrGroup);
    }

    public function __construct(
        public readonly ?string $specialType = null,
        TokenGroup|Token ...$tokens
    )
    {
        $this->tokens = $tokens;
    }


    public function push(TokenGroup|Token ...$tokens) {
        array_push($this->tokens, ...$tokens);
    }

    public function getTokens() {
        return $this->tokens;
    }

    public function toArray(): array {
        $array = [];
        foreach ($this->tokens as $tokenOrGroup) {
            if ($tokenOrGroup instanceof TokenGroup) {
                $array[] = $tokenOrGroup->toArray();
            } else {
                $array[] = (string) $tokenOrGroup;
            }
        }
        return $array;
    }
}