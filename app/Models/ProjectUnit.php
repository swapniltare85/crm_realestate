<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'tower',
        'floor',
        'unit_number',
        'configuration',
        'carpet_area',
        'base_price',
        'status',
        'booked_lead_id',
        'token_amount',
        'booked_at',
        'notes',
    ];

    protected $casts = [
        'floor' => 'integer',
        'carpet_area' => 'integer',
        'base_price' => 'decimal:2',
        'token_amount' => 'decimal:2',
        'booked_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function bookedLead()
    {
        return $this->belongsTo(Lead::class, 'booked_lead_id');
    }
}
