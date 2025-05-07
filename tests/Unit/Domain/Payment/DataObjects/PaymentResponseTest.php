<?php

namespace Tests\Unit\Domain\Payment\DataObjects;

use Tests\TestCase;
use Src\Domain\Payment\DataTransferObjects\PaymentResponse;
use Src\Domain\Payment\Enums\PaymentStatus;

class PaymentResponseTest extends TestCase
{
    public function test_it_creates_with_all_properties(): void
    {
        $clientReference = 'e0f4763d-28ea-42d4-ac1c-c4013c242105';
        $status = PaymentStatus::COMPLETED;
        $bankReference = 'BANK-REF-2025-987654';
        $message = 'Payment processed successfully';

        $response = PaymentResponse::create(
            $clientReference,
            $status,
            $bankReference,
            $message
        );

        $this->assertEquals($clientReference, $response->getReference());
        $this->assertSame($status, $response->getStatus());
        $this->assertEquals($bankReference, $response->getBankReference());
        $this->assertEquals($message, $response->getMessage());
    }

    public function test_it_creates_with_nullable_fields(): void
    {
        $clientReference = 'e0f4763d-28ea-42d4-ac1c-c4013c242105';
        $status = PaymentStatus::PENDING;

        $response = PaymentResponse::create(
            $clientReference,
            $status,
            null,
            null
        );

        $this->assertEquals($clientReference, $response->getReference());
        $this->assertSame($status, $response->getStatus());
        $this->assertNull($response->getBankReference());
        $this->assertNull($response->getMessage());
    }

    public function test_getters_return_correct_types(): void
    {
        $response = PaymentResponse::create(
            'e0f4763d-28ea-42d4-ac1c-c4013c242105',
            PaymentStatus::FAILED,
            'BANK-REF-2025-987654',
            'Payment failed'
        );

        $this->assertIsString($response->getReference());
        $this->assertInstanceOf(PaymentStatus::class, $response->getStatus());
        $this->assertIsString($response->getBankReference());
        $this->assertIsString($response->getMessage());
    }

    public function test_nullable_getters_return_correct_types_when_null(): void
    {
        $response = PaymentResponse::create(
            'e0f4763d-28ea-42d4-ac1c-c4013c242105',
            PaymentStatus::PENDING,
            null,
            null
        );

        $this->assertNull($response->getBankReference());
        $this->assertNull($response->getMessage());
    }

    public function test_it_handles_all_payment_statuses(): void
    {
        $reference = 'BANK-REF-2025-987654';

        foreach (PaymentStatus::cases() as $status) {
            $response = PaymentResponse::create(
                $reference,
                $status,
                'BANK-REF-2025-987654F'
            );

            $this->assertSame($status, $response->getStatus());
        }
    }

}
