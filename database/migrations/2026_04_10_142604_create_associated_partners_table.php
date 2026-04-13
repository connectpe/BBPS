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
        Schema::create('associated_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('referell_url');
            $table->text('logo');
            $table->integer('priority');
            $table->enum('status', ['0', '1'])->default('1');
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('associated_partners');
    }
};
