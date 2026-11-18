<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        // Contact details
        'name',
        'phone',
        'phone_country_code',
        'alt_phone',
        'email',
        'city',
        'ip_country_code',
        'ip_address',
        'physical_location',

        // Requirements & Enquiry
        'project_id',
        'enquiry_name',
        'project_name', // alias for enquiry_name
        'sub_project_name',
        'property_type',
        'configuration',
        'budget_min',
        'budget_max',
        'preferred_location',
        'sourcing_price',

        // Pipeline & Assignment
        'source',
        'source_id',
        'status_id',
        'assigned_to',
        'created_by',
        'next_follow_up_date',
        'notes',
        'description',
        'is_hot',
        'is_organic',

        // Digital Marketing & Attribution Tracking
        'gclick_id',
        'url',
        'device',
        'platform',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'source_key',
        'sub_source',
        'campaign_id',
        'campaign_name',
        'ads_id',
        'ads_name',
        'ads_group_id',
        'ads_set_name',
        'ads_extension_id',
        'ads_placement',
        'ads_position',
        'ads_keyword',
        'keyword_type',
        'ad_strategy',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'sourcing_price' => 'decimal:2',
        'next_follow_up_date' => 'datetime',
        'is_hot' => 'boolean',
        'is_organic' => 'boolean',
    ];

    // Backward compatibility for project_name <-> enquiry_name
    public function setProjectNameAttribute($value)
    {
        $this->attributes['enquiry_name'] = $value;
    }

    public function getProjectNameAttribute()
    {
        return $this->attributes['enquiry_name'] ?? null;
    }

    // Display Source Name seamlessly
    public function getSourceNameAttribute()
    {
        if (!empty($this->attributes['source'])) {
            return $this->attributes['source'];
        }
        return $this->leadSource?->name ?? 'Direct';
    }

    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function followups()
    {
        return $this->hasMany(LeadFollowup::class, 'lead_id')->latest('created_at');
    }

    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class, 'lead_id')->latest('visit_date');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class, 'lead_id')->latest('created_at');
    }
}
