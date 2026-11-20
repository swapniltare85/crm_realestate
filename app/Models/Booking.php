<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_reference',
        'lead_id',
        'project_id',
        'unit_id',
        'agent_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'agreement_value',
        'token_amount',
        'booking_date',
        'status',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'agreement_value' => 'decimal:2',
        'token_amount' => 'decimal:2',
        'booking_date' => 'date',
    ];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function unit()
    {
        return $this->belongsTo(ProjectUnit::class, 'unit_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function milestones()
    {
        return $this->hasMany(PaymentMilestone::class, 'booking_id')->orderBy('stage_order', 'asc');
    }

    public function commissions()
    {
        return $this->hasMany(AgentCommission::class, 'booking_id');
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class, 'booking_id')->latest('id');
    }

    // Financial calculations
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->milestones()->where('status', 'paid')->sum('amount');
    }

    public function getTotalPendingAttribute(): float
    {
        return max(0, (float) $this->agreement_value - $this->total_paid);
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->agreement_value <= 0) return 0;
        return (int) min(100, round(($this->total_paid / $this->agreement_value) * 100));
    }

    // Status Badges
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'confirmed' => ['label' => 'Confirmed', 'badge' => 'bg-primary text-white', 'icon' => 'bi-check-circle-fill'],
            'under_registration' => ['label' => 'Under Registration', 'badge' => 'bg-info text-dark', 'icon' => 'bi-file-earmark-text-fill'],
            'completed' => ['label' => 'Possession Completed', 'badge' => 'bg-success text-white', 'icon' => 'bi-patch-check-fill'],
            'cancelled' => ['label' => 'Cancelled', 'badge' => 'bg-danger text-white', 'icon' => 'bi-x-circle-fill'],
            default => ['label' => ucfirst($this->status), 'badge' => 'bg-secondary text-white', 'icon' => 'bi-info-circle'],
        };
    }

    public function getPaymentStatusBadgeAttribute(): array
    {
        return match($this->payment_status) {
            'token_received' => ['label' => 'Token Received', 'badge' => 'bg-warning text-dark'],
            'partially_paid' => ['label' => 'Partially Paid', 'badge' => 'bg-info text-dark'],
            'fully_paid' => ['label' => '100% Fully Paid', 'badge' => 'bg-success text-white'],
            'cancelled' => ['label' => 'Refunded / Cancelled', 'badge' => 'bg-danger text-white'],
            default => ['label' => ucfirst(str_replace('_', ' ', $this->payment_status)), 'badge' => 'bg-secondary text-white'],
        };
    }

    /**
     * Auto-generate Booking, Milestones, and Agent Commission when a lead is marked Won.
     */
    public static function createForWonLead(Lead $lead, $dealValue = null): ?self
    {
        $existing = self::where('lead_id', $lead->id)->first();
        if ($existing) {
            return $existing;
        }

        $projectId = $lead->project_id ?: (Project::first()?->id ?: 1);
        $agentId = $lead->assigned_to ?: (User::where('role', 'sales_agent')->first()?->id ?: 1);

        // Determine deal value
        $val = $dealValue ?: ($lead->budget_max > 0 ? (float)$lead->budget_max : ($lead->budget_min > 0 ? (float)$lead->budget_min : 12500000));
        $token = round($val * 0.05, 2);

        // Check for available unit in this project
        $unit = ProjectUnit::where('project_id', $projectId)->where('status', 'available')->first();

        $nextId = (self::max('id') ?? 0) + 1;
        $ref = 'BK-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $booking = self::create([
            'booking_reference' => $ref,
            'lead_id' => $lead->id,
            'project_id' => $projectId,
            'unit_id' => $unit?->id,
            'agent_id' => $agentId,
            'customer_name' => $lead->name ?: 'Customer',
            'customer_phone' => $lead->phone ?: '9820000000',
            'customer_email' => $lead->email,
            'agreement_value' => $val,
            'token_amount' => $token,
            'booking_date' => now()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'token_received',
            'notes' => 'Auto-generated on deal won status update.',
        ]);

        if ($unit) {
            $unit->update([
                'status' => 'sold',
                'booked_lead_id' => $lead->id,
                'token_amount' => $token,
                'booked_at' => now(),
            ]);
        }

        // 6 Standard Construction Milestones
        $stages = [
            ['name' => '1. Booking & Token Amount (10%)', 'pct' => 10, 'days' => 0],
            ['name' => '2. Agreement & Registration (10%)', 'pct' => 10, 'days' => 30],
            ['name' => '3. Plinth Level Completion (20%)', 'pct' => 20, 'days' => 90],
            ['name' => '4. RCC Slabs & Structure (25%)', 'pct' => 25, 'days' => 180],
            ['name' => '5. Brickwork, Flooring & Finishing (20%)', 'pct' => 20, 'days' => 270],
            ['name' => '6. Final Possession & Key Handover (15%)', 'pct' => 15, 'days' => 365],
        ];

        foreach ($stages as $index => $stage) {
            $amount = round($val * ($stage['pct'] / 100), 2);
            $dueDate = date('Y-m-d', strtotime("+" . $stage['days'] . " days"));
            $isPaid = ($index === 0 && $token > 0);

            PaymentMilestone::create([
                'booking_id' => $booking->id,
                'milestone_name' => $stage['name'],
                'stage_order' => $index + 1,
                'percentage' => $stage['pct'],
                'amount' => $isPaid ? $token : $amount,
                'due_date' => $dueDate,
                'paid_date' => $isPaid ? now()->toDateString() : null,
                'status' => $isPaid ? 'paid' : ($index === 0 ? 'due' : 'pending'),
                'payment_method' => $isPaid ? 'Online / Cheque' : null,
                'transaction_reference' => $isPaid ? 'Token Received' : null,
                'notes' => $isPaid ? 'Initial advance token received.' : null,
            ]);
        }

        // Agent Commission (1.50% standard, 5% TDS)
        $commPct = 1.50;
        $gross = round($val * ($commPct / 100), 2);
        $tds = round($gross * 0.05, 2);
        $net = max(0, $gross - $tds);

        AgentCommission::create([
            'booking_id' => $booking->id,
            'agent_id' => $agentId,
            'commission_type' => 'internal_agent',
            'deal_value' => $val,
            'commission_percentage' => $commPct,
            'commission_amount' => $gross,
            'tds_percentage' => 5.00,
            'tds_amount' => $tds,
            'net_payable' => $net,
            'status' => 'pending_approval',
            'notes' => "Deal closed for {$lead->name} ({$ref}).",
        ]);

        return $booking;
    }

    /**
     * Auto-sync any leads marked Won that don't yet have a Booking.
     */
    public static function syncAllWonLeads(): int
    {
        $wonStatus = LeadStatus::where('slug', 'won')->first();
        if (!$wonStatus) return 0;

        $wonLeads = Lead::where('status_id', $wonStatus->id)
            ->whereDoesntHave('bookings')
            ->get();

        $count = 0;
        foreach ($wonLeads as $l) {
            self::createForWonLead($l);
            $count++;
        }

        return $count;
    }
}
