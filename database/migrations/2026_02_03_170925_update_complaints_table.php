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
        Schema::table('complaints', function (Blueprint $table) {


            // 1️ Rename columns
            // $table->renameColumn('service_name', 'service_id');
            // $table->renameColumn('admin_notes', 'remark');
            // $table->renameColumn('category', 'complaints_category');

            // 2️ Change types / nullability
            // $table->unsignedBigInteger('service_id')->change();
            // $table->unsignedBigInteger('complaints_category')->change();
            $table->enum('priority', ['Low', 'Normal', 'High'])->default('Normal')->change();
            $table->text('attachment_path')->nullable()->change();

            // $table->foreignId('service_id')->constrained('global_services')->cascadeOnDelete();
            // $table->foreignId('complaints_category')->constrained('complaints_categories')->cascadeOnDelete();
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {

            // 1️ Drop foreign keys first
            $table->dropForeign(['service_id']);
            $table->dropForeign(['complaints_category']);
            $table->dropForeign(['updated_by']);

            // 2️ Revert types / nullable
            $table->string('service_name')->change();
            $table->text('admin_notes')->nullable()->change();
            $table->string('category')->nullable()->change();
            $table->enum('priority', ['Low', 'Normal', 'High'])->default('Normal')->change();
            $table->text('attachment_path')->nullable()->change();

            // 3️ Rename columns back
            $table->renameColumn('service_id', 'service_name');
            $table->renameColumn('remark', 'admin_notes');
            $table->renameColumn('complaints_category', 'category');
        });
    }
};
