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
        Schema::create('associate_cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('associate_id')->constrained('associates');
            $table->bigInteger('ileva_associate_vehicle_id')->nullable();
            $table->string('plate');
            $table->string('brand');
            $table->string('model');
            $table->string('color');
            $table->string('year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('associate_cars', function (Blueprint $table) {
            $table->dropForeign(['associate_id']);
        });
        Schema::dropIfExists('associate_cars');
    }
};
