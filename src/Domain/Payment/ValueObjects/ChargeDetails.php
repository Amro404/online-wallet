<?php

namespace Src\Domain\Payment\ValueObjects;

class ChargeDetails
{
    public const SHA = 'SHA';
    public const RB = 'RB';
    public const BEN = 'BEN';

    public function __construct(protected string $value)
    {
        if (!in_array($value, [self::SHA, self::RB, self::BEN])) {
            throw new \InvalidArgumentException('Invalid charge details');
        }
    }

    public static function create(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isRequired(): bool
    {
        return $this->value !== self::SHA;
    }

    public function isShared(): bool
    {
        return $this->value === self::SHA;
    }
}
