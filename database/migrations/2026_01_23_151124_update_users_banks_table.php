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
        Schema::table('users_banks', function (Blueprint $table) {
            $table->string('bank_docs')->nullable()->after('ifsc_code');
            $table->string('account_type')->nullable()->after('bank_docs');
            $table->string('benificiary_name')->nullable()->after('account_type');
            $table->string('branch_name')->nullable()->after('benificiary_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_banks', function (Blueprint $table) {
            $table->dropColumn('bank_docs');
            $table->dropColumn('branch_name');

            $table->dropColumn('account_type');
            $table->dropColumn('benificiary_name');

        });
    }
};
