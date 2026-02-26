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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('global_services')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade');


            $table->string('connectpe_id', 255);
            $table->string('transaction_no', 255);
            $table->string('client_txn_id', 255)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('utr_no', 255)->nullable();
            $table->string('fee', 255)->nullable();
            $table->string('tax', 255)->nullable();
            $table->decimal('total_amount', 15, 2);

            $table->string('mode', 255);
            $table->string('purpose', 255);
            $table->enum('status', ['queued', 'reversed', 'hold', 'processing', 'processed', 'failed'])->default('queued');

            $table->enum('currency', ['INR'])->default('INR');

            $table->string('status_code', 5)->nullable();
            $table->enum('is_api_call', ['0', '1'])->default('0');
            $table->enum('is_cron', ['0', '1'])->default('0');
            $table->date('cron_date')->nullable();
            $table->text('failed_msg')->nullable();
            $table->string('fee_type', 255)->nullable();
            $table->string('remark', 300)->nullable();
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
