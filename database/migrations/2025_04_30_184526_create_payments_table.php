<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->uuid('reference')->nullable()->unique();
            $table->decimal('amount', 11, 2);
            $table->char('currency', 3)->nullable();
            $table->string('sender_account_number', 34);
            $table->string('receiver_bank_code', 11);
            $table->string('receiver_account_number', 34);
            $table->string('receiver_beneficiary_name', 100);
            $table->unsignedSmallInteger('payment_type')->default(99);
            $table->string('charge_details', 3)->default('SHA');
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED'])->default('PENDING');
            $table->text('failure_reason')->nullable();
            $table->json('notes');
            $table->dateTime('transfer_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
