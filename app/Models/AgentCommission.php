<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'agent_id',
        'commission_type',
        'partner_name',
        'deal_value',
        'commission_percentage',
        'commission_amount',
        'tds_percentage',
        'tds_amount',
        'net_payable',
        'status',
        'approved_by',
        'approved_at',
        'paid_at',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'deal_value' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'tds_percentage' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'pending_approval' => ['label' => 'Pending Approval', 'badge' => 'bg-warning text-dark', 'icon' => 'bi-hourglass-split'],
            'approved' => ['label' => 'Approved (Ready for Payout)', 'badge' => 'bg-primary text-white', 'icon' => 'bi-check-circle-fill'],
            'paid' => ['label' => 'Disbursed / Paid', 'badge' => 'bg-success text-white', 'icon' => 'bi-cash-coin'],
            'cancelled' => ['label' => 'Cancelled / Withheld', 'badge' => 'bg-danger text-white', 'icon' => 'bi-x-circle-fill'],
            default => ['label' => ucfirst($this->status), 'badge' => 'bg-secondary text-white', 'icon' => 'bi-info-circle'],
        };
    }
}
