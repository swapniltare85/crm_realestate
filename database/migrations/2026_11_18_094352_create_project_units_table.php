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
        Schema::create('project_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('tower', 50)->default('Tower A');
            $table->integer('floor')->default(1);
            $table->string('unit_number', 20); // e.g. 101, 102, 402, 1204
            $table->string('configuration', 50)->default('2 BHK'); // 1 BHK, 2 BHK, 3 BHK, 4 BHK, Penthouse
            $table->integer('carpet_area')->default(1200); // in sq.ft.
            $table->decimal('base_price', 15, 2)->default(0); // in Rupees
            $table->enum('status', ['available', 'blocked', 'sold'])->default('available');
            $table->foreignId('booked_lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->decimal('token_amount', 12, 2)->nullable();
            $table->dateTime('booked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_units');
    }
};
