<?php

namespace Src\Domain\Payment\Entities;

class SenderInfo
{
    public function __construct(protected string $accountNumber) {}

    public static function create(string $accountNumber): self
    {
        return new self($accountNumber);
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }
}
