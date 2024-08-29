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
        Schema::create('expertises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('call_id')->constrained('calls');
            $table->integer('app_expertise_index');
            $table->enum('type', ['main', 'secondary']);
            $table->enum('person_type', ['associate', 'third_party', 'eyewitness']);
            $table->enum('status', ['canceled', 'done', 'waiting', 'waiting_validate_from_others'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expertises', function (Blueprint $table) {
            $table->dropConstrainedForeignId('call_id');
        });
        Schema::dropIfExists('expertises');
    }
};
