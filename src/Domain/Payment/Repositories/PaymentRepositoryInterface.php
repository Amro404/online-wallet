<?php

namespace Src\Domain\Payment\Repositories;

use App\Models\Payment;
use Src\Domain\Payment\DataTransferObjects\PaymentRequest;
use Src\Domain\Payment\DataTransferObjects\PaymentResponse;
use Src\Domain\Payment\Enums\PaymentStatus;

interface PaymentRepositoryInterface
{
    public function create(PaymentRequest $paymentRequest): Payment;
    public function updateFromResponse(string $reference, PaymentResponse $paymentResponse): void;
    public function updateStatus(string $reference, PaymentStatus $status): void;
}
