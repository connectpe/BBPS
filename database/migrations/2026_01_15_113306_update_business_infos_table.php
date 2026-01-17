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
            $table->string('business_pan_number')->nullable()->after('business_name');
            $table->string('business_pan_name')->nullable()->after('business_pan_number');
            $table->string('business_type')->nullable()->after('business_pan_name');
            $table->string('pan_number')->nullable()->after('business_type');
            $table->string('pan_owner_name')->nullable()->after('business_type');

            $table->string('aadhar_number')->nullable()->after('pan_number');
            $table->string('aadhar_name')->nullable()->after('business_type');
            $table->string('gst_number')->nullable()->after('aadhar_name');
            $table->foreignId('bank_id')->nullable()->constrained('users_banks')->onDelete('set null')->after('gst_number');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_infos', function (Blueprint $table) {
            $table->dropColumn([
                'business_pan_number',
                'business_pan_name',
                'business_type',
                'pan_number',
                'pan_owner_name',
                'aadhar_number',
                'aadhar_name',
                'gst_number',
                'bank_id'
            ]);
        });
    }
};
