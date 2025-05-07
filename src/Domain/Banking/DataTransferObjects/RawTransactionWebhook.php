<?php

namespace Src\Domain\Banking\DataTransferObjects;

use Src\Domain\Banking\Enums\BankType;
use Src\Domain\Banking\Enums\RawTransactionWebhookStatus;

class RawTransactionWebhook
{
    protected RawTransactionWebhookStatus $status;
    public function __construct(
        protected int $clientId,
        protected string $content,
        protected BankType $bankType,
        protected array $headers = []
    )
    {
        $this->status = RawTransactionWebhookStatus::PENDING;
    }

    public static function create(int $clientId, string $content, BankType $bankType, array $headers = []): self
    {
        return new self(
            clientId: $clientId,
            content: $content,
            bankType: $bankType,
            headers: $headers
        );
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBankType(): BankType
    {
        return $this->bankType;
    }

    public function getStatus(): RawTransactionWebhookStatus
    {
        return $this->status;
    }


}
