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
}
