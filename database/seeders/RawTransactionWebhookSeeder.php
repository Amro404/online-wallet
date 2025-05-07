<?php

namespace Database\Seeders;

use App\Models\RawTransactionWebhook;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RawTransactionWebhookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $foodicsBankPayload = <<<EOT
                    20250615156,50#20250615202506159000411#note/debt payment march/internal_reference/A462JE81
                    20250615156,50#20250615202506159000421#note/debt payment march/internal_reference/A462JE81
                    20250615156,50#20250615202506159000431#note/debt payment march/internal_reference/A462JE81
                    20250615156,50#20250615202506159000441#note/debt payment march/internal_reference/A462JE81
                    20250615156,50#20250615202506159000451#note/debt payment march/internal_reference/A462JE81
                    EOT;

        $acmeBankPayload = <<<EOT
                    2000,50//202506159000021//20250615
                    2000,50//202506159000031//20250615
                    2000,50//202506159000041//20250615
                    2000,50//202506159000051//20250615
                    2000,50//202506159000061//20250615
                    EOT;
        $data = [
            [
                'client_id' => 1,
                'payload' => $foodicsBankPayload,
                'headers' => json_encode(['X-Merchant-Id' => 'CLIENT-12345']),
                'bank_name' => 'foodics',
                'status' => 'PENDING',
                'received_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 1,
                'payload' => $acmeBankPayload,
                'headers' => json_encode(['X-Merchant-Id' => 'CLIENT-12345']),
                'bank_name' => 'acme',
                'status' => 'PENDING',
                'received_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        RawTransactionWebhook::query()->insert($data);
    }
}
