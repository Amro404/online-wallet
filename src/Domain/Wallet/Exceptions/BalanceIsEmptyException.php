<?php

namespace Src\Domain\Wallet\Exceptions;

class BalanceIsEmptyException extends WalletBaseException
{
    protected $message = 'The wallet balance is empty.';

    public function __construct(string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
