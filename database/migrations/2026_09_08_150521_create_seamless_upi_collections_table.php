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
        Schema::create('seamless_upi_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('cust_txn_id')->unique();
            $table->string('connectpe_order_id')->index();
            $table->string('cust_name');
            $table->string('cust_email');
            $table->string('cust_mobile', 15);
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->decimal('net_amount', 10, 2);
            $table->string('upi_intent');
            $table->string('txn_order_id')->index();
            $table->string('route')->nullable();
            $table->string('response_code')->nullable();
            $table->string('response_message')->nullable();
            $table->longText('response')->nullable();
            $table->string('utr')->nullable()->index();
            $table->boolean('is_auto_settlement')->default(false);
            $table->boolean('is_webhook_send')->default(false);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->dateTime('webhook_send_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seamless_upi_collections');
    }
};
