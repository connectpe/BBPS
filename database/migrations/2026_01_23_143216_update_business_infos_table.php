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
            
            $table->string('business_document')->nullable()->after('business_type');
            $table->string('aadhar_front_image')->nullable()->after('business_document');
            $table->string('aadhar_back_image')->nullable()->after('aadhar_front_image');
            $table->string('pancard_image')->nullable()->after('aadhar_back_image');
           


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_infos', function (Blueprint $table) {
            $table->dropColumn('business_document');
            $table->dropColumn('aadhar_front_image');
            $table->dropColumn('aadhar_back_image');
            $table->dropColumn('pancard_image');
        });
    }
};
