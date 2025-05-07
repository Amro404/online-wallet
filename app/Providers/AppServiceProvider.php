<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Domain\Banking\Repositories\BankTransactionRepositoryInterface;
use Src\Domain\Banking\Repositories\RawTransactionWebhookRepositoryInterface;
use Src\Domain\Banking\Repositories\WebhookIngestionSettingRepositoryInterface;
use Src\Domain\Client\Repositories\ClientRepositoryInterface;
use Src\Domain\Payment\Contracts\BankAdapterInterface;
use Src\Domain\Payment\Repositories\PaymentRepositoryInterface;
use Src\Domain\Payment\Services\BankXAdapter;
use Src\Domain\Wallet\Repositories\WalletRepositoryInterface;
use Src\Domain\Wallet\Repositories\WalletTransactionRepositoryInterface;
use Src\Infrastructure\Repositories\EloquentBankTransactionRepository;
use Src\Infrastructure\Repositories\EloquentClientRepository;
use Src\Infrastructure\Repositories\EloquentPaymentRepository;
use Src\Infrastructure\Repositories\EloquentRawTransactionWebhookRepository;
use Src\Infrastructure\Repositories\EloquentWalletRepository;
use Src\Infrastructure\Repositories\EloquentWalletTransactionRepository;
use Src\Infrastructure\Repositories\EloquentWebhookIngestionSettingRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            BankTransactionRepositoryInterface::class,
            EloquentBankTransactionRepository::class
        );

        $this->app->bind(
            RawTransactionWebhookRepositoryInterface::class,
            EloquentRawTransactionWebhookRepository::class
        );

        $this->app->bind(
            WebhookIngestionSettingRepositoryInterface::class,
            EloquentWebhookIngestionSettingRepository::class
        );

        $this->app->bind(
            WalletRepositoryInterface::class,
            EloquentWalletRepository::class
        );

        $this->app->bind(
            WalletTransactionRepositoryInterface::class,
            EloquentWalletTransactionRepository::class
        );

        $this->app->bind(
            ClientRepositoryInterface::class,
            EloquentClientRepository::class
        );

        $this->app->bind(
            PaymentRepositoryInterface::class,
            EloquentPaymentRepository::class
        );

        $this->app->bind(
            BankAdapterInterface::class,
            BankXAdapter::class
        );
        $this->app->bind(
            PaymentRepositoryInterface::class,
            EloquentPaymentRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
