<?php

namespace Src\Domain\Payment\DataTransferObjects;

use Src\Domain\Payment\Enums\PaymentStatus;

class PaymentResponse
{
    public function __construct(
        protected string $reference,
        protected PaymentStatus $status,
        protected ?string $bankReference,
        protected readonly ?string $message = null
    ) {}

    public static function create(string $reference, PaymentStatus $status, ?string $bankReference, ?string $message = null): self
    {
        return new self(
            reference: $reference,
            status: $status,
            bankReference: $bankReference,
            message: $message,

        );
    }
    public function getReference(): string
    {
        return $this->reference;
    }

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function getBankReference(): ?string
    {
        return $this->bankReference;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

}
