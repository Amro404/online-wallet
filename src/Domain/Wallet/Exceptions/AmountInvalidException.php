<?php

namespace Src\Domain\Wallet\Exceptions;

class AmountInvalidException extends  WalletBaseException
{
    protected $message = 'The specified amount is invalid.';

    public function __construct(string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
