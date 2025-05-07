<?php

namespace Src\Domain\Banking\ValueObjects;

class Money
{
    public function __construct(private readonly float $amount, private  string $currency)
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }

        if (strlen($currency) !== 3) {
            throw new \InvalidArgumentException('Currency must be 3 characters');
        }

        $this->currency = strtoupper($currency);
    }

    public static function create(string|float $amount, string $currency): self
    {
        if (is_string($amount)) {
            $amount = (float) str_replace(',', '.', $amount);
        }

        return new self($amount, $currency);
    }
    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

}
