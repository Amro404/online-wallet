<?php

namespace Src\Domain\Wallet\Exceptions;

class InsufficientFundsException extends WalletBaseException
{
    protected $message = 'Insufficient funds in the wallet.';

    public function __construct(string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
