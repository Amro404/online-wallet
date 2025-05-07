<?php

namespace Src\Domain\Payment\DataTransferObjects;

use Src\Domain\Payment\Entities\ReceiverInfo;
use Src\Domain\Payment\Entities\SenderInfo;
use Src\Domain\Payment\ValueObjects\Amount;
use Src\Domain\Payment\ValueObjects\ChargeDetails;
use Src\Domain\Payment\ValueObjects\PaymentType;
use Src\Domain\Payment\ValueObjects\Reference;

class PaymentRequest
{
    public function __construct(
        protected Reference $reference,
        protected Amount $amount,
        protected SenderInfo $senderInfo,
        protected ReceiverInfo $receiverInfo,
        protected PaymentType $paymentType,
        protected ChargeDetails $chargeDetails,
        protected int $clientId,
        protected string $date,
        protected array $notes = []
    ) {}

    public static function create(
        Reference $reference,
        Amount $amount,
        SenderInfo $senderInfo,
        ReceiverInfo $receiverInfo,
        PaymentType $paymentType,
        ChargeDetails $chargeDetails,
        int $clientId,
        string $date,
        array $notes = []
    ): self
    {
        return new self(
            reference: $reference,
            amount: $amount,
            senderInfo: $senderInfo,
            receiverInfo: $receiverInfo,
            paymentType: $paymentType,
            chargeDetails: $chargeDetails,
            clientId: $clientId,
            date: $date,
            notes: $notes
        );
    }

    public function addNote(string $note): void
    {
        $this->notes[] = $note;
    }

    public function reference(): Reference
    {
        return $this->reference;
    }

    public function date(): string
    {
        return $this->date;
    }

    public function amount(): Amount
    {
        return $this->amount;
    }

    public function senderInfo(): SenderInfo
    {
        return $this->senderInfo;
    }

    public function receiverInfo(): ReceiverInfo
    {
        return $this->receiverInfo;
    }

    public function paymentType(): PaymentType
    {
        return $this->paymentType;
    }

    public function chargeDetails(): ChargeDetails
    {
        return $this->chargeDetails;
    }

    public function notes(): array
    {
        return $this->notes;
    }

    public function hasNotes(): bool
    {
        return !empty($this->notes);
    }

    public function clientId(): int
    {
        return $this->clientId;
    }
}
