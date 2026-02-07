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
        Schema::table('ip_whitelists', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained('global_services')->cascadeOnDelete()->after('user_id');
            $table->enum('is_deleted', ['0', '1'])->default('0')->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ip_whitelists', function (Blueprint $table) {
            Schema::table('ip_whitelists', function (Blueprint $table) {
                $table->dropColumn('is_deleted');
                $table->dropForeign(['service_id']); // Drop the foreign key first
                $table->dropColumn('service_id');
            });
        });
    }
};
