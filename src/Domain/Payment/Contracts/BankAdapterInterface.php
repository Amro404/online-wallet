<?php

namespace Src\Domain\Payment\Contracts;

use Src\Domain\Payment\DataTransferObjects\PaymentRequest;
use Src\Domain\Payment\DataTransferObjects\PaymentResponse;

interface BankAdapterInterface
{
    public function send(PaymentRequest $paymentRequest): PaymentResponse;
}
