<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'follow_up_type',
        'call_response',
        'notes',
        'follow_up_date',
        'next_follow_up_date',
        'status',
    ];

    protected $casts = [
        'follow_up_date' => 'datetime',
        'next_follow_up_date' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
