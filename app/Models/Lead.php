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
        'tags',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'sourcing_price' => 'decimal:2',
        'next_follow_up_date' => 'datetime',
        'is_hot' => 'boolean',
        'is_organic' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Feature 7: Smart Lead Intent Score (0 to 100)
     */
    public function getIntentScoreAttribute(): int
    {
        $score = 15;

        if (!empty($this->phone) && !empty($this->email)) $score += 15;
        if ($this->budget_min > 0 || $this->budget_max > 0) $score += 15;
        if (!empty($this->configuration) || !empty($this->preferred_location)) $score += 10;
        if ($this->is_hot) $score += 15;
        if ($this->followups()->count() > 0) $score += 15;

        $visits = $this->siteVisits();
        if ($visits->count() > 0) {
            $score += 15;
            if ($visits->where('status', 'completed')->exists()) {
                $score += 15;
            }
        }

        return min(100, $score);
    }

    public function getIntentLabelAttribute(): array
    {
        $score = $this->intent_score;
        if ($score >= 75) {
            return ['label' => 'High Intent', 'badge' => 'bg-danger text-white', 'icon' => 'bi-fire'];
        } elseif ($score >= 50) {
            return ['label' => 'Warm / Engaged', 'badge' => 'bg-warning text-dark', 'icon' => 'bi-lightning-charge-fill'];
        }
        return ['label' => 'Nurturing', 'badge' => 'bg-secondary text-white', 'icon' => 'bi-hourglass-split'];
    }

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

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'lead_id')->latest('id');
    }

    public function latestBooking()
    {
        return $this->hasOne(Booking::class, 'lead_id')->latestOfMany();
    }

    /**
     * Cross-Project Portfolio: Find other inquiries by the same buyer across different projects
     */
    public function relatedProjectLeads()
    {
        $cleanPhone = substr(preg_replace('/[^0-9]/', '', (string)$this->phone), -10);

        return static::with(['project', 'status', 'assignedAgent'])
            ->where('id', '!=', $this->id)
            ->where(function($q) use ($cleanPhone) {
                if (!empty($cleanPhone) && strlen($cleanPhone) >= 8) {
                    $q->where('phone', 'like', "%{$cleanPhone}");
                }
                if (!empty($this->email)) {
                    $q->orWhere('email', $this->email);
                }
            })
            ->latest('id')
            ->get();
    }
}
