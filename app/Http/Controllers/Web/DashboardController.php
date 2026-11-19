<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAgent = $user && $user->role === 'sales_agent';

        // Base query depending on role
        $leadQuery = Lead::query();
        $visitQuery = SiteVisit::where('status', 'scheduled')->where('visit_date', '>=', now());

        if ($isAgent) {
            $leadQuery->where('assigned_to', $user->id);
            $visitQuery->where('assigned_to', $user->id);
        }

        $totalLeads = (clone $leadQuery)->count();
        $newLeadsToday = (clone $leadQuery)->whereDate('created_at', now()->toDateString())->count();
        $hotLeads = (clone $leadQuery)->where('is_hot', true)->count();
        $scheduledVisits = $visitQuery->count();

        $wonStatus = LeadStatus::where('slug', 'won')->first();
        $totalWon = $wonStatus ? (clone $leadQuery)->where('status_id', $wonStatus->id)->count() : 0;

        // Pending follow-ups
        $pendingFollowupsQuery = (clone $leadQuery)->with(['assignedAgent', 'status'])
            ->whereNotNull('next_follow_up_date')
            ->whereDate('next_follow_up_date', '<=', now()->toDateString())
            ->orderBy('next_follow_up_date', 'asc');

        $pendingFollowups = $pendingFollowupsQuery->take(6)->get();

        // Recent leads
        $recentLeads = (clone $leadQuery)->with(['leadSource', 'status', 'assignedAgent'])
            ->latest('id')
            ->take(8)
            ->get();

        // Status pipeline breakdown
        $statuses = LeadStatus::withCount(['leads' => function ($q) use ($isAgent, $user) {
            if ($isAgent) {
                $q->where('assigned_to', $user->id);
            }
        }])->orderBy('order', 'asc')->get();

        // Sources breakdown
        $sources = LeadSource::withCount(['leads' => function ($q) use ($isAgent, $user) {
            if ($isAgent) {
                $q->where('assigned_to', $user->id);
            }
        }])->get();

        // Feature 4: Sales Team Leaderboard & Performance Gamification
        $salesAgents = User::whereIn('role', ['sales_agent', 'manager'])
            ->where('status', 'active')
            ->with(['assignedLeads.status', 'followups', 'siteVisits'])
            ->get();

        $wonStatusId = $wonStatus?->id;

        $leaderboard = $salesAgents->map(function ($agent) use ($wonStatusId) {
            $totalLeads = $agent->assignedLeads->count();
            $wonLeads = $agent->assignedLeads->where('status_id', $wonStatusId);
            $wonCount = $wonLeads->count();
            $callsCount = $agent->followups->count();
            $visitsCount = $agent->siteVisits->count();
            $completedVisits = $agent->siteVisits->where('status', 'completed')->count();

            // Estimated revenue booked
            $revenueBooked = $wonLeads->sum(function($l) {
                return $l->budget_max ?: ($l->budget_min ?: 7500000);
            });

            // Gamification points: 100 per won deal + 25 per site visit + 5 per call logged
            $points = ($wonCount * 100) + ($visitsCount * 25) + ($callsCount * 5);
            $conversionRate = $totalLeads > 0 ? round(($wonCount / $totalLeads) * 100, 1) : 0;

            return (object) [
                'id' => $agent->id,
                'name' => $agent->name,
                'email' => $agent->email,
                'role' => $agent->role,
                'total_leads' => $totalLeads,
                'won_deals' => $wonCount,
                'calls_logged' => $callsCount,
                'site_visits' => $visitsCount,
                'completed_visits' => $completedVisits,
                'revenue_booked' => $revenueBooked,
                'conversion_rate' => $conversionRate,
                'points' => $points,
            ];
        })->sortByDesc('points')->values();

        return view('dashboard', compact(
            'totalLeads',
            'newLeadsToday',
            'hotLeads',
            'scheduledVisits',
            'totalWon',
            'pendingFollowups',
            'recentLeads',
            'statuses',
            'sources',
            'isAgent',
            'user',
            'leaderboard'
        ));
    }
}
