<?php

namespace Src\Domain\Wallet\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Src\Domain\Wallet\Contracts\WalletHolderInterface;

class LockService
{
    private const LOCK_KEY = 'wallet_lock::';
    private const MAX_RETRIES = 3;
    private const BASE_DELAY = 100;

    public function __construct(
        protected WalletDomainService $walletDomainService,
    ) {}

    /**
     * @throws LockTimeoutException
     */
    public function block(WalletHolderInterface $client, callable $callback): mixed
    {
        $wallet = $this->walletDomainService->getWallet($client);

        $lock = Cache::lock(self::LOCK_KEY . $wallet->id, 10);

        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {

            if ($lock->block(5)) {

                try {
                    $result = DB::transaction($callback);

                    $lock->release();

                    return $result;

                } catch (LockTimeoutException $e) {
                    $lock->release();
                    throw  $e;
                } finally {
                    $lock->release();
                }
            }

            $delay = $this->getBackoffDelay($attempt);
            usleep($delay * 1000);
        }

        throw new \Exception("Unable to acquire lock after multiple retries.");
    }

    private function getBackoffDelay($attempt): float|int
    {
        $delay = self::BASE_DELAY * pow(2, $attempt);
        $jitter = rand(0, 100);
        return $delay + $jitter;
    }


}
