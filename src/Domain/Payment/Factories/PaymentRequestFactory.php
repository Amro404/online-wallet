<?php

namespace Src\Domain\Payment\Factories;

use Src\Domain\Payment\DataTransferObjects\PaymentRequest;
use Src\Domain\Payment\Entities\ReceiverInfo;
use Src\Domain\Payment\Entities\SenderInfo;
use Src\Domain\Payment\ValueObjects\Amount;
use Src\Domain\Payment\ValueObjects\ChargeDetails;
use Src\Domain\Payment\ValueObjects\PaymentType;
use Src\Domain\Payment\ValueObjects\Reference;

class PaymentRequestFactory
{

    public static function fromRequest(array $data): PaymentRequest
    {
        $reference = Reference::create();
        $amount = Amount::create($data['amount'], $data['currency']);
        $senderInfo = SenderInfo::create($data['sender_account_number']);
        $receiverInfo = ReceiverInfo::create(
            $data['receiver_bank_code'],
            $data['receiver_account_number'],
            $data['receiver_beneficiary_name']
        );
        $paymentType = PaymentType::create($data['payment_type']);
        $chargeDetails = ChargeDetails::create($data['charge_details']);
        $date = now()->format('Y-m-d H:i:sP');
        $notes = $data['notes'] ?? [];

        return PaymentRequest::create(
            reference: $reference,
            amount: $amount,
            senderInfo: $senderInfo,
            receiverInfo: $receiverInfo,
            paymentType: $paymentType,
            chargeDetails: $chargeDetails,
            clientId: $data['client_id'],
            date: $date,
            notes: $notes,
        );
    }
}
