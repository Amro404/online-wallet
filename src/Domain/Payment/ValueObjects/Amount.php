<?php

namespace Src\Domain\Payment\ValueObjects;

class Amount
{
    public function __construct(protected float $value, protected string $currency)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        if (!in_array($currency, ['SAR', 'USD', 'EGP'])) {
            throw new \InvalidArgumentException('Invalid currency');
        }
    }


    public static function create(float $value, string $currency): self
    {
        return new self($value, $currency);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function currency(): string
    {
        return $this->currency ?? 'SAR';
    }
}
