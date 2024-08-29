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
        Schema::create('expertise_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expertise_id')->constrained('expertises');
            $table->string('path')->nullable();
            $table->enum('file_expertise_type', [
                'report_audio',
                'report_video',
                'cnh_front_image',
                'crlv_front_image',
                'vehicle_plate_image',
                'vehicle_front_side_image',
                'vehicle_right_side_image',
                'vehicle_left_side_image',
                'vehicle_rear_side_image',
                'vehicle_street_video',
                'road_image',
                'road_sign_image',
                'biker_observation_audio',
                'dynamic_image',
            ]);
            $table->longText('error_message')->nullable();
            $table->boolean('is_approved')->nullable();
            $table->string('refusal_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expertise_files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expertise_id');
        });
        Schema::dropIfExists('expertise_files');
    }
};
