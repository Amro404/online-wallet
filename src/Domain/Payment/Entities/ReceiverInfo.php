<?php

namespace Src\Domain\Payment\Entities;

class ReceiverInfo
{
    public function __construct(
        protected string $bankCode,
        protected string $accountNumber,
        protected string $beneficiaryName
    ) {}

    public static function create(string $bankCode, string $accountNumber, string $beneficiaryName): self
    {
        return new self($bankCode, $accountNumber, $beneficiaryName);
    }

    public function getBankCode(): string
    {
        return $this->bankCode;
    }
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }
    public function getBeneficiaryName(): string
    {
        return $this->beneficiaryName;
    }
}
