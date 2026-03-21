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
        Schema::table('business_infos', function (Blueprint $table) {

            // Remove old column
            $table->dropColumn('business_document');

            // Add new columns
            $table->string('individual_photo', 300)->after('aadhar_front_image');
            $table->string('business_address_proof_image', 300)->after('business_pan_name')->nullable();
            $table->string('business_pan_image', 300)->after('business_pan_number');
            $table->string('registration_certificate_image', 300)->after('business_pan_image');
            $table->string('gst_registration_certificate_image', 300)->after('gst_number')->nullable();
            $table->string('inside_image', 300)->after('registration_certificate_image')->nullable();
            $table->string('outside_image', 300)->after('inside_image')->nullable();
            $table->string('signed_moa_image', 300)->after('outside_image')->nullable();
            $table->string('signed_aoa_image', 300)->after('signed_moa_image')->nullable();
            $table->string('board_resoultion_image', 300)->after('signed_aoa_image')->nullable();
            $table->string('nsdl_declaration_image', 300)->after('board_resoultion_image')->nullable();
            $table->enum('itr_filled', ['1', '0'])->after('nsdl_declaration_image');
            $table->string('itr_file_image', 300)->after('itr_filled')->nullable();
            $table->string('itr_not_filed_reason', 300)->after('itr_file_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_infos', function (Blueprint $table) {
            // Drop all newly added columns
            $table->dropColumn([
                'individual_photo',
                'business_address_proof_type',
                'business_pan_image',
                'registration_certificate',
                'gst_registration_certificate',
                'inside_image',
                'outside_image',
                'signed_moa',
                'signed_aoa',
                'board_resolution',
                'nsdl_declaration',
                'itr_filled',
                'itr_file',
            ]);

            // Recreate the old column if you want full rollback
            $table->string('business_document', 300)->nullable()->after('aadhar_front_image');
        });
    }
};
