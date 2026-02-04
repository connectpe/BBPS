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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('request_id')->unique()->nullable()->after('reference_number');
            $table->string('mobile_number')->nullable()->after('request_id');
            $table->string('payment_ref_id')->nullable()->after('mobile_number');
            $table->string('payment_account_info')->nullable()->after('payment_ref_id');
            $table->string('recharge_type')->nullable()->after('payment_account_info');
            $table->string('connectpe_id')->nullable()->after('recharge_type');
            $table->enum('cron_status',['0','1'])->default(0)->after('connectpeId');
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('request_id');
            $table->dropColumn('mobile_number');
            $table->dropColumn('payment_ref_id');
            $table->dropColumn('payment_account_info');
            $table->dropColumn('recharge_type');
            $table->dropColumn('cron_status');
            $table->dropColumn('connectpe_id');


        });
    }
};
