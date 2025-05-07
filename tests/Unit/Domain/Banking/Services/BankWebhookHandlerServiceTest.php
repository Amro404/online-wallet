<?php

namespace Tests\Unit\Domain\Banking\Services;

use Src\Domain\Banking\Enums\BankType;
use Tests\TestCase;
use Mockery;
use Src\Domain\Banking\Services\BankWebhookHandlerService;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Src\Domain\Banking\Factories\BankWebhookParserFactory;
use Src\Domain\Banking\Contracts\BankWebhookParserInterface;
use Src\Domain\Banking\DataTransferObjects\BankTransaction;

class BankWebhookHandlerServiceTest extends TestCase
{
    private BankWebhookParserFactory $parserFactory;
    private BankWebhookHandlerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parserFactory = Mockery::mock(BankWebhookParserFactory::class);
        $this->service = new BankWebhookHandlerService($this->parserFactory);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createTransaction(string $reference, BankType $bankType): BankTransaction
    {
        $bankTransaction = BankTransaction::create(
            date: '2023-01-01',
            amount: 156.50,
            currency: 'SAR',
            reference: $reference,
            bank: $bankType,
            meta: ['note' => 'Payment']
        );

        $bankTransaction->setClientId(1);

        return $bankTransaction;
    }

    public function test_it_handles_webhook_with_transactions(): void
    {
        $clientId = 1;
        $payload = BankWebhookPayload::create('foodics', 'some_content');
        $transactions = [
            $this->createTransaction('REF123', BankType::FOODICS),
            $this->createTransaction('REF456', BankType::FOODICS)
        ];

        $parser = Mockery::mock(BankWebhookParserInterface::class);
        $parser->shouldReceive('parse')
            ->with($payload)
            ->andReturn($transactions);

        $this->parserFactory->shouldReceive('fromBank')
            ->with('foodics')
            ->andReturn($parser);

        $this->service->handle($clientId, $payload);

        $this->assertEquals(1, $transactions[0]->getClientId());
        $this->assertEquals(1, $transactions[1]->getClientId());
    }


    public function test_it_handles_empty_transactions(): void
    {
        $clientId = 1;
        $payload = BankWebhookPayload::create('foodics', 'some_content');

        $parser = Mockery::mock(BankWebhookParserInterface::class);
        $parser->shouldReceive('parse')
            ->with($payload)
            ->andReturn([]);

        $this->parserFactory->shouldReceive('fromBank')
            ->with('foodics')
            ->andReturn($parser);


        $this->service->handle($clientId, $payload);
    }

    public function test_it_assigns_client_id_to_all_transactions(): void
    {
        $clientId = 1;
        $payload =  BankWebhookPayload::create('foodics', 'some_content');
        $transactions = [
            $this->createTransaction('REF123', BankType::FOODICS),
            $this->createTransaction('REF456', BankType::FOODICS)
        ];

        $parser = Mockery::mock(BankWebhookParserInterface::class);
        $parser->shouldReceive('parse')
            ->with($payload)
            ->andReturn($transactions);

        $this->parserFactory->shouldReceive('fromBank')
            ->with('foodics')
            ->andReturn($parser);

        $this->service->handle($clientId, $payload);

        foreach ($transactions as $transaction) {
            $this->assertEquals($clientId, $transaction->getClientId());
        }
    }
}
