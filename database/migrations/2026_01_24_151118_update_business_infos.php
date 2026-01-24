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
            $table->string('business_email', 255)->after('business_name')->unique();
            $table->string('business_phone', 20)->after('business_email')->unique();
            $table->string('cin_no', 255)->after('aadhar_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_infos', function (Blueprint $table) {
            $table->dropColumn('business_email');
            $table->dropColumn('business_phone');
            $table->dropColumn('cin_no');
        });
    }
};
