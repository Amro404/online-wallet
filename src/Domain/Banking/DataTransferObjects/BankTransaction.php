<?php

namespace Src\Domain\Banking\DataTransferObjects;

use Illuminate\Support\Str;
use Ramsey\Uuid\UuidInterface;
use Src\Domain\Banking\Enums\BankType;

class BankTransaction
{
    protected UuidInterface $id;
    protected int $clientId;
    public function __construct(
        private readonly string $date,
        private readonly string $amount,
        private readonly string $currency,
        private readonly string $reference,
        private readonly BankType $bank,
        private readonly array $meta,
    ) {
        $this->id = Str::uuid();
    }

    public static function create(string $date, string $amount, string $currency, string $reference, BankType $bank, array $meta): self
    {
        return new self(
            date: $date,
            amount: $amount,
            currency: $currency,
            reference: $reference,
            bank: $bank,
            meta: $meta,
        );
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getBank(): BankType
    {
        return $this->bank;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setClientId(int $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }
}
