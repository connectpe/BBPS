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
        Schema::create('payin_api_documentations', function (Blueprint $table) {
            $table->id();
            $table->text('authorization');
            $table->text('request_header');

            // Generate Payment
            $table->longText('generate_payment_response');
            $table->longText('generate_payment_description');

            // Check Status
            $table->longText('check_status_response');
            $table->longText('check_status_description');

            //Callback Examples
            $table->longText('callback_examples_response');
            $table->longText('callback_examples_description');

            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payin_api_documentations');
    }
};
