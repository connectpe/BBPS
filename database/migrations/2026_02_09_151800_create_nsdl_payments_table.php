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
        Schema::create('nsdl_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('global_services')->cascadeOnDelete();
            $table->string('mobile_no', 15);
            $table->decimal('amount', 13, 2)->default(0.00);
            $table->string('transaction_id')->unique();
            $table->string('utr')->unique()->nullable();
            $table->string('order_id')->unique()->nullable();
            $table->enum('status', ['initiated', 'pending', 'success', 'failed'])->default('initiated');
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nsdl_payments');
    }
};
