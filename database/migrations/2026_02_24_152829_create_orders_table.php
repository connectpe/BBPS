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
            $table->string('client_ref_id')->unique();
            $table->string('contact_id')->unique();
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade');

            $table->string('mode_id')->nullable();
            $table->foreign('mode_id')->references('mode_id')->on('payment_modes')->cascadeOnDelete();

            $table->foreignId('service_id')->constrained('global_services')->onDelete('cascade');
            $table->string('connectpe_id', 255)->unique();
            $table->string('order_ref_id', 255)->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('currency', ['INR'])->default('INR');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->nullable();
            $table->decimal('tax', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->string('mode', 255);
            $table->string('purpose', 255);
            $table->string('utr_no', 255)->nullable();
            $table->string('narration', 300)->nullable();
            $table->string('remark', 300)->nullable();
            $table->enum('status', ['queued', 'pending', 'success', 'failed', 'reversed'])->default('queued');
            $table->string('status_code', 5)->nullable();
            $table->string('status_response', 255)->nullable();
            $table->string('failed_status_code', 5)->nullable();
            $table->text('failed_message')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->decimal('txn_refunded', 15, 2)->default(0.00);
            $table->timestamp('txn_refunded_at')->nullable();
            $table->string('ip');
            $table->text('user_agent');
            $table->enum('is_api_call', ['0', '1'])->default('0');
            $table->enum('is_cron', ['0', '1'])->default('0');
            $table->date('cron_date')->nullable();
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
