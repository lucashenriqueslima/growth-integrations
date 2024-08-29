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
        Schema::create('auvo_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auvo_department_id')->constrained('auvo_departments');
            $table->string('external_id');
            $table->string('customer_id');
            $table->json('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auvo_customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('auvo_department_id');
        });
        Schema::dropIfExists('auvo_customers');
    }
};
