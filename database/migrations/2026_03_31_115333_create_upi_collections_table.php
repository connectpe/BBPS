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
        Schema::create('upi_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('cust_txn_id')->unique();
            $table->string('connectpe_order_id')->unique();
            $table->string('cust_name');
            $table->string('cust_email');
            $table->string('cust_mobile');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('fee', 255)->nullable();
            $table->string('tax', 255)->nullable();
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->string('qr_intent');
            $table->string('npci_txn_id')->unique();
            $table->string('txn_order_id')->unique();
            $table->string('txn_id')->unique();
            $table->string('type');
            $table->string('root');
            $table->string('res_code');
            $table->text('res_message');
            $table->text('response');
            $table->string('utr')->unique();
            $table->enum('status', ['initiated', 'success', 'failed'])->default('initiated');
            $table->enum('is_auto_settlement', ['1', '0'])->default('0');
            $table->enum('is_webhook_sent', ['1', '0'])->default('0');
            $table->timestamp('webhook_sent_at')->nullable();
            $table->enum('is_txn_credited', ['1', '0'])->default('0');
            $table->timestamp('txn_credited_at')->nullable();
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upi_collections');
    }
};
