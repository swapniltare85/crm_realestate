<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'project_id',
        'assigned_to',
        'visit_date',
        'status',
        'cab_required',
        'client_feedback',
        'rating',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'cab_required' => 'boolean',
        'rating' => 'integer',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
