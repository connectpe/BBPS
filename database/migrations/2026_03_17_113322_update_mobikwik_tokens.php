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
        Schema::table('mobikwik_tokens', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('expire_at');
            $table->timestamp('rotated_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobikwik_tokens', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'rotated_at'
            ]);
        });
    }
};
