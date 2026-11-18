<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function followups()
    {
        return $this->hasMany(LeadFollowup::class, 'user_id');
    }

    /**
     * RBAC Role Helper Methods
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isSalesAgent(): bool
    {
        return $this->role === 'sales_agent';
    }

    public function isTelecaller(): bool
    {
        return $this->role === 'telecaller';
    }

    /**
     * Check if user has permission to view a specific lead
     */
    public function canViewLead(Lead $lead): bool
    {
        if ($this->isAdmin() || $this->isManager()) {
            return true;
        }

        if ($this->isSalesAgent()) {
            return (int) $lead->assigned_to === (int) $this->id;
        }

        return true;
    }

    /**
     * Check if user has permission to edit a specific lead
     */
    public function canEditLead(Lead $lead): bool
    {
        if ($this->isAdmin() || $this->isManager()) {
            return true;
        }

        if ($this->isSalesAgent()) {
            return (int) $lead->assigned_to === (int) $this->id;
        }

        return false;
    }

    /**
     * Check if user has permission to reassign lead to another agent
     */
    public function canReassignLead(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    /**
     * Check if user has permission to permanently delete a lead
     */
    public function canDeleteLead(): bool
    {
        return $this->isAdmin();
    }

    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class, 'assigned_to');
    }
}
