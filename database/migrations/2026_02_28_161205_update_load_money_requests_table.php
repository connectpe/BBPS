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
        Schema::table('load_money_requests', function (Blueprint $table) {
            $table->string('request_id', 255)->after('user_id');
            $table->string('remark', 300)->after('request_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('load_money_requests', function (Blueprint $table) {
            $table->dropColumn([
                'request_id',
                'remark',
            ]);
        });
    }
};
