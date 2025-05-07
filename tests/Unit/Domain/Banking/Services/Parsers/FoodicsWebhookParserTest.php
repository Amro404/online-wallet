<?php

namespace Tests\Unit\Domain\Banking\Services\Parsers;

use Tests\TestCase;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Src\Domain\Banking\Enums\BankType;
use Src\Domain\Banking\Services\Parsers\FoodicsWebhookParser;
use Illuminate\Support\Facades\Log;

class FoodicsWebhookParserTest extends TestCase
{
    private FoodicsWebhookParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new FoodicsWebhookParser();
    }

    private function createPayload(string $content): BankWebhookPayload
    {
        $headers = ['X-Merchant-Id' => 'CLIENT-12345'];
        return BankWebhookPayload::create(
            bank: 'foodics',
            content: $content,
            merchantId: $headers['X-Merchant-Id'],
            headers: $headers
        );
    }

    public function test_parsing_payload_content_transaction(): void
    {
        $content = "20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81";

        $payload = $this->createPayload($content);

        $result = $this->parser->parse($payload);

        $this->assertCount(1, $result);
        $transaction = $result[0];
        $this->assertEquals('2025-06-15', $transaction->getDate());
        $this->assertEquals(156.50, $transaction->getAmount());
        $this->assertEquals('20250615202506159000411', $transaction->getReference());;
        $this->assertEquals(BankType::FOODICS, $transaction->getBank());
        $this->assertEquals([
            'note' => 'debt payment march',
            'internal_reference' => 'A462JE81'
        ], $transaction->getMeta());
    }

    public function test_parsing_payload_content_multiple_transactions(): void
    {
        $content = <<<EOT
                    20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81
                    20250615156,50#20250615202506159000421#note/debt payment march/internal_reference/A462JE81
                    EOT;

        $payload = $this->createPayload($content);

        $result = $this->parser->parse($payload);
        $this->assertCount(2, $result);

        $this->assertEquals('20250615202506159000411', $result[0]->getReference());
        $this->assertEquals('20250615202506159000421', $result[1]->getReference());
    }

    public function test_matching_transaction_line_pattern(): void
    {
        $validLines = [
            "20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81",
            "20250615156,50#20250615202506159000421#note/debt payment march/internal_reference/A462JE81",
        ];

        $invalidLines = [
            "invalid_format",
            "20230506100,100.50#REF123",
            "20230506100#REF123#note/test/note/internal_reference/INT123",
            "20230506100,100.50REF123#note/test/note/internal_reference/INT123",
        ];

        foreach ($validLines as $line) {
            $this->assertMatchesRegularExpression(
                FoodicsWebhookParser::TRX_LINE_PATTERN,
                $line,
                "Line '$line' should match pattern but doesn't"
            );
        }

        foreach ($invalidLines as $line) {
            $this->assertDoesNotMatchRegularExpression(
                FoodicsWebhookParser::TRX_LINE_PATTERN,
                $line,
                "Line '$line' should not match pattern but does"
            );
        }
    }

    public function test_skipping_invalid_transactions(): void
    {
        Log::spy();

        $content = <<<EOT
                    20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81
                    20230506100,100.50REF123#note/test/note/internal_reference/INT123
                    EOT;

        $payload = $this->createPayload($content);

        $result = $this->parser->parse($payload);

        Log::shouldHaveReceived('warning')
            ->once()
            ->with("Skipping invalid line in webhook payload", ['line' => '20230506100,100.50REF123#note/test/note/internal_reference/INT123']);

        $this->assertCount(1, $result);
        $this->assertEquals('20250615202506159000411', $result[0]->getReference());
    }

    public function test_returning_only_unique_transactions_by_reference(): void
    {
        $content = <<<EOT
                    20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81
                    20250615156,50#20250615202506159000421#note/debt payment march/internal_reference/A462JE81
                    20250615156,50#20250615202506159000431#note/debt payment march/internal_reference/A462JE81
                    20250615156,50#20250615202506159000431#note/debt payment march/internal_reference/A462JE81
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
        $payload = $this->createPayload('20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81');
        $result = $this->parser->parse($payload)[0];
        $this->assertEquals('2025-06-15', $result->getDate());
    }


    public function test_parse_amount_correctly(): void
    {
        $payload = $this->createPayload('20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81');
        $result = $this->parser->parse($payload)[0];
        $this->assertEquals(156.50, $result->getAmount());
    }

    public function test_parse_key_value_pairs_correctly(): void
    {
        $payload = $this->createPayload('20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81');
        $result = $this->parser->parse($payload)[0];

        $this->assertEquals([
            'note' => 'debt payment march',
            'internal_reference' => 'A462JE81'
        ], $result->getMeta());
    }
}
