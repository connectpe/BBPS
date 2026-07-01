<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users_rootings', function (Blueprint $table) {
            // Drop old column
            $table->dropColumn('service_unique_id');

            // Add new foreign key column
            $table->foreignId('provider_id')->after('service_id')->constrained('providers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('users_rootings', function (Blueprint $table) {

            $table->dropForeign(['provider_id']);
            $table->dropColumn('provider_id');
            $table->string('service_unique_id')->nullable();
        });
    }
};
