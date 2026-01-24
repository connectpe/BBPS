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
            $table->longText('business_document')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_infos', function (Blueprint $table) {
            // Rollback: change it back to string(255)
            $table->string('business_document', 255)->nullable()->change();
        });
    }
};
