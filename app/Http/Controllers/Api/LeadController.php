<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    /**
     * Get paginated list of leads with filters.
     */
    public function index(Request $request)
    {
        $query = Lead::with(['leadSource', 'status', 'project', 'assignedAgent']);

        // Search by keyword (name, phone, email, city, enquiry_name, source, utm_campaign)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('enquiry_name', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%")
                  ->orWhere('utm_campaign', 'like', "%{$search}%")
                  ->orWhere('campaign_name', 'like', "%{$search}%");
            });
        }

        // Filter by Status
        if ($statusId = $request->input('status_id')) {
            $query->where('status_id', $statusId);
        }

        // Filter by Source
        if ($sourceId = $request->input('source_id')) {
            $query->where('source_id', $sourceId);
        }

        // Filter by UTM Campaign
        if ($utmCampaign = $request->input('utm_campaign')) {
            $query->where('utm_campaign', $utmCampaign);
        }

        // Filter by Platform
        if ($platform = $request->input('platform')) {
            $query->where('platform', $platform);
        }

        // Filter by Project
        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }

        // Filter by Assigned Agent
        if ($assignedTo = $request->input('assigned_to')) {
            $query->where('assigned_to', $assignedTo);
        }

        // Filter by Hot leads
        if ($request->has('is_hot')) {
            $query->where('is_hot', filter_var($request->input('is_hot'), FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by Follow-up due today
        if ($request->input('follow_up_today')) {
            $query->whereDate('next_follow_up_date', now()->toDateString());
        }

        $perPage = $request->input('per_page', 15);
        $leads = $query->latest('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $leads
        ]);
    }

    /**
     * Store new lead (can be called by Webhook, Website Form, or API client).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Core Contact Details
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'phone_country_code' => 'nullable|string|max:10',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'ip_country_code' => 'nullable|string|max:10',
            'ip_address' => 'nullable|string|max:45',
            'physical_location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',

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
            'preferred_location' => 'nullable|string|max:255',

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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Auto capture IP if not provided
        if (empty($data['ip_address'])) {
            $data['ip_address'] = $request->ip();
        }

        // Map description to notes if notes is empty
        if (!empty($data['description']) && empty($data['notes'])) {
            $data['notes'] = $data['description'];
        }

        // Normalise enquiry_name / project_name
        if (empty($data['enquiry_name']) && !empty($data['project_name'])) {
            $data['enquiry_name'] = $data['project_name'];
        }

        // Auto link Project ID if enquiry_name matches an existing project
        $searchProjectName = $data['enquiry_name'] ?? $data['project_name'] ?? null;
        if (!empty($searchProjectName) && empty($data['project_id'])) {
            $project = Project::where('name', 'like', "%{$searchProjectName}%")->first();
            if ($project) {
                $data['project_id'] = $project->id;
            }
        }

        // Smart Source Mapping based on source, utm_source, or platform
        if (empty($data['source_id'])) {
            $sourceIdentifier = strtolower($data['source'] ?? $data['utm_source'] ?? $data['platform'] ?? $data['source_key'] ?? '');
            
            if (str_contains($sourceIdentifier, 'google')) {
                $src = LeadSource::where('code', 'google_ads')->first();
            } elseif (str_contains($sourceIdentifier, 'facebook') || str_contains($sourceIdentifier, 'instagram') || str_contains($sourceIdentifier, 'meta') || str_contains($sourceIdentifier, 'fb')) {
                $src = LeadSource::where('code', 'meta_ads')->first();
            } elseif (str_contains($sourceIdentifier, 'magicbricks')) {
                $src = LeadSource::where('code', 'magicbricks')->first();
            } elseif (str_contains($sourceIdentifier, '99acres')) {
                $src = LeadSource::where('code', '99acres')->first();
            } elseif (str_contains($sourceIdentifier, 'housing')) {
                $src = LeadSource::where('code', 'housing_com')->first();
            } else {
                $src = LeadSource::where('code', 'website_form')->first();
            }

            if ($src) {
                $data['source_id'] = $src->id;
            }
        }

        // If source string not provided, default to matched source name
        if (empty($data['source']) && !empty($data['source_id'])) {
            $matchedSource = LeadSource::find($data['source_id']);
            $data['source'] = $matchedSource?->name;
        }

        // Default status to 'new' if not provided
        if (empty($data['status_id'])) {
            $newStatus = LeadStatus::where('slug', 'new')->first();
            $data['status_id'] = $newStatus ? $newStatus->id : null;
        }

        // Auto-assign Round-Robin to active sales agent
        if (empty($data['assigned_to'])) {
            $agent = User::where('role', 'sales_agent')
                ->where('status', 'active')
                ->withCount('assignedLeads')
                ->orderBy('assigned_leads_count', 'asc')
                ->first();

            $data['assigned_to'] = $agent ? $agent->id : null;
        }

        $data['created_by'] = $request->user() ? $request->user()->id : null;

        $lead = Lead::create($data);

        // Activity log
        $sourceName = $lead->source?->name ?? ($data['utm_source'] ?? 'Website API');
        $campaignInfo = !empty($lead->campaign_name) ? " [Campaign: {$lead->campaign_name}]" : '';
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $lead->created_by,
            'activity_type' => 'created',
            'description' => "Lead captured via API from {$sourceName}{$campaignInfo}. Assigned to " . ($lead->assignedAgent ? $lead->assignedAgent->name : 'Unassigned'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully',
            'lead_id' => $lead->id,
            'data' => $lead->load(['leadSource', 'status', 'project', 'assignedAgent'])
        ], 201);
    }

    /**
     * Get single lead details with complete history.
     */
    public function show($id)
    {
        $lead = Lead::with([
            'leadSource',
            'status',
            'project',
            'assignedAgent',
            'creator',
            'followups.user',
            'siteVisits.project',
            'activities.user'
        ])->find($id);

        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $lead
        ]);
    }

    /**
     * Update lead details or status.
     */
    public function update(Request $request, $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found'], 404);
        }

        $lead->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully',
            'data' => $lead->fresh()->load(['leadSource', 'status', 'project', 'assignedAgent'])
        ]);
    }

    /**
     * Delete lead.
     */
    public function destroy($id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found'], 404);
        }

        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully'
        ]);
    }

    /**
     * Add follow-up note & update next follow-up date.
     */
    public function addFollowup(Request $request, $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'follow_up_type' => 'required|in:call,meeting,whatsapp,email,site_visit',
            'call_response' => 'nullable|string|max:100',
            'notes' => 'required|string',
            'next_follow_up_date' => 'nullable|date',
            'new_status_id' => 'nullable|exists:lead_statuses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $followup = LeadFollowup::create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()?->id ?? $lead->assigned_to,
            'follow_up_type' => $request->follow_up_type,
            'call_response' => $request->call_response,
            'notes' => $request->notes,
            'follow_up_date' => now(),
            'next_follow_up_date' => $request->next_follow_up_date,
            'status' => 'completed',
        ]);

        $updates = [];
        if ($request->filled('next_follow_up_date')) {
            $updates['next_follow_up_date'] = $request->next_follow_up_date;
        }
        if ($request->filled('new_status_id')) {
            $updates['status_id'] = $request->new_status_id;
        }
        if (!empty($updates)) {
            $lead->update($updates);
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()?->id ?? $lead->assigned_to,
            'activity_type' => 'followup_added',
            'description' => "Followup added [{$request->follow_up_type}]: {$request->call_response}",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Follow-up logged successfully',
            'data' => $followup
        ], 201);
    }

    /**
     * Schedule a site visit for this lead.
     */
    public function scheduleSiteVisit(Request $request, $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'visit_date' => 'required|date|after:now',
            'assigned_to' => 'nullable|exists:users,id',
            'cab_required' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $visit = SiteVisit::create([
            'lead_id' => $lead->id,
            'project_id' => $request->project_id,
            'assigned_to' => $request->assigned_to ?? $lead->assigned_to,
            'visit_date' => $request->visit_date,
            'cab_required' => $request->boolean('cab_required', false),
            'status' => 'scheduled',
        ]);

        $visitStatus = LeadStatus::where('slug', 'visit_scheduled')->first();
        if ($visitStatus) {
            $lead->update([
                'status_id' => $visitStatus->id,
                'project_id' => $request->project_id,
                'next_follow_up_date' => $request->visit_date,
            ]);
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()?->id ?? $lead->assigned_to,
            'activity_type' => 'visit_scheduled',
            'description' => "Site visit scheduled for {$visit->visit_date->format('d M Y, h:i A')}",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Site visit scheduled successfully',
            'data' => $visit->load('project')
        ], 201);
    }
}
