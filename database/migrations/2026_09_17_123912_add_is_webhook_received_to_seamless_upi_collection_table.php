<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seamless_upi_collections', function (Blueprint $table) {
            $table->boolean('is_webhook_received')
                ->default(0)
                ->after('is_auto_settlement');
        });
    }

    public function down(): void
    {
        Schema::table('seamless_upi_collections', function (Blueprint $table) {
            $table->dropColumn('is_webhook_received');
        });
    }
};