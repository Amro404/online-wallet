<?php

namespace Src\Domain\Payment\Services;

use Src\Domain\Payment\Contracts\BankAdapterInterface;
use Src\Domain\Payment\DataTransferObjects\PaymentRequest;
use Src\Domain\Payment\DataTransferObjects\PaymentResponse;
use Src\Domain\Payment\Enums\PaymentStatus;
use Src\Infrastructure\Integration\BankClient;

class BankXAdapter implements BankAdapterInterface
{
    public function __construct(
        protected BankClient $httpClient,
        protected PaymentRequestXmlGenerator $paymentRequestXmlGenerator
    ) {}

    public function send(PaymentRequest $paymentRequest): PaymentResponse
    {
        $xml = $this->paymentRequestXmlGenerator->generate($paymentRequest);

        $response = $this->httpClient->post(
            config('bank-integration.bankX.endpoint'),
            $xml,
            ['Content-Type' => 'application/xml']
        );

        $xml = simplexml_load_string($response);

        return PaymentResponse::create(
            reference: (string)$xml->TransactionInfo->ClientReference,
            status: $this->mapStatus((string)$xml->TransactionInfo->Status),
            bankReference: (string)$xml->TransactionInfo->BankReference,
        );
    }

    private function mapStatus(string $bankStatus): PaymentStatus
    {
        return match($bankStatus) {
            'ACCEPTED', 'COMPLETED' => PaymentStatus::COMPLETED,
            'PENDING', 'PROCESSING' => PaymentStatus::PENDING,
            default => PaymentStatus::FAILED
        };
    }

}
