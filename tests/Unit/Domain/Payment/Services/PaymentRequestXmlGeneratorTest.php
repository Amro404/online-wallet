<?php

namespace Tests\Unit\Domain\Payment\Services;

use Tests\TestCase;
use Src\Domain\Payment\Services\PaymentRequestXmlGenerator;
use Src\Domain\Payment\DataTransferObjects\PaymentRequest;
use Src\Domain\Payment\Entities\ReceiverInfo;
use Src\Domain\Payment\Entities\SenderInfo;
use Src\Domain\Payment\ValueObjects\Amount;
use Src\Domain\Payment\ValueObjects\ChargeDetails;
use Src\Domain\Payment\ValueObjects\PaymentType;
use Src\Domain\Payment\ValueObjects\Reference;
use DOMDocument;

class PaymentRequestXmlGeneratorTest extends TestCase
{
    private PaymentRequestXmlGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new PaymentRequestXmlGenerator();
    }

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
        return PaymentType::create(99);
    }

    private function createChargeDetails(): ChargeDetails
    {
        return ChargeDetails::create('RB');
    }

    private function createBasicPaymentRequest(): PaymentRequest
    {
        return PaymentRequest::create(
            reference: $this->createReference(),
            amount: $this->createAmount(),
            senderInfo: $this->createSenderInfo(),
            receiverInfo: $this->createReceiverInfo(),
            paymentType: PaymentType::create(99),
            chargeDetails: ChargeDetails::create('SHA'),
            clientId: 1,
            date: '2023-01-01',
            notes: []
        );
    }

    public function test_generates_basic_xml_structure(): void
    {
        $request = $this->createBasicPaymentRequest();

        $xml = $this->generator->generate($request);
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $this->assertEquals('PaymentRequestMessage', $dom->documentElement->nodeName);
        $this->assertNotNull($dom->getElementsByTagName('TransferInfo')->item(0));
        $this->assertNotNull($dom->getElementsByTagName('SenderInfo')->item(0));
        $this->assertNotNull($dom->getElementsByTagName('ReceiverInfo')->item(0));
    }

    public function test_includes_all_basic_required_fields(): void
    {
        $request = $this->createBasicPaymentRequest();

        $xml = $this->generator->generate($request);
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $this->assertXmlStringEqualsXmlString(
            <<<XML
            <PaymentRequestMessage>
                <TransferInfo>
                    <Reference>{$request->reference()->value()}</Reference>
                    <Date>{$request->date()}</Date>
                    <Amount>{$request->amount()->value()}</Amount>
                    <Currency>{$request->amount()->currency()}</Currency>
                </TransferInfo>
                <SenderInfo>
                    <AccountNumber>{$request->senderInfo()->getAccountNumber()}</AccountNumber>
                </SenderInfo>
                <ReceiverInfo>
                    <BankCode>{$request->receiverInfo()->getBankCode()}</BankCode>
                    <AccountNumber>{$request->receiverInfo()->getAccountNumber()}</AccountNumber>
                    <BeneficiaryName>{$request->receiverInfo()->getBeneficiaryName()}</BeneficiaryName>
                </ReceiverInfo>
            </PaymentRequestMessage>
            XML,
            $xml
        );
    }

    public function test_includes_notes_when_present(): void
    {
        $request = $this->createPaymentRequestWithNotes(['Note 1', 'Note 2']);;

        $xml = $this->generator->generate($request);
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $notes = $dom->getElementsByTagName('Notes');
        $this->assertEquals(1, $notes->length);
        $noteElements = $notes->item(0)->getElementsByTagName('Note');
        $this->assertEquals(2, $noteElements->length);
        $this->assertEquals('Note 1', $noteElements->item(0)->nodeValue);
        $this->assertEquals('Note 2', $noteElements->item(1)->nodeValue);
    }

    public function test_includes_payment_type_when_value_other_than_default(): void
    {
        // the default value is 99
        $request = $this->createPaymentRequestWithPaymentType(421);

        $xml = $this->generator->generate($request);
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $paymentType = $dom->getElementsByTagName('PaymentType');
        $this->assertEquals(1, $paymentType->length);
    }

    public function test_includes_charge_details_when_value_other_than_default(): void
    {
        // the default value is 'SHA'
        $request = $this->createPaymentRequestWithChargeDetails('RB');

        $xml = $this->generator->generate($request);
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $chargeDetails = $dom->getElementsByTagName('ChargeDetails');
        $this->assertEquals(1, $chargeDetails->length);
    }

    public function test_excludes_optional_fields_when_not_required(): void
    {
        $request = $this->createBasicPaymentRequest();

        $xml = $this->generator->generate($request);
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $this->assertEquals(0, $dom->getElementsByTagName('Notes')->length);
        $this->assertEquals(0, $dom->getElementsByTagName('PaymentType')->length);
        $this->assertEquals(0, $dom->getElementsByTagName('ChargeDetails')->length);
    }

    public function test_generates_valid_xml(): void
    {
        $request = $this->createBasicPaymentRequest();

        $xml = $this->generator->generate($request);
        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($xml));
    }

    private function createPaymentRequestWithNotes(array $notes): PaymentRequest
    {
        return PaymentRequest::create(
            reference: $this->createReference(),
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

    private function createPaymentRequestWithPaymentType(int $paymentType): PaymentRequest
    {
        return PaymentRequest::create(
            reference: $this->createReference(),
            amount: $this->createAmount(),
            senderInfo: $this->createSenderInfo(),
            receiverInfo: $this->createReceiverInfo(),
            paymentType: PaymentType::create($paymentType),
            chargeDetails: $this->createChargeDetails(),
            clientId: 1,
            date: '2023-01-01',
            notes: ['Lorem Epsum', 'Dolor Sit Amet']
        );
    }

    private function createPaymentRequestWithChargeDetails(string $chargeDetails): PaymentRequest
    {
        return PaymentRequest::create(
            reference: $this->createReference(),
            amount: $this->createAmount(),
            senderInfo: $this->createSenderInfo(),
            receiverInfo: $this->createReceiverInfo(),
            paymentType: $this->createPaymentType(),
            chargeDetails: ChargeDetails::create($chargeDetails),
            clientId: 1,
            date: '2023-01-01',
            notes: ['Lorem Epsum', 'Dolor Sit Amet']
        );
    }
}
