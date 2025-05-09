<?php

namespace Src\Domain\Payment\Services;

use Illuminate\Support\Facades\DB;
use Src\Domain\Client\Services\ClientService;
use Src\Domain\Payment\Contracts\BankAdapterInterface;
use Src\Domain\Payment\DataTransferObjects\PaymentResponse;
use Src\Domain\Payment\Enums\PaymentStatus;
use Src\Domain\Payment\Events\PaymentRequestCreated;
use Src\Domain\Payment\Factories\PaymentRequestFactory;
use Src\Domain\Payment\Repositories\PaymentRepositoryInterface;
use Src\Domain\Wallet\Enums\WalletTransactionStatus;
use Src\Domain\Wallet\Exceptions\InsufficientFundsException;
use Src\Domain\Wallet\Services\WalletService;


class PaymentService
{
    public function __construct(
        protected BankAdapterInterface $bankAdapter,
        protected WalletService $walletService,
        protected ClientService $clientService,
        protected PaymentRepositoryInterface $paymentRepository
    ) {}

    public function sendPaymentRequest(array $paymentData): PaymentResponse
    {
        $client = $this->clientService->getById($paymentData['client_id']);

        $canWithdraw = $this->walletService
            ->canWithdraw($paymentData['client'], $paymentData['amount']);

        if ( ! $canWithdraw) {
            throw new InsufficientFundsException();
        }

        $paymentRequest = PaymentRequestFactory::fromRequest($paymentData);
        $payment = $this->paymentRepository->create($paymentRequest);

        try {
            DB::beginTransaction();

            $response = $this->bankAdapter->send($paymentRequest);

            $this->paymentRepository->updateFromResponse(
                $paymentRequest->reference()->value(),
                $response
            );

            $this->walletService->withdraw(
                holder: $client,
                amount: $paymentRequest->amount()->value(),
                meta: [
                    'source_id' => $payment->id,
                    'source_type' => 'payment_request'
                ],
                // pending until I receive a webhook from the provider to confirm the transaction
                status: WalletTransactionStatus::PENDING
            );

            event(new PaymentRequestCreated($payment));

            DB::commit();

            return $response;

        } catch (\Exception $exception) {
            DB::rollBack();
            $this->paymentRepository->updateStatus(
                $paymentRequest->reference()->value(),
                PaymentStatus::FAILED
            );
            throw $exception;
        }
    }
}
