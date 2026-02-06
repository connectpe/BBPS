<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('complaints')
            ->where('status', 'Resolved')
            ->update(['status' => 'Closed']);

        //  Now safely change the ENUM
        Schema::table('complaints', function (Blueprint $table) {
            $table->enum('status', ['Open', 'In Progress', 'Closed'])
                ->default('Open')
                ->nullable(false)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->enum('status', ['Open', 'In Progress', 'Closed', 'Resolved'])
                ->default('Open')
                ->nullable(false)
                ->change();
        });
    }
};
