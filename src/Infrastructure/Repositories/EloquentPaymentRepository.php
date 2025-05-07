<?php

namespace Src\Infrastructure\Repositories;

use App\Models\Payment;
use Src\Domain\Payment\DataTransferObjects\PaymentRequest;
use Src\Domain\Payment\DataTransferObjects\PaymentResponse;
use Src\Domain\Payment\Enums\PaymentStatus;
use Src\Domain\Payment\Repositories\PaymentRepositoryInterface;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{

    public function create(PaymentRequest $paymentRequest): Payment
    {
        return Payment::query()->create([
            'client_id' => $paymentRequest->clientId(),
            'amount' => $paymentRequest->amount()->value(),
            'reference' => $paymentRequest->reference()->value(),
            'currency' => $paymentRequest->amount()->currency(),
            'status' => PaymentStatus::PENDING,
            'sender_account_number' => $paymentRequest->senderInfo()->getAccountNumber(),
            'receiver_bank_code' => $paymentRequest->receiverInfo()->getBankCode(),
            'receiver_account_number' => $paymentRequest->receiverInfo()->getAccountNumber(),
            'receiver_beneficiary_name' => $paymentRequest->receiverInfo()->getBeneficiaryName(),
            'payment_type' => $paymentRequest->paymentType()->value(),
            'charge_details' => $paymentRequest->chargeDetails()->value(),
            'transfer_date' => $paymentRequest->date(),
            'notes' => json_encode($paymentRequest->notes()),
        ]);
    }

    public function updateFromResponse(string $reference, PaymentResponse $paymentResponse): void
    {
        Payment::query()->where('reference', $reference)->update([
            'status' => $paymentResponse->getStatus()->value,
        ]);
    }

    public function updateStatus(string $reference, PaymentStatus $status): void
    {
        Payment::query()->where('reference', $reference)->update([
            'status' => $status->value
        ]);
    }
}
