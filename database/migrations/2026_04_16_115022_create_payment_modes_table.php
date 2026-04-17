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
        Schema::create('payment_modes', function (Blueprint $table) {
            $table->id();
            $table->string('mode_id')->unique();
            $table->foreignId('service_id')->constrained('global_services')->cascadeOnDelete();
            $table->string('mode_name');
            $table->string('mode_slug')->unique();
            $table->decimal('min_order_value', 13, 2)->default(0.00)->nullable();
            $table->decimal('max_order_value', 13, 2)->default(0.00)->nullable();
            $table->decimal('tax_value', 13, 2)->default(0.00)->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_modes');
    }
};
