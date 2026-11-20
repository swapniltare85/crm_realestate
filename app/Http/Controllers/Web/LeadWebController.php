<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAgent = $user && $user->role === 'sales_agent';

        $query = Lead::with(['leadSource', 'status', 'project', 'assignedAgent']);

        // Role-based visibility
        if ($isAgent) {
            $query->where('assigned_to', $user->id);
        } elseif ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('enquiry_name', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%")
                  ->orWhere('preferred_location', 'like', "%{$search}%")
                  ->orWhere('campaign_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->filled('source_id')) {
            $query->where('source_id', $request->source_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->has('is_hot')) {
            $query->where('is_hot', true);
        }

        if ($request->has('due_today')) {
            $query->whereDate('next_follow_up_date', '<=', now()->toDateString());
        }

        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        $leads = $query->latest('id')->paginate(12)->withQueryString();

        // Query phone numbers with multiple project inquiries for Cross-Project badges
        $duplicatePhones = Lead::select('phone')
            ->whereNotNull('phone')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('phone')
            ->toArray();

        // Unique tags across database for filter dropdown
        $allTags = Lead::whereNotNull('tags')->pluck('tags')->flatten()->unique()->filter()->values()->toArray();
        if (empty($allTags)) {
            $allTags = ['NRI', 'Investor', 'Immediate-Possession', 'First-Time Buyer', 'Pre-Approved Loan', 'High Networth'];
        }

        $sources = LeadSource::where('is_active', true)->get();
        $statuses = LeadStatus::where('is_active', true)->orderBy('order')->get();
        $projects = Project::all();
        $agents = User::where('role', 'sales_agent')->where('status', 'active')->get();

        return view('leads.index', compact('leads', 'sources', 'statuses', 'projects', 'agents', 'isAgent', 'user', 'duplicatePhones', 'allTags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Core Contact Details
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'phone_country_code' => 'nullable|string|max:10',
            'alt_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'physical_location' => 'nullable|string|max:255',
            'preferred_location' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:45',
            'ip_country_code' => 'nullable|string|max:10',

            // Project & Enquiry Specs
            'enquiry_name' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'sub_project_name' => 'nullable|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'property_type' => 'nullable|string|max:100',
            'configuration' => 'nullable|string|max:50',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'sourcing_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',

            // Lead Pipeline & Assignment
            'source' => 'nullable|string|max:100',
            'source_id' => 'nullable|exists:lead_sources,id',
            'status_id' => 'nullable|exists:lead_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'is_hot' => 'nullable|boolean',
            'is_organic' => 'nullable|boolean',
            'next_follow_up_date' => 'nullable|date',

            // Attribution & UTM Tracking
            'gclick_id' => 'nullable|string|max:255',
            'url' => 'nullable|string',
            'device' => 'nullable|string|max:50',
            'platform' => 'nullable|string|max:100',
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_term' => 'nullable|string|max:255',
            'utm_content' => 'nullable|string|max:255',
            'source_key' => 'nullable|string|max:100',
            'sub_source' => 'nullable|string|max:100',
            'campaign_id' => 'nullable|string|max:100',
            'campaign_name' => 'nullable|string|max:255',
            'ads_id' => 'nullable|string|max:100',
            'ads_name' => 'nullable|string|max:255',
            'ads_group_id' => 'nullable|string|max:100',
            'ads_set_name' => 'nullable|string|max:255',
            'ads_extension_id' => 'nullable|string|max:100',
            'ads_placement' => 'nullable|string|max:100',
            'ads_position' => 'nullable|string|max:100',
            'ads_keyword' => 'nullable|string|max:255',
            'keyword_type' => 'nullable|string|max:100',
            'ad_strategy' => 'nullable|string|max:100',
        ]);

        if (empty($validated['enquiry_name']) && !empty($validated['project_name'])) {
            $validated['enquiry_name'] = $validated['project_name'];
        }

        if (empty($validated['source']) && !empty($validated['source_id'])) {
            $src = LeadSource::find($validated['source_id']);
            $validated['source'] = $src?->name;
        }

        if (empty($validated['status_id'])) {
            $defaultStatus = LeadStatus::where('slug', 'new')->first();
            $validated['status_id'] = $defaultStatus?->id;
        }

        if (empty($validated['assigned_to'])) {
            $agent = User::where('role', 'sales_agent')->where('status', 'active')->first();
            $validated['assigned_to'] = $agent?->id;
        }

        $validated['created_by'] = Auth::id();
        $validated['is_hot'] = $request->has('is_hot');
        $validated['is_organic'] = $request->has('is_organic');

        if ($request->filled('tags')) {
            if (is_string($request->tags)) {
                $validated['tags'] = array_values(array_filter(array_map('trim', explode(',', $request->tags))));
            } elseif (is_array($request->tags)) {
                $validated['tags'] = array_values(array_filter($request->tags));
            }
        }

        $lead = Lead::create($validated);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'created',
            'description' => "Lead created manually. Assigned to " . ($lead->assignedAgent ? $lead->assignedAgent->name : 'Unassigned'),
        ]);

        // Cross-Project Linking: Detect if customer has other project inquiries
        $cleanPhone = substr(preg_replace('/[^0-9]/', '', (string)$lead->phone), -10);
        $existingLeads = Lead::where('id', '!=', $lead->id)
            ->where(function($q) use ($cleanPhone, $lead) {
                if (!empty($cleanPhone) && strlen($cleanPhone) >= 8) {
                    $q->where('phone', 'like', "%{$cleanPhone}");
                }
                if (!empty($lead->email)) {
                    $q->orWhere('email', $lead->email);
                }
            })
            ->with('project')
            ->get();

        if ($existingLeads->isNotEmpty()) {
            $priorProjects = $existingLeads->map(fn($l) => $l->project?->name ?? $l->enquiry_name)->filter()->unique()->implode(', ');
            if (empty($priorProjects)) $priorProjects = 'Prior Project';

            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id() ?? $lead->assigned_to,
                'activity_type' => 'cross_project_linked',
                'description' => "Cross-Project Portfolio: Customer previously inquired for {$priorProjects} (Lead #" . $existingLeads->pluck('id')->implode(', #') . "). Profile linked.",
                'properties' => [
                    'linked_lead_ids' => $existingLeads->pluck('id')->toArray(),
                    'prior_projects' => $priorProjects,
                ],
            ]);

            foreach ($existingLeads as $prevLead) {
                LeadActivity::create([
                    'lead_id' => $prevLead->id,
                    'user_id' => Auth::id() ?? $prevLead->assigned_to,
                    'activity_type' => 'cross_project_linked',
                    'description' => "Cross-Project Opportunity: Customer also inquired for '{$lead->project_name}' (New Lead #{$lead->id}).",
                    'properties' => [
                        'new_lead_id' => $lead->id,
                        'new_project' => $lead->project_name,
                    ],
                ]);
            }
        }

        return redirect()->route('leads.show', $lead->id)
            ->with('success', 'Lead created successfully!');
    }

    public function show($id)
    {
        $user = Auth::user();
        $lead = Lead::with([
            'leadSource',
            'status',
            'project',
            'assignedAgent',
            'creator',
            'followups.user',
            'siteVisits.project',
            'siteVisits.assignedAgent',
            'activities.user',
            'documents.uploader',
            'bookings.unit.project'
        ])->findOrFail($id);

        // RBAC: Check if current user has permission to view this lead
        if ($user && !$user->canViewLead($lead)) {
            return redirect()->route('leads.index')->with('error', 'Unauthorized: Aap sirf apni assigned leads hi dekh sakte hain.');
        }

        $statuses = LeadStatus::where('is_active', true)->orderBy('order')->get();
        $projects = Project::all();
        $agents = User::where('status', 'active')->get();
        $sources = LeadSource::where('is_active', true)->get();

        return view('leads.show', compact('lead', 'statuses', 'projects', 'agents', 'sources'));
    }

    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $user = Auth::user();

        // RBAC: Check if user has permission to edit this lead
        if (!$user || !$user->canEditLead($lead)) {
            return redirect()->back()->with('error', 'Unauthorized: Aapko is lead ko edit karne ki permission nahi hai.');
        }

        $validated = $request->validate([
            // Core Contact Details
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'phone_country_code' => 'nullable|string|max:10',
            'alt_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'physical_location' => 'nullable|string|max:255',
            'preferred_location' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:45',
            'ip_country_code' => 'nullable|string|max:10',

            // Project & Enquiry Specs
            'enquiry_name' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'sub_project_name' => 'nullable|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'property_type' => 'nullable|string|max:100',
            'configuration' => 'nullable|string|max:50',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'sourcing_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',

            // Lead Pipeline & Assignment
            'source' => 'nullable|string|max:100',
            'source_id' => 'nullable|exists:lead_sources,id',
            'status_id' => 'nullable|exists:lead_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'is_hot' => 'nullable|boolean',
            'is_organic' => 'nullable|boolean',
            'next_follow_up_date' => 'nullable|date',

            // Attribution & UTM Tracking
            'gclick_id' => 'nullable|string|max:255',
            'url' => 'nullable|string',
            'device' => 'nullable|string|max:50',
            'platform' => 'nullable|string|max:100',
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_term' => 'nullable|string|max:255',
            'utm_content' => 'nullable|string|max:255',
            'source_key' => 'nullable|string|max:100',
            'sub_source' => 'nullable|string|max:100',
            'campaign_id' => 'nullable|string|max:100',
            'campaign_name' => 'nullable|string|max:255',
            'ads_id' => 'nullable|string|max:100',
            'ads_name' => 'nullable|string|max:255',
            'ads_group_id' => 'nullable|string|max:100',
            'ads_set_name' => 'nullable|string|max:255',
            'ads_extension_id' => 'nullable|string|max:100',
            'ads_placement' => 'nullable|string|max:100',
            'ads_position' => 'nullable|string|max:100',
            'ads_keyword' => 'nullable|string|max:255',
            'keyword_type' => 'nullable|string|max:100',
            'ad_strategy' => 'nullable|string|max:100',
        ]);

        if (empty($validated['enquiry_name']) && !empty($validated['project_name'])) {
            $validated['enquiry_name'] = $validated['project_name'];
        }

        $validated['is_hot'] = $request->has('is_hot');
        $validated['is_organic'] = $request->has('is_organic');

        if ($request->has('tags')) {
            if (is_string($request->tags)) {
                $validated['tags'] = array_values(array_filter(array_map('trim', explode(',', $request->tags))));
            } elseif (is_array($request->tags)) {
                $validated['tags'] = array_values(array_filter($request->tags));
            }
        }

        // RBAC: If user cannot reassign leads (e.g. Sales Agent), prevent modifying assigned_to
        if (!$user->canReassignLead()) {
            unset($validated['assigned_to']);
        }

        // Field Label Map for Friendly Audit Logging
        $fieldLabels = [
            'name' => 'Customer Name',
            'phone' => 'Primary Phone',
            'phone_country_code' => 'Phone Country Code',
            'alt_phone' => 'Alternate Phone',
            'email' => 'Email Address',
            'city' => 'City',
            'physical_location' => 'Physical Location',
            'preferred_location' => 'Preferred Location',
            'ip_address' => 'IP Address',
            'ip_country_code' => 'IP Country Code',
            'enquiry_name' => 'Enquiry Name',
            'project_name' => 'Project Name',
            'sub_project_name' => 'Sub-Project / Tower',
            'project_id' => 'Project Tagged',
            'property_type' => 'Property Type',
            'configuration' => 'Configuration',
            'budget_min' => 'Min Budget',
            'budget_max' => 'Max Budget',
            'sourcing_price' => 'Sourcing Price',
            'description' => 'Description',
            'notes' => 'Internal Notes',
            'source' => 'Lead Source Name',
            'source_id' => 'Source Category',
            'status_id' => 'Pipeline Status',
            'assigned_to' => 'Assigned Executive',
            'is_hot' => 'Hot Priority Flag',
            'is_organic' => 'Organic Lead Flag',
            'next_follow_up_date' => 'Next Follow-up Reminder',
            'tags' => 'Smart Tags',
            'platform' => 'Ad Platform',
            'sub_source' => 'Sub-Source',
            'campaign_name' => 'Campaign Name',
            'campaign_id' => 'Campaign ID',
            'ads_name' => 'Ad Creative Name',
            'ads_id' => 'Ad ID',
            'ads_set_name' => 'Ad Set Name',
            'ads_keyword' => 'Search Keyword',
            'device' => 'Device Type',
            'utm_source' => 'UTM Source',
            'utm_medium' => 'UTM Medium',
            'utm_campaign' => 'UTM Campaign',
            'utm_term' => 'UTM Term',
            'utm_content' => 'UTM Content',
            'gclick_id' => 'Google Click ID (GCLID)',
            'url' => 'Landing Page URL',
        ];

        // Track and compute field-level diff
        $lead->fill($validated);
        $dirty = $lead->getDirty();
        $changesList = [];

        foreach ($dirty as $field => $newValue) {
            if (in_array($field, ['updated_at'])) {
                continue;
            }

            $oldValue = $lead->getOriginal($field);

            // Skip if both are empty/null or identical representation
            if (($oldValue === null || $oldValue === '') && ($newValue === null || $newValue === '')) {
                continue;
            }
            if ((string)$oldValue === (string)$newValue) {
                continue;
            }

            $label = $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field));
            $formattedOld = $oldValue;
            $formattedNew = $newValue;

            if ($field === 'status_id') {
                $oldSt = LeadStatus::find($oldValue);
                $newSt = LeadStatus::find($newValue);
                $formattedOld = $oldSt ? $oldSt->name : ($oldValue ?? 'None');
                $formattedNew = $newSt ? $newSt->name : ($newValue ?? 'None');
            } elseif ($field === 'assigned_to') {
                $oldAg = User::find($oldValue);
                $newAg = User::find($newValue);
                $formattedOld = $oldAg ? $oldAg->name : ($oldValue ? "User #{$oldValue}" : 'Unassigned');
                $formattedNew = $newAg ? $newAg->name : ($newValue ? "User #{$newValue}" : 'Unassigned');
            } elseif ($field === 'project_id') {
                $oldP = Project::find($oldValue);
                $newP = Project::find($newValue);
                $formattedOld = $oldP ? $oldP->name : 'None';
                $formattedNew = $newP ? $newP->name : 'None';
            } elseif ($field === 'source_id') {
                $oldS = LeadSource::find($oldValue);
                $newS = LeadSource::find($newValue);
                $formattedOld = $oldS ? $oldS->name : 'None';
                $formattedNew = $newS ? $newS->name : 'None';
            } elseif ($field === 'is_hot' || $field === 'is_organic') {
                $formattedOld = $oldValue ? 'Yes' : 'No';
                $formattedNew = $newValue ? 'Yes' : 'No';
            } elseif (in_array($field, ['budget_min', 'budget_max', 'sourcing_price'])) {
                $formattedOld = $oldValue !== null ? '₹ ' . number_format((float)$oldValue, 2) . ' L' : '—';
                $formattedNew = $newValue !== null ? '₹ ' . number_format((float)$newValue, 2) . ' L' : '—';
            } elseif ($field === 'next_follow_up_date') {
                $formattedOld = $oldValue ? date('d M Y, h:i A', strtotime($oldValue)) : 'None';
                $formattedNew = $newValue ? date('d M Y, h:i A', strtotime($newValue)) : 'None';
            }

            $changesList[] = [
                'field' => $field,
                'label' => $label,
                'old' => (string) ($formattedOld ?? '—'),
                'new' => (string) ($formattedNew ?? '—'),
            ];
        }

        // Save Lead Updates
        $lead->save();

        if (count($changesList) > 0) {
            $changedLabels = array_column($changesList, 'label');
            $summaryPreview = implode(', ', array_slice($changedLabels, 0, 3));
            if (count($changedLabels) > 3) {
                $summaryPreview .= ' and ' . (count($changedLabels) - 3) . ' more fields';
            }

            $roleDisplay = ucwords(str_replace('_', ' ', $user->role));

            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'activity_type' => 'lead_edited',
                'description' => "Updated by {$user->name} ({$roleDisplay}): Modified {$summaryPreview}.",
                'properties' => [
                    'editor_name' => $user->name,
                    'editor_role' => $user->role,
                    'editor_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 150),
                    'changes_count' => count($changesList),
                    'changes' => $changesList,
                ],
            ]);

            return redirect()->back()->with('success', 'Lead parameters updated and audit log recorded successfully!');
        }

        return redirect()->back()->with('info', 'No changes were made to lead parameters.');
    }

    public function updateStatus(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $user = Auth::user();

        // RBAC: Check if user has permission to edit this lead
        if (!$user || !$user->canEditLead($lead)) {
            return redirect()->back()->with('error', 'Unauthorized: Aapko is lead ka status change karne ki permission nahi hai.');
        }

        $request->validate(['status_id' => 'required|exists:lead_statuses,id']);

        if ((int)$lead->status_id === (int)$request->status_id) {
            return redirect()->back();
        }

        $oldStatusName = $lead->status?->name ?? 'None';
        $lead->update(['status_id' => $request->status_id]);
        $freshLead = $lead->fresh();
        $newStatusName = $freshLead->status?->name ?? 'Unknown';

        // Auto-generate Booking and Agent Commission if marked Won
        if ($freshLead->status?->slug === 'won') {
            Booking::createForWonLead($freshLead);
        }

        $roleDisplay = ucwords(str_replace('_', ' ', $user->role));

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'status_changed',
            'description' => "Pipeline status updated from '{$oldStatusName}' to '{$newStatusName}' by {$user->name} ({$roleDisplay}).",
            'properties' => [
                'editor_name' => $user->name,
                'editor_role' => $user->role,
                'editor_email' => $user->email,
                'ip_address' => $request->ip(),
                'changes_count' => 1,
                'changes' => [
                    [
                        'field' => 'status_id',
                        'label' => 'Pipeline Status',
                        'old' => $oldStatusName,
                        'new' => $newStatusName,
                    ]
                ],
            ],
        ]);

        return redirect()->back()->with('success', 'Status updated to ' . $newStatusName);
    }

    public function addFollowup(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'follow_up_type' => 'required|in:call,meeting,whatsapp,email,site_visit',
            'call_response' => 'nullable|string|max:100',
            'notes' => 'required|string',
            'next_follow_up_date' => 'nullable|date',
            'new_status_id' => 'nullable|exists:lead_statuses,id',
        ]);

        $followup = LeadFollowup::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'follow_up_type' => $validated['follow_up_type'],
            'call_response' => $validated['call_response'] ?? null,
            'notes' => $validated['notes'],
            'follow_up_date' => now(),
            'next_follow_up_date' => $validated['next_follow_up_date'] ?? null,
            'status' => 'completed',
        ]);

        if (!empty($validated['next_follow_up_date'])) {
            $lead->update(['next_follow_up_date' => $validated['next_follow_up_date']]);
        }

        if (!empty($validated['new_status_id'])) {
            $lead->update(['status_id' => $validated['new_status_id']]);
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'followup_added',
            'description' => ucfirst($validated['follow_up_type']) . " logged: " . ($validated['call_response'] ?? 'Follow-up completed'),
        ]);

        return redirect()->back()->with('success', 'Follow-up conversation saved successfully!');
    }

    public function scheduleSiteVisit(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'visit_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'cab_required' => 'nullable|boolean',
        ]);

        $validated['lead_id'] = $lead->id;
        $validated['assigned_to'] = $validated['assigned_to'] ?? $lead->assigned_to;
        $validated['cab_required'] = $request->has('cab_required');
        $validated['status'] = 'scheduled';

        $visit = SiteVisit::create($validated);

        // Advance lead status to Site Visit Scheduled if available
        $visitStatus = LeadStatus::where('slug', 'visit_scheduled')->first();
        if ($visitStatus) {
            $lead->update(['status_id' => $visitStatus->id]);
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'site_visit_scheduled',
            'description' => "Site visit scheduled for {$visit->visit_date->format('d M Y, h:i A')} at " . ($visit->project ? $visit->project->name : 'Project'),
        ]);

        return redirect()->back()->with('success', 'Site visit scheduled successfully!');
    }

    public function bulkReassign(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->canReassignLead()) {
            return redirect()->back()->with('error', 'Unauthorized: Sirf Admin aur Manager hi leads reassign kar sakte hain.');
        }

        $validated = $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $newAgent = User::findOrFail($validated['assigned_to']);
        $count = 0;

        foreach ($validated['lead_ids'] as $leadId) {
            $lead = Lead::find($leadId);
            if (!$lead) continue;

            $oldAgentName = $lead->assignedAgent?->name ?? 'Unassigned';
            if ((int)$lead->assigned_to !== (int)$newAgent->id) {
                $lead->assigned_to = $newAgent->id;
                $lead->save();

                LeadActivity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $user->id,
                    'activity_type' => 'lead_edited',
                    'description' => "Reassigned from {$oldAgentName} to {$newAgent->name} by {$user->name} (Bulk Action).",
                    'properties' => [
                        'editor_name' => $user->name,
                        'editor_role' => $user->role,
                        'editor_email' => $user->email,
                        'ip_address' => $request->ip(),
                        'changes_count' => 1,
                        'changes' => [
                            [
                                'field' => 'assigned_to',
                                'label' => 'Assigned Executive',
                                'old' => $oldAgentName,
                                'new' => $newAgent->name,
                            ]
                        ],
                    ],
                ]);
                if ($newStatus->slug === 'won') {
                    Booking::createForWonLead($lead);
                }

                $count++;
            }
        }

        return redirect()->back()->with('success', "{$count} lead(s) successfully reassigned to {$newAgent->name}!");
    }

    public function bulkStatus(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isManager())) {
            return redirect()->back()->with('error', 'Unauthorized: Action not permitted.');
        }

        $validated = $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'status_id' => 'required|exists:lead_statuses,id',
        ]);

        $newStatus = LeadStatus::findOrFail($validated['status_id']);
        $count = 0;

        foreach ($validated['lead_ids'] as $leadId) {
            $lead = Lead::find($leadId);
            if (!$lead) continue;

            $oldStatusName = $lead->status?->name ?? 'Unknown';
            if ((int)$lead->status_id !== (int)$newStatus->id) {
                $lead->status_id = $newStatus->id;
                $lead->save();

                LeadActivity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $user->id,
                    'activity_type' => 'status_changed',
                    'description' => "Pipeline stage updated from '{$oldStatusName}' to '{$newStatus->name}' by {$user->name} (Bulk Action).",
                    'properties' => [
                        'editor_name' => $user->name,
                        'editor_role' => $user->role,
                        'editor_email' => $user->email,
                        'ip_address' => $request->ip(),
                        'changes_count' => 1,
                        'changes' => [
                            [
                                'field' => 'status_id',
                                'label' => 'Pipeline Status',
                                'old' => $oldStatusName,
                                'new' => $newStatus->name,
                            ]
                        ],
                    ],
                ]);
                if ($newStatus->slug === 'won') {
                    Booking::createForWonLead($lead);
                }

                $count++;
            }
        }

        return redirect()->back()->with('success', "{$count} lead(s) status updated to '{$newStatus->name}'!");
    }

    public function updateSiteVisitOutcome(Request $request, $id, $visitId)
    {
        $lead = Lead::findOrFail($id);
        $user = Auth::user();

        if (!$user || !$user->canEditLead($lead)) {
            return redirect()->back()->with('error', 'Unauthorized: Aapko is visit ka outcome update karne ka access nahi hai.');
        }

        $validated = $request->validate([
            'status' => 'required|in:completed,rescheduled,cancelled',
            'client_feedback' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'new_status_id' => 'nullable|exists:lead_statuses,id',
        ]);

        $visit = SiteVisit::where('lead_id', $lead->id)->findOrFail($visitId);
        $oldStatus = $visit->status;
        $visit->update([
            'status' => $validated['status'],
            'client_feedback' => $validated['client_feedback'],
            'rating' => $validated['rating'] ?? $visit->rating,
        ]);

        if (!empty($validated['new_status_id'])) {
            $lead->update(['status_id' => $validated['new_status_id']]);
        }

        $roleDisplay = ucwords(str_replace('_', ' ', $user->role));
        $statusDisplay = ucfirst($validated['status']);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'site_visit_scheduled',
            'description' => "Property Site Visit marked as {$statusDisplay} by {$user->name} ({$roleDisplay}). Client Feedback: \"{$validated['client_feedback']}\"",
            'properties' => [
                'editor_name' => $user->name,
                'editor_role' => $user->role,
                'editor_email' => $user->email,
                'ip_address' => $request->ip(),
                'visit_id' => $visit->id,
                'visit_status' => $validated['status'],
                'rating' => $validated['rating'] ?? null,
                'feedback' => $validated['client_feedback'],
            ],
        ]);

        return redirect()->back()->with('success', "Site visit marked as '{$statusDisplay}' with client feedback recorded.");
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || !$user->canDeleteLead()) {
            return redirect()->back()->with('error', 'Unauthorized: Sirf Admin users hi leads ko delete kar sakte hain.');
        }

        $lead = Lead::findOrFail($id);
        $leadName = $lead->name;
        $lead->delete();

        return redirect()->route('leads.index')->with('success', "Lead #{$id} ({$leadName}) record deleted successfully.");
    }
}
