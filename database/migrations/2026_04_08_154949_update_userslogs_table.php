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
        Schema::table('userslogs', function (Blueprint $table) {
            $table->renameColumn('logged_at', 'time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('userslogs', function (Blueprint $table) {
            $table->renameColumn('time', 'logged_at');
        });
    }
};
