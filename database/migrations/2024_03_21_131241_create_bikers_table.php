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
        Schema::create('bikers', function (Blueprint $table) {
            $table->id();
            $table->string('locavibe_biker_id');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('cpf');
            $table->string('cnh');
            $table->string('firebase_token')->nullable();
            $table->enum('status', ['avaible', 'not_avaible', 'busy'])->default('avaible');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bikers');
    }
};
