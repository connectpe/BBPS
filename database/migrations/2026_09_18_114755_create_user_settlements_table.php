<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('settlement_ref_id', 100)->unique();
            $table->string('mode', 50);
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('net_amount', 10, 2);
            $table->string('account_number', 50);
            $table->string('account_ifsc', 100);
            $table->string('beneficiary_name', 100);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->boolean('is_balance_debited')->default(false);
            $table->string('from_wallet', 100);
            $table->string('to_wallet', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settlements');
    }
};







