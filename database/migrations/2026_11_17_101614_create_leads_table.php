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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->index();
            $table->string('alt_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('property_type')->nullable(); // Flat, Villa, Plot, Commercial
            $table->string('configuration')->nullable(); // 1 BHK, 2 BHK, 3 BHK, 4+ BHK
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->string('preferred_location')->nullable();
            
            $table->foreignId('source_id')->nullable()->constrained('lead_sources')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('lead_statuses')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('next_follow_up_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->boolean('is_hot')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
