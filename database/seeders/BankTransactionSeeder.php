<?php

namespace Database\Seeders;

use App\Models\BankTransaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BankTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => Str::uuid(),
                'client_id' => 1,
                'reference' => '20250615202506159000471',
                'amount' => 156.50,
                'currency' => 'SAR',
                'bank_name' => 'foodics',
                'meta' => json_encode(['note' => 'debt payment march', 'internal_reference' => 'A462JE81']),
                'date' => now()->subDays(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'client_id' => 1,
                'reference' => '202506159000061',
                'amount' => 156.50,
                'currency' => 'SAR',
                'bank_name' => 'acme',
                'meta' => json_encode([]),
                'date' => now()->subDays(1),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        BankTransaction::query()->insert($data);
    }
}
