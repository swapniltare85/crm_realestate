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
        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            
            // Commission recipient type
            $table->enum('commission_type', ['internal_agent', 'channel_partner_broker'])->default('internal_agent');
            $table->string('partner_name')->nullable(); // External Broker Name if applicable

            // Calculation
            $table->decimal('deal_value', 15, 2); // Base deal value (agreement value)
            $table->decimal('commission_percentage', 5, 2)->default(1.50); // e.g. 1.5%
            $table->decimal('commission_amount', 15, 2); // Gross commission
            $table->decimal('tds_percentage', 5, 2)->default(5.00); // TDS standard (e.g. 5%)
            $table->decimal('tds_amount', 15, 2)->default(0);
            $table->decimal('net_payable', 15, 2); // Gross minus TDS

            // Lifecycle & Payout Status
            $table->enum('status', ['pending_approval', 'approved', 'paid', 'cancelled'])->default('pending_approval');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('payment_reference', 100)->nullable(); // Bank UTR / Cheque No.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_commissions');
    }
};
