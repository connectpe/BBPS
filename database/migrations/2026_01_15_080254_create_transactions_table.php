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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('operator_id')->constrained('operators')->onDelete('cascade');
            $table->foreignId('circle_id')->constrained('circles')->onDelete('cascade');
            $table->decimal('amount', 15, 2);

            $table->string('transaction_type');
            $table->enum('status', [
                'pending',
                'processing',
                'processed',
                'failed',
                'reversed'
            ])->default('pending');
            $table->string('reference_number')->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
