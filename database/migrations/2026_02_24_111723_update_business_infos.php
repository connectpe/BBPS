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
        Schema::table('business_infos', function (Blueprint $table) {
            $table->enum('is_pan_verify', ['0', '1'])->after('business_pan_number')->default('0');
            $table->enum('is_cin_verify', ['0', '1'])->after('cin_no')->default('0');
            $table->enum('is_gstin_verify', ['0', '1'])->after('gst_number')->default('0');
            $table->enum('is_bank_details_verify', ['0', '1'])->after('bank_id')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_infos', function (Blueprint $table) {
            $table->dropColumn([
                'is_pan_verify',
                'is_cin_verify',
                'is_gstin_verify',
                'is_bank_details_verify',
            ]);
        });
    }
};
