<?php

namespace Src\Domain\Banking\Enums;

enum BankType: string
{
    case ACME = 'acme';
    case FOODICS = 'foodics';

    public static function fromIdentifier(string $identifier): self
    {
        return match(strtolower($identifier)) {
            'foodics' => self::FOODICS,
            'acme' => self::ACME,
            default => throw new \InvalidArgumentException("Unknown bank identifier: $identifier")
        };
    }
}
