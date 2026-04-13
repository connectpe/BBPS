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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('setup_cost', 13, 2)->default(200000)->after('payin_wallet_amount');
            $table->enum('setup_cost_paid', ['0', '1'])->default('0')->after('setup_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['setup_cost', 'setup_cost_paid']);
        });
    }
};
