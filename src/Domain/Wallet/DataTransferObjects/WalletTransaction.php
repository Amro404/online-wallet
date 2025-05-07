<?php

namespace Src\Domain\Wallet\DataTransferObjects;

use Src\Domain\Wallet\Enums\WalletTransactionStatus;
use Src\Domain\Wallet\Enums\WalletTransactionType;

class WalletTransaction
{
    public function __construct(
        protected int    $clientId,
        protected int    $walletId,
        protected WalletTransactionType $type,
        protected float  $amount,
        protected WalletTransactionStatus $status,
        protected ?array $meta,
    ) {}

    public static function create(
        int $clientId,
        int $walletId,
        WalletTransactionType $type,
        float $amount,
        WalletTransactionStatus $status,
        ?array $meta = [],
    ): self
    {
        return new self(
            clientId: $clientId,
            walletId: $walletId,
            type: $type,
            amount: $amount,
            status: $status,
            meta: $meta,
        );
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function getType(): string
    {
        return $this->type->value;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getStatus(): string
    {
        return $this->status->value;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }


}
