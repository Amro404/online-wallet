<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'client_id' => 1,
                'reference' => Str::uuid(),
                'amount' => 2500.00,
                'currency' => 'SAR',
                'sender_account_number' => 'SA1122334455667788990011',
                'receiver_bank_code' => 'BANKSA001',
                'receiver_account_number' => 'SA9988776655443322110011',
                'receiver_beneficiary_name' => 'Acme Vendor',
                'payment_type' => 99,
                'charge_details' => 'SHA',
                'status' => 'COMPLETED',
                'failure_reason' => null,
                'notes' => json_encode(['purpose' => 'Invoice #123']),
                'transfer_date' => now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 1,
                'reference' => Str::uuid(),
                'amount' => 500.00,
                'currency' => 'SAR',
                'sender_account_number' => 'SA1122334455667788990011',
                'receiver_bank_code' => 'BANKSA001',
                'receiver_account_number' => 'SA9988776655443322110011',
                'receiver_beneficiary_name' => 'Foodics Vendor',
                'payment_type' => 421,
                'charge_details' => 'SHA',
                'status' => 'COMPLETED',
                'failure_reason' => null,
                'notes' => json_encode(['purpose' => 'Invoice #123']),
                'transfer_date' => now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        Payment::query()->insert($data);
    }
}
