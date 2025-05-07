<?php

namespace Src\Domain\Payment\Services;

use Src\Domain\Payment\DataTransferObjects\PaymentRequest;

class PaymentRequestXmlGenerator
{
    public function generate(PaymentRequest $paymentRequest): string
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('PaymentRequestMessage');
        $dom->appendChild($root);

        $transferInfo = $dom->createElement('TransferInfo');
        $transferInfo->appendChild($dom->createElement('Reference', $paymentRequest->reference()->value()));
        $transferInfo->appendChild($dom->createElement('Date', $paymentRequest->date()));
        $transferInfo->appendChild($dom->createElement('Amount', $paymentRequest->amount()->value()));
        $transferInfo->appendChild($dom->createElement('Currency', $paymentRequest->amount()->currency()));
        $root->appendChild($transferInfo);

        $senderInfo = $dom->createElement('SenderInfo');
        $senderInfo->appendChild($dom->createElement('AccountNumber', $paymentRequest->senderInfo()->getAccountNumber()));
        $root->appendChild($senderInfo);

        $receiverInfo = $dom->createElement('ReceiverInfo');
        $receiverInfo->appendChild($dom->createElement('BankCode', $paymentRequest->receiverInfo()->getBankCode()));
        $receiverInfo->appendChild($dom->createElement('AccountNumber', $paymentRequest->receiverInfo()->getAccountNumber()));
        $receiverInfo->appendChild($dom->createElement('BeneficiaryName', $paymentRequest->receiverInfo()->getBeneficiaryName()));
        $root->appendChild($receiverInfo);

        if ($paymentRequest->hasNotes()) {
            $notesElement = $dom->createElement('Notes');
            foreach ($paymentRequest->notes() as $note) {
                $notesElement->appendChild($dom->createElement('Note', $note));
            }
            $root->appendChild($notesElement);
        }

        if ($paymentRequest->paymentType()->isRequired()) {
            $root->appendChild($dom->createElement('PaymentType', $paymentRequest->paymentType()->value()));
        }

        if ($paymentRequest->chargeDetails()->isRequired()) {
            $root->appendChild($dom->createElement('ChargeDetails', $paymentRequest->chargeDetails()->value()));
        }

        return $dom->saveXML();
    }
}
