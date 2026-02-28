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
        Schema::table('ladgers', function (Blueprint $table) {
            $table->string('fee', 255)->after('total_txn_amount')->nullable();
            $table->string('tax', 255)->after('fee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ladgers', function (Blueprint $table) {
            $table->dropColumn(['fee', 'tax']);
        });
    }
};
