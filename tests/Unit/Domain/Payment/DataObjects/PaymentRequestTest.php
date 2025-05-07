<?php

namespace Tests\Unit\Domain\Payment\DataObjects;

use PHPUnit\Framework\TestCase;
use Src\Domain\Payment\DataTransferObjects\PaymentRequest;
use Src\Domain\Payment\Entities\ReceiverInfo;
use Src\Domain\Payment\Entities\SenderInfo;
use Src\Domain\Payment\ValueObjects\Amount;
use Src\Domain\Payment\ValueObjects\ChargeDetails;
use Src\Domain\Payment\ValueObjects\PaymentType;
use Src\Domain\Payment\ValueObjects\Reference;

class PaymentRequestTest extends TestCase
{

    private function createReference(): Reference
    {
        return Reference::create();
    }

    private function createAmount(): Amount
    {
        return Amount::create(100.50, 'USD');
    }

    private function createSenderInfo(): SenderInfo
    {
        return SenderInfo::create('SA6980000204608016212908');
    }

    private function createReceiverInfo(): ReceiverInfo
    {
        return ReceiverInfo::create(
            'FDCSSARI',
            'SA6980000204608016211111',
            'Jane Doe'
        );
    }

    private function createPaymentType(): PaymentType
    {
        return PaymentType::create(421);
    }

    private function createChargeDetails(): ChargeDetails
    {
        return ChargeDetails::create('SHA');
    }

    private function createPaymentRequest(Reference $paymentReference = null, array $notes = []): PaymentRequest
    {
        return PaymentRequest::create(
            reference: $paymentReference ?? $this->createReference(),
            amount: $this->createAmount(),
            senderInfo: $this->createSenderInfo(),
            receiverInfo: $this->createReceiverInfo(),
            paymentType: $this->createPaymentType(),
            chargeDetails: $this->createChargeDetails(),
            clientId: 1,
            date: '2023-01-01',
            notes: $notes
        );
    }

    public function test_it_creates_with_all_properties(): void
    {
        $paymentReference = $this->createReference();

        $paymentRequest = $this->createPaymentRequest($paymentReference, ['note1']);

        $this->assertEquals($paymentReference->value(), $paymentRequest->reference()->value());
        $this->assertEquals(100.50, $paymentRequest->amount()->value());
        $this->assertEquals('USD', $paymentRequest->amount()->currency());
        $this->assertEquals('SA6980000204608016212908', $paymentRequest->senderInfo()->getAccountNumber());
        $this->assertEquals('Jane Doe', $paymentRequest->receiverInfo()->getBeneficiaryName());
        $this->assertEquals(421, $paymentRequest->paymentType()->value());
        $this->assertEquals('SHA', $paymentRequest->chargeDetails()->value());
        $this->assertEquals(1, $paymentRequest->clientId());
        $this->assertEquals('2023-01-01', $paymentRequest->date());
        $this->assertEquals(['note1'], $paymentRequest->notes());
        $this->assertTrue($paymentRequest->hasNotes());
    }

    public function test_it_creates_with_empty_notes(): void
    {
        $paymentRequest = $this->createPaymentRequest();

        $this->assertEmpty($paymentRequest->notes());
        $this->assertFalse($paymentRequest->hasNotes());
    }

    public function test_add_note_functionality(): void
    {
        $paymentRequest = $this->createPaymentRequest();

        $paymentRequest->addNote('new note');
        $paymentRequest->addNote('another note');

        $this->assertEquals(['new note', 'another note'], $paymentRequest->notes());
        $this->assertTrue($paymentRequest->hasNotes());
    }

    public function test_static_create_method(): void
    {
        $reference = $this->createReference();
        $amount = $this->createAmount();
        $senderInfo = $this->createSenderInfo();
        $receiverInfo = $this->createReceiverInfo();
        $paymentType = $this->createPaymentType();
        $chargeDetails = $this->createChargeDetails();

        $paymentRequest = PaymentRequest::create(
            reference: $reference,
            amount: $amount,
            senderInfo: $senderInfo,
            receiverInfo: $receiverInfo,
            paymentType: $paymentType,
            chargeDetails: $chargeDetails,
            clientId: 1,
            date: '2023-01-01',
            notes: ['test']
        );

        $this->assertSame($reference, $paymentRequest->reference());
        $this->assertSame($amount, $paymentRequest->amount());
        $this->assertSame($senderInfo, $paymentRequest->senderInfo());
        $this->assertSame($receiverInfo, $paymentRequest->receiverInfo());
        $this->assertSame($paymentType, $paymentRequest->paymentType());
        $this->assertSame($chargeDetails, $paymentRequest->chargeDetails());
        $this->assertEquals(1, $paymentRequest->clientId());
        $this->assertEquals('2023-01-01', $paymentRequest->date());
        $this->assertEquals(['test'], $paymentRequest->notes());
    }

    public function test_property_accessors(): void
    {
        $paymentRequest = $this->createPaymentRequest();

        $this->assertInstanceOf(Reference::class, $paymentRequest->reference());
        $this->assertInstanceOf(Amount::class, $paymentRequest->amount());
        $this->assertInstanceOf(SenderInfo::class, $paymentRequest->senderInfo());
        $this->assertInstanceOf(ReceiverInfo::class, $paymentRequest->receiverInfo());
        $this->assertInstanceOf(PaymentType::class, $paymentRequest->paymentType());
        $this->assertInstanceOf(ChargeDetails::class, $paymentRequest->chargeDetails());
        $this->assertIsInt($paymentRequest->clientId());
        $this->assertIsString($paymentRequest->date());
        $this->assertIsArray($paymentRequest->notes());
    }
}
