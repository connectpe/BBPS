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
            $table->enum('is_aadhaar_verified', ['0', '1'])->default('0')->after('aadhar_name');
            $table->string('masked_aadhaar_url')->nullable()->after('is_aadhaar_verified');
        });
    }

    public function down(): void
    {
        Schema::table('business_infos', function (Blueprint $table) {
            $table->dropColumn(['is_aadhaar_verified', 'masked_aadhaar_url']);
        });
    }
};
