<?php

namespace Tests\Unit\Domain\Banking\Services\Parsers;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Src\Domain\Banking\Enums\BankType;
use Src\Domain\Banking\Services\Parsers\AcmeWebhookParser;

class AcmeWebhookParserTest extends TestCase
{

    private AcmeWebhookParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new AcmeWebhookParser();
    }

    private function createPayload(string $content): BankWebhookPayload
    {
        $headers = ['X-Merchant-Id' => 'CLIENT-12345'];
        return BankWebhookPayload::create(
            bank: 'acme',
            content: $content,
            merchantId: $headers['X-Merchant-Id'],
            headers: $headers
        );
    }

    public function test_parsing_payload_content_transaction(): void
    {

        $content = '2000,50//202506159000001//20250615';

        $payload = $this->createPayload($content);

        $result = $this->parser->parse($payload);

        $this->assertCount(1, $result);
        $transaction = $result[0];
        $this->assertEquals('2025-06-15', $transaction->getDate());
        $this->assertEquals(2000.50, $transaction->getAmount());
        $this->assertEquals('202506159000001', $transaction->getReference());;
        $this->assertEquals(BankType::ACME, $transaction->getBank());
        $this->assertEquals([], $transaction->getMeta());
    }

    public function test_parsing_payload_content_multiple_transactions(): void
    {
        $content = <<<EOT
                    2000,50//202506159000001//20250615
                    4200,50//202506159000021//20250615
                    EOT;

        $payload = $this->createPayload($content);

        $result = $this->parser->parse($payload);
        $this->assertCount(2, $result);

        $this->assertEquals('202506159000001', $result[0]->getReference());
        $this->assertEquals('202506159000021', $result[1]->getReference());
    }

    public function test_matching_transaction_line_pattern(): void
    {
        $validLines = [
            "4200,50//202506159000021//20250615",
            "2000,50//202506159000001//20250615",
        ];

        $invalidLines = [
            "invalid_format",
            "20230506100,100.50#REF123",
            "20230506100#REF123#note//test/note//internal_reference/INT123",
        ];

        foreach ($validLines as $line) {
            $this->assertMatchesRegularExpression(
                AcmeWebhookParser::TRX_LINE_PATTERN,
                $line,
                "Line '$line' should match pattern but doesn't"
            );
        }

        foreach ($invalidLines as $line) {
            $this->assertDoesNotMatchRegularExpression(
                AcmeWebhookParser::TRX_LINE_PATTERN,
                $line,
                "Line '$line' should not match pattern but does"
            );
        }
    }

    public function test_skipping_invalid_transactions(): void
    {
        Log::spy();

        $content = <<<EOT
                    2420,50//202506159000021//20250615
                    20230506100,100.50REF123#note/test/note/internal_reference/INT123
                    EOT;


        $payload = $this->createPayload($content);

        $result = $this->parser->parse($payload);

        Log::shouldHaveReceived('warning')
            ->once()
            ->with("Skipping invalid line in webhook payload", ['line' => '20230506100,100.50REF123#note/test/note/internal_reference/INT123']);

        $this->assertCount(1, $result);
        $this->assertEquals('202506159000021', $result[0]->getReference());
    }

    public function test_returning_only_unique_transactions_by_reference(): void
    {
        $content = <<<EOT
                    2000,50//202506159000001//20250615
                    4200,50//202506159000021//20250615
                    4200,50//202506159000031//20250615
                    4200,50//202506159000021//20250615
                    EOT;

        $payload = $this->createPayload($content);

        $result = $this->parser->parse($payload);

        $this->assertCount(3, $result);

    }

    public function test_empty_content_returns_empty_array(): void
    {
        $payload = $this->createPayload('');

        $result = $this->parser->parse($payload);
        $this->assertEmpty($result);
    }

    public function test_parse_date_correctly(): void
    {
        $payload = $this->createPayload('4200,50//202506159000021//20250615');
        $result = $this->parser->parse($payload)[0];
        $this->assertEquals('2025-06-15', $result->getDate());
    }


    public function test_parse_amount_correctly(): void
    {
        $payload = $this->createPayload('156,50//202506159000021//20250615');
        $result = $this->parser->parse($payload)[0];
        $this->assertEquals(156.50, $result->getAmount());
    }

}
