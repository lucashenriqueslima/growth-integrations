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
        Schema::create('expertise_file_validation_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expertise_file_id')->constrained()->cascadeOnDelete();
            $table->longText('error_message');
            $table->enum('status', ['pending', 'sent', 'read'])->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expertise_file_validation_errors', function (Blueprint $table) {
            $table->dropForeign(['call_id']);
            $table->dropForeign(['expertise_file_id']);
        });
        Schema::dropIfExists('expertise_file_validation_errors');
    }
};
