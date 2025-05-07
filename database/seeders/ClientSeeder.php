<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::query()->firstOrCreate([
            'id' => 1,
        ], [
            'provider_merchant_id' => 'CLIENT-12345',
            'name' => 'Amr Saeed',
            'bank_account_number' => 'SA6980000204608016212908',
        ]);
    }
}
