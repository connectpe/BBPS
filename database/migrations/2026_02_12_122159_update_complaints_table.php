<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('payment_ref_id')->nullable()->after('complaints_category');
            $table->string('mobile_number')->nullable()->after('payment_ref_id');
            $table->date('transaction_date')->after('mobile_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn('payment_ref_id');
            $table->dropColumn('mobile_number');
            $table->dropColumn('transaction_date');
        });
    }
};
