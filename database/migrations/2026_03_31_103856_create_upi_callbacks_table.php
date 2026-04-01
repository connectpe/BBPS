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
        Schema::create('upi_callbacks', function (Blueprint $table) {
            $table->id();
            $table->string('txn_id', 255)->unique();
            $table->string('txn_order_id', 255)->unique();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('root', 300);
            $table->string('utr', 255)->unique();
            $table->string('status', 255);
            $table->text('message');
            $table->text('response');
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upi_callbacks');
    }
};
