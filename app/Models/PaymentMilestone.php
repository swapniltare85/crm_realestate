<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'milestone_name',
        'stage_order',
        'percentage',
        'amount',
        'due_date',
        'paid_date',
        'status',
        'payment_method',
        'transaction_reference',
        'notes',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'paid' => ['label' => 'Paid', 'badge' => 'bg-success text-white', 'icon' => 'bi-check-all'],
            'due' => ['label' => 'Due Now', 'badge' => 'bg-warning text-dark', 'icon' => 'bi-clock-history'],
            'overdue' => ['label' => 'Overdue', 'badge' => 'bg-danger text-white', 'icon' => 'bi-exclamation-triangle-fill'],
            default => ['label' => 'Upcoming / Pending', 'badge' => 'bg-secondary text-white', 'icon' => 'bi-hourglass-split'],
        };
    }
}
