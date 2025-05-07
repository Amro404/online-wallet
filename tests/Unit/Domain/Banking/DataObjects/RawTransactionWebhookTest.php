<?php

namespace Tests\Unit\Domain\Banking\DataObjects;

use PHPUnit\Framework\TestCase;
use Src\Domain\Banking\DataTransferObjects\RawTransactionWebhook;
use Src\Domain\Banking\Enums\BankType;
use Src\Domain\Banking\Enums\RawTransactionWebhookStatus;

class RawTransactionWebhookTest extends TestCase
{
    private function createBankWebhookPayload(
        int $clientId,
        string $content,
        BankType $bankType,
        array $headers = []
    ): RawTransactionWebhook
    {
        return RawTransactionWebhook::create(
            clientId: $clientId,
            content: $content,
            bankType: $bankType,
            headers: $headers
        );

    }

    public function test_it_creates_payload_with_required_fields(): void
    {
        $headers = ['X-Merchant-Id' => 'CLIENT-12345'];

        $webhook = $this->createBankWebhookPayload(
            clientId: 1,
            content: '20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81',
            bankType: BankType::FOODICS,
            headers: $headers
        );

        $this->assertEquals(1, $webhook->getClientId());
        $this->assertEquals('20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81', $webhook->getContent());
        $this->assertEquals(BankType::FOODICS, $webhook->getBankType());
        $this->assertEquals($headers, $webhook->getHeaders());
    }

    public function test_it_throws_for_invalid_bank_identifier(): void
    {
        $headers = ['X-Merchant-Id' => 'CLIENT-12345'];
        $this->expectException(\TypeError::class);
        $this->createBankWebhookPayload(
            clientId: 1,
            content: '20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81',
            bankType: 'invalid_bank',
            headers: $headers
        );
    }


    public function test_it_initializes_with_pending_state(): void
    {
        $webhook = $this->createBankWebhookPayload(
            clientId: 1,
            content: '20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81',
            bankType: BankType::FOODICS
        );
        $this->assertEquals(RawTransactionWebhookStatus::PENDING, $webhook->getStatus());

    }

}
