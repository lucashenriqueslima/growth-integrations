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
            $table->enum('auvo_department', ['expertise', 'inspection', 'tracking']);
            $table->string('external_id');
            $table->string('customer_id')->nullable();
            $table->string('name');
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
