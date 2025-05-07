<?php

namespace Tests\Unit\Domain\Banking\DataObjects;

use PHPUnit\Framework\TestCase;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Src\Domain\Banking\Enums\BankType;

class BankWebhookPayloadTest extends TestCase
{
    private function createBankWebhookPayload(
        string $bank,
        string $content,
        string $merchantId,
        array $headers = []
    ): BankWebhookPayload
    {
        return BankWebhookPayload::create(
            bank: $bank,
            content: $content,
            merchantId: $merchantId,
            headers: $headers
        );
    }

    public function test_it_creates_payload_with_required_fields()
    {
        $headers = ['X-Merchant-Id' => 'CLIENT-12345'];

        $foodicsPayload = $this->createBankWebhookPayload(
            bank: 'foodics',
            content: '20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81',
            merchantId: 'CLIENT-12345',
            headers:  $headers
        );

        $acmePayload = $this->createBankWebhookPayload(
            bank: 'acme',
            content: '2000,50//202506159000021//20250615',
            merchantId: 'CLIENT-12345',
            headers:  $headers
        );

        $foodicsTransaction = '20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81';
        $acmeTransaction = '2000,50//202506159000021//20250615';

        $this->assertEquals(BankType::FOODICS, $foodicsPayload->getBankIdentifier());
        $this->assertEquals($foodicsTransaction, $foodicsPayload->getContent());
        $this->assertEquals('CLIENT-12345', $foodicsPayload->getMerchantId());;
        $this->assertEquals($headers, $foodicsPayload->getHeaders());

        $this->assertEquals(BankType::ACME, $acmePayload->getBankIdentifier());
        $this->assertEquals($acmeTransaction, $acmePayload->getContent());
        $this->assertEquals('CLIENT-12345', $acmePayload->getMerchantId());;
        $this->assertEquals($headers, $acmePayload->getHeaders());
    }

    public function test_it_throws_for_invalid_bank_identifier()
    {
        $headers = ['X-Merchant-Id' => 'CLIENT-12345'];

        $this->expectException(\InvalidArgumentException::class);

        $this->createBankWebhookPayload(
            bank: 'invalid_bank',
            content: '20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81',
            merchantId: 'CLIENT-12345',
            headers:  $headers
        );

    }

}
