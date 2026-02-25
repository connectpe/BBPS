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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('account_no', 50)->after('total_amount');
            $table->string('ifsc_code', 50)->after('account_no');
            $table->string('bank_name', 255)->after('ifsc_code')->nullable();
            $table->string('beneficiary_name', 50)->after('bank_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'account_no',
                'ifsc_code',
                'bank_name',
                'beneficiary_name',
            ]);
        });
    }
};
