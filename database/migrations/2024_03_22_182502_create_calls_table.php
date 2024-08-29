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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('biker_id')->nullable()->constrained('bikers');
            $table->foreignId('associate_car_id')->constrained('associate_cars');
            $table->string('address');
            $table->geometry('location', subtype: 'point')->nullable();
            $table->longText('observation')->nullable();
            $table->enum('status', ['searching_biker', 'waiting_arrival', 'in_service', 'waiting_validation', 'in_validation', 'waiting_biker_see_validation', 'approved'])->default('searching_biker');
            $table->timestamp('biker_accepted_at')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropForeign(['biker_id']);
            $table->dropForeign(['associate_id']);
        });
        Schema::dropIfExists('calls');
    }
};
