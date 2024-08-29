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
        Schema::create('motorcycles', function (Blueprint $table) {
            $table->id();
            $table->string('locavibe_motorcycle_id');
            $table->foreignId('biker_id')->constrained('bikers');
            $table->string('plate');
            $table->string('brand');
            $table->string('model');
            $table->string('color');
            $table->string('chassi');
            $table->string('renavam');
            $table->string('fipe_code');
            $table->string('fipe_year');
            $table->string('motor_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('motorcycles', function (Blueprint $table) {
            $table->dropForeign(['biker_id']);
        });
        Schema::dropIfExists('motorcycles');
    }
};
