<?php

namespace Database\Seeders;

use App\Models\WalletTransaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'client_id' => 1,
                'wallet_id' => 1,
                'amount' => 1500.00,
                'currency' => 'SAR',
                'type' => 'DEPOSIT',
                'status' => 'SUCCESSFUL',
                'meta' => json_encode(['source' => 'Webhook']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 1,
                'wallet_id' => 1,
                'amount' => 1500.00,
                'currency' => 'SAR',
                'type' => 'DEPOSIT',
                'status' => 'SUCCESSFUL',
                'meta' => json_encode(['source' => 'Webhook']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 1,
                'wallet_id' => 1,
                'amount' => 800.00,
                'currency' => 'SAR',
                'type' => 'WITHDRAW',
                'status' => 'SUCCESSFUL',
                'meta' => json_encode(['source' => 'Payout']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        WalletTransaction::query()->insert($data);
    }
}
