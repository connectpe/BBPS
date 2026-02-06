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
        Schema::create('ladgers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no',255)->nullable();
            $table->string('request_id',255)->nullable();
            $table->string('connectpe_id',255)->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('txn_amount');
            $table->string('total_txn_amount');
            $table->timestamp('txn_date')->nullable();
            $table->enum('txn_type',['cr','dr'])->default('dr');
            $table->foreignId('service_id')->constrained('global_services')->cascadeOnDelete();
            $table->string('opening_balance');
            $table->string('closing_balanace');
            $table->text('remarks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ladgers');
    }
};
