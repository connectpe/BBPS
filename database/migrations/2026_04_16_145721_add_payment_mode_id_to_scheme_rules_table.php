<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheme_rules', function (Blueprint $table) {
            $table->string('payment_mode_id')->nullable()->after('service_id');
            $table->foreign('payment_mode_id')->references('mode_id')->on('payment_modes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('scheme_rules', function (Blueprint $table) {
            $table->dropForeign(['payment_mode_id']);
            $table->dropColumn('payment_mode_id');
        });
    }
};