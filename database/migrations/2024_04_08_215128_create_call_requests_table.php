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
        Schema::create('call_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained('calls');
            $table->foreignId('biker_id')->constrained('bikers');
            $table->enum('status', ['denied', 'accepted', 'not_answered'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('call_id');
            $table->dropConstrainedForeignId('biker_id');
        });
        Schema::dropIfExists('call_requests');
    }
};
