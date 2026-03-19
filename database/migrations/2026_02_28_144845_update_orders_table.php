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
            $table->string('bene_first_name')->after('mode');
            $table->string('bene_last_name')->after('bene_first_name');
            $table->string('bene_email')->after('bene_last_name');
            $table->string('type')->after('bene_email');
            $table->string('account_type')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'bene_first_name',
                'bene_last_name',
                'bene_email',
                'type',
                'account_type'
            ]);

        });
    }
};
