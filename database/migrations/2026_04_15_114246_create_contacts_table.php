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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('contact_id', 255)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone', 15);

            $table->enum('type', ['vendor', 'customer', 'employee', 'self']);
            $table->enum('account_type', ['bank_account', 'vpa', 'card']);

            $table->string('account_number')->nullable();
            $table->string('account_ifsc')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('vpa_address')->nullable();
            $table->string('card_number')->nullable();

            $table->string('reference_id')->unique();

            $table->boolean('is_active')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
