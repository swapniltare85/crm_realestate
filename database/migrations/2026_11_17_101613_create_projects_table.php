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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('builder_name')->nullable();
            $table->string('property_type')->default('Apartment'); // Apartment, Villa, Plot, Commercial
            $table->string('location');
            $table->string('city')->default('Mumbai');
            $table->decimal('price_starting', 15, 2)->nullable();
            $table->decimal('price_max', 15, 2)->nullable();
            $table->string('status')->default('Ongoing'); // Ongoing, Ready to Move, Upcoming
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
