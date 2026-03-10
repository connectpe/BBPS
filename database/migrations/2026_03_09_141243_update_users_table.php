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
            $table->string('forget_password_otp', 6)->after('password')->nullable();
            $table->dateTime('password_otp_expires_at')->after('forget_password_otp')->nullable();
            $table->string('forget_mpin_otp', 6)->after('mpin')->nullable();
            $table->dateTime('mpin_otp_expires_at')->after('forget_mpin_otp')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
