<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'builder_name',
        'property_type',
        'location',
        'city',
        'price_starting',
        'price_max',
        'status',
        'description',
    ];

    protected $casts = [
        'price_starting' => 'decimal:2',
        'price_max' => 'decimal:2',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'project_id');
    }

    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class, 'project_id');
    }

    public function units()
    {
        return $this->hasMany(ProjectUnit::class, 'project_id')->orderBy('floor', 'desc')->orderBy('unit_number', 'asc');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'project_id')->latest('id');
    }
}
