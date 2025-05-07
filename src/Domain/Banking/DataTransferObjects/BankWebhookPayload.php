<?php

namespace Src\Domain\Banking\DataTransferObjects;

use Src\Domain\Banking\Enums\BankType;

class BankWebhookPayload
{
    public function __construct(
        protected BankType $bankIdentifier,
        protected string $content,
        protected ?string $merchantId = null, // provider merchant id
        protected array $headers = [],
    ) {}

    public static function create(string $bank, string $content, string $merchantId = null, array $headers = []): self
    {
        return new self(
            bankIdentifier: BankType::fromIdentifier($bank),
            content: $content,
            merchantId: $merchantId,
            headers: $headers
        );
    }

    public function getBankIdentifier(): BankType
    {
        return $this->bankIdentifier;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getMerchantId(): ?string
    {
        return $this->merchantId;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
