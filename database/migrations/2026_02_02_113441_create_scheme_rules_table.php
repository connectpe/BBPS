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
        Schema::create('scheme_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')->constrained('schemes')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('global_services')->cascadeOnDelete();
            $table->decimal('start_value', 13, 2)->default(0.00)->nullable();
            $table->decimal('end_value', 13, 2)->default(0.00)->nullable();
            $table->enum('type', ['Fixed', 'Percentage']);
            $table->decimal('fee', 10, 2)->default(0.00)->nullable();
            $table->decimal('min_fee', 13, 2)->default(0.00)->nullable();
            $table->decimal('max_fee', 13, 2)->default(0.00)->nullable();
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
        Schema::dropIfExists('scheme_rules');
    }
};
