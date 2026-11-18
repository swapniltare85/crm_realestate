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
        Schema::create('lead_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('follow_up_type', ['call', 'meeting', 'whatsapp', 'email', 'site_visit'])->default('call');
            $table->string('call_response')->nullable(); // e.g. 'Interested', 'Call Later', 'Not Reachable'
            $table->text('notes');
            $table->dateTime('follow_up_date')->nullable();
            $table->dateTime('next_follow_up_date')->nullable();
            $table->enum('status', ['completed', 'scheduled', 'missed'])->default('completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_followups');
    }
};
