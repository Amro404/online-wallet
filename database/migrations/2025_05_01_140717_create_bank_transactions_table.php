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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('client_id')->constrained();
            $table->string('reference');
            $table->decimal('amount', 11, 2);
            $table->char('currency', 3)->nullable();
            $table->string('bank_name');
            $table->json('meta')->nullable();
            $table->dateTime('date');
            $table->timestamps();

            $table->unique(['bank_name', 'reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
