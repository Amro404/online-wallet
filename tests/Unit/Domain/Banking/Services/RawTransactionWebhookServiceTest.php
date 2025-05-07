<?php

namespace Tests\Unit\Domain\Banking\Services;

use Tests\TestCase;
use Mockery;
use Src\Domain\Banking\Services\RawTransactionWebhookService;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Src\Domain\Banking\DataTransferObjects\RawTransactionWebhook;
use Src\Domain\Banking\Repositories\RawTransactionWebhookRepositoryInterface;
use Illuminate\Support\Facades\Log;

class RawTransactionWebhookServiceTest extends TestCase
{
    private RawTransactionWebhookRepositoryInterface $repository;
    private RawTransactionWebhookService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(RawTransactionWebhookRepositoryInterface::class);
        $this->service = new RawTransactionWebhookService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    public function test_it_handles_webhook_successfully(): void
    {
        $clientId = 1;


        $payload = BankWebhookPayload::create('foodics', 'test content', 'CLIENT-123', ['header1' => 'value1']);

        $this->repository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (RawTransactionWebhook $rawWebhook) use ($clientId) {
                return $rawWebhook->getClientId() === $clientId &&
                    $rawWebhook->getContent() === 'test content' &&
                    $rawWebhook->getBankType()->value === 'foodics' &&
                    $rawWebhook->getHeaders() === ['header1' => 'value1'];
            }));

        $this->service->handle($clientId, $payload);
    }


    public function test_creates_raw_transaction_with_minimal_data(): void
    {
        $clientId = 1;
        $payload = BankWebhookPayload::create('foodics', 'test content');

        $this->repository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (RawTransactionWebhook $rawWebhook) {
                return $rawWebhook->getHeaders() === [];
            }));

        $this->service->handle($clientId, $payload);
    }
}
