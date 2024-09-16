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
        Schema::create('auvo_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auvo_customer_id')->constrained('auvo_customers');
            $table->enum('auvo_department', ['expertise', 'inspection', 'tracking']);
            $table->string('external_id');
            $table->string('task_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auvo_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('auvo_customer_id');
        });
        Schema::dropIfExists('auvo_tasks');
    }
};
