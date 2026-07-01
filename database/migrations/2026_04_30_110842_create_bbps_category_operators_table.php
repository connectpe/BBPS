<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 
     * Run the migrations.
     * 
     */
    public function up(): void
    {
        Schema::create('bbps_category_operators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bbps_category_id')->constrained('bbps_categories')->onDelete('cascade');
            $table->integer('operator_id')->unsigned();
            $table->string('biller_name');
            $table->string('biller_id');
            $table->enum('status', ['1', '0'])->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bbps_category_operators');
    }
};
