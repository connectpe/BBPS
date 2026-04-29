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
        Schema::create('recharge_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('global_services')->cascadeOnDelete();
            $table->string('connectpe_id')->unique();
            $table->string('request_id');
            $table->string('payment_ref_id')->unique();
            $table->string('transaction_id')->unique()->nullable();
            $table->string('utr')->unique()->nullable();
            $table->string('connection_no');
            $table->string('operator_id');
            $table->string('circle_id');
            $table->string('plan_type');
            $table->string('customer_mobile');
            $table->string('agent_id');
            $table->string('remitter_name');
            $table->enum('payment_mode', ['cash', 'creditcard', 'debitcard', 'internetbanking', 'upi', 'wallet'])->default('wallet');
            $table->string('payment_account_info');
            $table->string('recharge_type');
            $table->decimal('amount', 13, 2)->default(0.00)->nullable();
            $table->decimal('fee', 13, 2)->default(0.00)->nullable();
            $table->decimal('tax', 13, 2)->default(0.00)->nullable();
            $table->decimal('net_amount', 13, 2)->default(0.00)->nullable();
            $table->text('success_response')->nullable();
            $table->text('failed_response')->nullable();
            $table->string('failed_message')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('transaction_reversed');
            $table->timestamp('transaction_reversed_at')->nullable();
            $table->text('narration')->nullable();
            $table->text('remark')->nullable();
            $table->string('ip');
            $table->string('user_agent');
            $table->enum('is_api_call', ['0', '1'])->default('0');
            $table->enum('is_cron', ['0', '1'])->default('0');
            $table->timestamp('cron_date')->nullable();
            $table->enum('status', ['queue', 'pending', 'success', 'failed', 'reversed'])->default('queue');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recharge_orders');
    }
};
