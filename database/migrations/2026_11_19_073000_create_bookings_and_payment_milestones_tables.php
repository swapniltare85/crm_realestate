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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference', 30)->unique(); // e.g. BK-2026-0001
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('project_units')->nullOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();

            // Customer snapshot at booking time
            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->string('customer_email')->nullable();

            // Financials
            $table->decimal('agreement_value', 15, 2); // Total selling price (INR)
            $table->decimal('token_amount', 15, 2)->default(0); // Initial token / booking amount
            $table->date('booking_date');
            
            // Statuses
            $table->enum('status', ['confirmed', 'under_registration', 'completed', 'cancelled'])->default('confirmed');
            $table->enum('payment_status', ['token_received', 'partially_paid', 'fully_paid', 'cancelled'])->default('token_received');
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('milestone_name'); // e.g. Booking Token, Plinth Slab (20%), Possession
            $table->integer('stage_order')->default(1);
            $table->decimal('percentage', 5, 2)->default(0); // % of agreement value
            $table->decimal('amount', 15, 2); // Exact amount in INR
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->enum('status', ['pending', 'due', 'paid', 'overdue'])->default('pending');
            $table->string('payment_method', 50)->nullable(); // Cheque, NEFT/RTGS, UPI, Cash
            $table->string('transaction_reference', 100)->nullable(); // Cheque / UTR No.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_milestones');
        Schema::dropIfExists('bookings');
    }
};
