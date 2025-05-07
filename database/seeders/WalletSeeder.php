<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Wallet::query()->firstOrCreate(
            [
                'id' => 1,
                'client_id' => 1,
            ],
            [
                'description' => 'Primary Wallet',
                'balance' => 10000.00,
                'currency' => 'SAR',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
