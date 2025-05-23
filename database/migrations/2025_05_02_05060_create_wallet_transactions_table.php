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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('wallet_id')->constrained();
            $table->decimal('amount', 11, 2)->default(0.00);
            $table->char('currency', 3)->nullable();
            $table->enum('type', ['DEPOSIT', 'WITHDRAW']);
            $table->enum('status', ['PENDING', 'SUCCESSFUL', 'FAILED'])->default('SUCCESSFUL');
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
