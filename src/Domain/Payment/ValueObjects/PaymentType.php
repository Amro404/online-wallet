<?php

namespace Src\Domain\Payment\ValueObjects;

class PaymentType
{
    public const DEFAULT_TYPE = 99;
    public const INTERNAL_TRANSFER = 101;
    public const CUSTOMER_CREDIT_TRANSFER = 421;
    public const STANDING_ORDER = 301;

    public function __construct(protected int $value) {
        if (!in_array($value, [self::DEFAULT_TYPE, self::INTERNAL_TRANSFER, self::CUSTOMER_CREDIT_TRANSFER, self::STANDING_ORDER])) {
            throw new \InvalidArgumentException('Invalid payment type');
        }
    }

    public static function create(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function isRequired(): bool
    {
        return $this->value !== self::DEFAULT_TYPE;
    }
}
