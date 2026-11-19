@extends('layouts.app')

@section('title', $lead->name . ' - Lead Profile & Parameters')
@section('page-title', 'Lead Details & Full Parameters')

@section('content')
@php
    $sources = $sources ?? \App\Models\LeadSource::where('is_active', true)->get();
    $currentUser = Auth::user();
    $canEdit = $currentUser ? $currentUser->canEditLead($lead) : false;
    $canReassign = $currentUser ? $currentUser->canReassignLead() : false;

    // Distinct separation: Edit Logs (field modifications) vs Audit Trail (system/lifecycle history)
    $editLogs = $lead->activities->filter(function($act) {
        return $act->activity_type === 'lead_edited' || 
               ($act->activity_type === 'updated' && !empty($act->properties['changes']));
    });

    $auditTrail = $lead->activities->filter(function($act) {
        return $act->activity_type !== 'lead_edited';
    });

    // Cross-Project Portfolio: Find other inquiries by the same buyer across different projects
    $crossProjectLeads = $lead->relatedProjectLeads();
@endphp

<!-- Lead Profile Header Card -->
<div class="card mb-3">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded bg-secondary text-white d-flex align-items-center justify-content-center fw-bold fs-4 flex-shrink-0" style="width: 52px; height: 52px; background-color: #334155 !important;">
                    {{ strtoupper(substr($lead->name, 0, 1)) }}
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small fw-bold">#{{ $lead->id }}</span>
                        <h5 class="fw-bold mb-0 text-dark">{{ $lead->name }}</h5>
                        @if($lead->is_hot)
                            <span class="badge-hot-pro"><i class="bi bi-fire"></i> Hot Priority</span>
                        @endif
                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.72rem;">
                            <i class="bi bi-tag me-1"></i> {{ $lead->source_name }}
                        </span>
                        @if($lead->is_organic)
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">Organic</span>
                        @else
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">Paid Campaign</span>
                        @endif
                        @if($crossProjectLeads->isNotEmpty())
                            <span class="badge bg-purple-subtle text-purple border fw-semibold" style="background-color: #f3e8ff; border-color: #d8b4fe !important; font-size: 0.7rem; color: #7e22ce;">
                                <i class="bi bi-diagram-3 me-1"></i> Multi-Project ({{ $crossProjectLeads->count() + 1 }} Inquiries)
                            </span>
                        @endif

                        <!-- Feature 7: Intent Score Badge -->
                        <span class="badge {{ $lead->intent_label['badge'] }} border py-1 px-2" style="font-size: 0.72rem;" title="AI Purchase Intent Score: {{ $lead->intent_score }}/100">
                            <i class="bi {{ $lead->intent_label['icon'] }} me-1"></i> Intent: {{ $lead->intent_score }}/100 ({{ $lead->intent_label['label'] }})
                        </span>

                        <!-- Feature 5: Smart Tags -->
                        @if(!empty($lead->tags) && is_array($lead->tags))
                            @foreach($lead->tags as $tg)
                                <span class="badge bg-light text-secondary border fw-medium" style="font-size: 0.7rem;">#{{ $tg }}</span>
                            @endforeach
                        @endif
                    </div>
                    <div class="text-muted small mt-1 d-flex flex-wrap align-items-center gap-3" style="font-size: 0.78rem;">
                        <span class="text-dark fw-medium">
                            <i class="bi bi-telephone text-primary me-1"></i> 
                            {{ $lead->phone_country_code }} {{ $lead->phone }}
                        </span>
                        @if($lead->email)
                            <span><i class="bi bi-envelope me-1"></i> {{ $lead->email }}</span>
                        @endif
                        @if($lead->city || $lead->physical_location)
                            <span><i class="bi bi-geo-alt me-1 text-danger"></i> {{ $lead->physical_location ?? $lead->city }}</span>
                        @endif
                        <span><i class="bi bi-calendar3 me-1"></i> Ingested: {{ $lead->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>

            <!-- Header Fast Actions & Role Locks -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="tel:{{ $lead->phone }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-telephone-fill text-primary"></i>
                    <span>Call</span>
                </a>
                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#whatsappTemplatesModal" title="Send WhatsApp Message with Preset Templates">
                    <i class="bi bi-whatsapp"></i>
                    <span>WhatsApp</span>
                </button>

                <!-- Feature 1: EMI Calculator Button -->
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#emiCalculatorModal" title="Calculate EMI & Share Loan Options">
                    <i class="bi bi-calculator"></i>
                    <span>EMI Calc</span>
                </button>

                <!-- Feature 6: Branded Cost Sheet Button -->
                <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#costSheetModal" title="Generate Branded Cost Sheet & Quotation">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    <span>Cost Sheet</span>
                </button>

                <!-- Pipeline Status Quick Switcher (Enforces RBAC) -->
                @if($canEdit)
                    <form action="{{ route('leads.update-status', $lead->id) }}" method="POST" class="d-inline">
                        @csrf
                        <select name="status_id" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()" style="border-color: {{ $lead->status?->color }}; color: {{ $lead->status?->color }}; min-width: 160px;" title="Quick status change">
                            @foreach($statuses as $st)
                                <option value="{{ $st->id }}" {{ $lead->status_id == $st->id ? 'selected' : '' }}>
                                    ● {{ $st->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @else
                    <span class="status-pill" style="background-color: {{ $lead->status?->color }}15; color: {{ $lead->status?->color }}; border-color: {{ $lead->status?->color }}30; height: 31px; display: inline-flex; align-items: center;" title="Only assigned executive or admin can change status">
                        <span class="status-dot" style="background-color: {{ $lead->status?->color }};"></span>
                        {{ $lead->status?->name ?? 'New' }}
                        <i class="bi bi-lock-fill ms-1 opacity-75"></i>
                    </span>
                @endif

                @if($canEdit)
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editLeadModal" title="Edit All Parameters">
                        <i class="bi bi-pencil-square"></i>
                        <span>Edit Parameters</span>
                    </button>
                @else
                    <button class="btn btn-sm btn-outline-secondary disabled" title="Edit locked: Only assigned executive or Admin can modify" disabled>
                        <i class="bi bi-lock-fill me-1 text-muted"></i>
                        <span>Edit Locked</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Cross-Project Portfolio Banner (If buyer has inquiries in other projects) -->
@if($crossProjectLeads->isNotEmpty())
    <div class="card mb-3 shadow-none" style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #10b981 !important;">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success text-white py-1 px-2 fw-semibold" style="font-size: 0.72rem;">
                        <i class="bi bi-diagram-3 me-1"></i> Cross-Project Portfolio
                    </span>
                    <strong class="text-dark small">This buyer has {{ $crossProjectLeads->count() }} other project inquiry in your CRM:</strong>
                </div>
                <span class="text-muted small" style="font-size: 0.72rem;">
                    <i class="bi bi-info-circle me-1"></i> Multi-project opportunity detected via shared phone ({{ $lead->phone }})
                </span>
            </div>

            <div class="row g-2">
                @foreach($crossProjectLeads as $relLead)
                    <div class="col-md-6 col-lg-4">
                        <div class="bg-white p-2.5 rounded border d-flex justify-content-between align-items-center shadow-none">
                            <div class="text-truncate me-2">
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="text-muted fw-bold small">#{{ $relLead->id }}</span>
                                    <strong class="text-dark small text-truncate" style="max-width: 150px;">
                                        {{ $relLead->project?->name ?? ($relLead->enquiry_name ?? 'Other Project') }}
                                    </strong>
                                </div>
                                <div class="text-muted small mt-0.5" style="font-size: 0.72rem;">
                                    <span>{{ $relLead->property_type ?? 'Property' }} {{ $relLead->configuration ? '(' . $relLead->configuration . ')' : '' }}</span>
                                    @if($relLead->budget_max)
                                        · <span class="fw-semibold text-dark">₹{{ number_format($relLead->budget_max / 100000, 1) }}L</span>
                                    @endif
                                </div>
                                <div class="text-muted small" style="font-size: 0.7rem;">
                                    Agent: <span class="text-secondary fw-medium">{{ $relLead->assignedAgent?->name ?? 'Unassigned' }}</span>
                                    · Stage: <span class="fw-semibold" style="color: {{ $relLead->status?->color }};">{{ $relLead->status?->name }}</span>
                                </div>
                            </div>
                            <a href="{{ route('leads.show', $relLead->id) }}" class="btn btn-sm btn-outline-success py-1 px-2 flex-shrink-0" style="font-size: 0.72rem;" title="View Lead #{{ $relLead->id }} Details">
                                View #{{ $relLead->id }} <i class="bi bi-arrow-right ms-0.5"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- Main Tabs: 1) All Parameters, 2) Follow-up Calls, 3) Site Visits, 4) Audit Trail, 5) Edit Logs -->
<div class="card mb-4">
    <div class="card-header border-bottom p-0">
        <ul class="nav nav-tabs border-0" id="leadMasterTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold small py-3 px-3 border-0 rounded-0" id="parameters-tab" data-bs-toggle="tab" data-bs-target="#parametersTabPane" type="button" role="tab">
                    <i class="bi bi-sliders me-1 text-primary"></i> All Parameters
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold small py-3 px-3 border-0 rounded-0" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timelineTabPane" type="button" role="tab">
                    <i class="bi bi-telephone-plus me-1 text-info"></i> Follow-up Calls ({{ $lead->followups->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold small py-3 px-3 border-0 rounded-0" id="visits-tab" data-bs-toggle="tab" data-bs-target="#visitsTabPane" type="button" role="tab">
                    <i class="bi bi-calendar2-check me-1 text-warning"></i> Site Visits ({{ $lead->siteVisits->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold small py-3 px-3 border-0 rounded-0" id="audit-tab" data-bs-toggle="tab" data-bs-target="#auditTabPane" type="button" role="tab">
                    <i class="bi bi-clock-history me-1 text-primary"></i> Audit Trail ({{ $auditTrail->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold small py-3 px-3 border-0 rounded-0" id="editlogs-tab" data-bs-toggle="tab" data-bs-target="#editLogsTabPane" type="button" role="tab">
                    <i class="bi bi-pencil-square me-1 text-danger"></i> Edit Logs ({{ $editLogs->count() }})
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-4">
        <div class="tab-content" id="leadMasterTabsContent">

            <!-- TAB 1: COMPLETE LEAD PARAMETERS (Organized in 4 Professional Sections) -->
            <div class="tab-pane fade show active" id="parametersTabPane" role="tabpanel">

                <div class="row g-4">
                    <!-- SECTION 1: Customer & Contact Demographics -->
                    <div class="col-lg-6">
                        <div class="border rounded-2 p-3 bg-white h-100">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <i class="bi bi-person-lines-fill text-primary"></i>
                                <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">1. Contact & Demographic Parameters</span>
                            </div>

                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.8125rem;">
                                <tbody>
                                    <tr>
                                        <td class="text-muted py-1" style="width: 40%;">Full Customer Name:</td>
                                        <td class="fw-bold text-dark py-1">{{ $lead->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Primary Phone Number:</td>
                                        <td class="fw-semibold text-dark py-1">
                                            {{ $lead->phone_country_code }} {{ $lead->phone }}
                                            <a href="tel:{{ $lead->phone }}" class="text-primary ms-1"><i class="bi bi-telephone-fill"></i></a>
                                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="text-success ms-1"><i class="bi bi-whatsapp"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Phone Country Code:</td>
                                        <td class="text-dark py-1 font-monospace">{{ $lead->phone_country_code ?? 'Not Set (+91 default)' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Alternate Phone Number:</td>
                                        <td class="text-dark py-1">{{ $lead->alt_phone ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Email Address:</td>
                                        <td class="text-dark py-1">
                                            @if($lead->email)
                                                <a href="mailto:{{ $lead->email }}" class="text-decoration-none">{{ $lead->email }}</a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">City:</td>
                                        <td class="text-dark py-1 fw-medium">{{ $lead->city ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Physical Location:</td>
                                        <td class="text-dark py-1">{{ $lead->physical_location ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Preferred Location:</td>
                                        <td class="text-dark py-1 fw-medium">{{ $lead->preferred_location ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Client IP Address:</td>
                                        <td class="text-dark py-1 font-monospace">{{ $lead->ip_address ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">IP Country Code:</td>
                                        <td class="text-dark py-1 font-monospace">{{ $lead->ip_country_code ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 2: Property & Enquiry Specs -->
                    <div class="col-lg-6">
                        <div class="border rounded-2 p-3 bg-white h-100">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <i class="bi bi-buildings text-primary"></i>
                                <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">2. Property & Enquiry Parameters</span>
                            </div>

                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.8125rem;">
                                <tbody>
                                    <tr>
                                        <td class="text-muted py-1" style="width: 40%;">Enquiry Reference / Name:</td>
                                        <td class="fw-bold text-dark py-1">{{ $lead->enquiry_name ?? ($lead->project_name ?? 'General Enquiry') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Tagged Master Project:</td>
                                        <td class="fw-semibold text-primary py-1">
                                            {{ $lead->project?->name ?? ($lead->project_name ?? 'None') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Sub-Project / Tower / Unit:</td>
                                        <td class="text-dark py-1">{{ $lead->sub_project_name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Property Type:</td>
                                        <td class="text-dark py-1">
                                            @if($lead->property_type)
                                                <span class="badge bg-light text-dark border">{{ $lead->property_type }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Configuration Required:</td>
                                        <td class="text-dark py-1">
                                            @if($lead->configuration)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $lead->configuration }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Budget Range (Min - Max):</td>
                                        <td class="fw-bold text-dark py-1">
                                            @if($lead->budget_min || $lead->budget_max)
                                                ₹ {{ $lead->budget_min ? number_format($lead->budget_min, 1) . 'L' : '0' }} - ₹ {{ $lead->budget_max ? number_format($lead->budget_max, 1) . 'L' : 'Open' }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Sourcing / Base Price:</td>
                                        <td class="text-dark py-1">
                                            {{ $lead->sourcing_price ? '₹ ' . number_format($lead->sourcing_price, 2) . ' L' : '—' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Client Requirement Notes:</td>
                                        <td class="text-dark py-1">{{ $lead->description ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1">Internal Sales Remarks:</td>
                                        <td class="text-dark py-1">{{ $lead->notes ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 3: Digital Marketing & Ad Attribution Tracking -->
                    <div class="col-12">
                        <div class="border rounded-2 p-3 bg-white">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-graph-up-arrow text-primary"></i>
                                    <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">3. Digital Marketing & Ad Attribution Parameters</span>
                                </div>
                                <span class="badge bg-light text-muted border small">
                                    <i class="bi bi-bullseye me-1"></i> Multi-Touch Attribution Engine
                                </span>
                            </div>

                            <div class="row g-3" style="font-size: 0.8125rem;">
                                <!-- Column 1: Source & Platform -->
                                <div class="col-md-4">
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Ad Platform:</span>
                                        <strong class="text-dark">{{ $lead->platform ?? '—' }}</strong>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Lead Source Category:</span>
                                        <span class="text-dark fw-medium">{{ $lead->leadSource?->name ?? 'None' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Source / Channel Name:</span>
                                        <span class="text-dark fw-medium">{{ $lead->source ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Source Key:</span>
                                        <span class="font-monospace text-dark">{{ $lead->source_key ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Sub-Source:</span>
                                        <span class="text-dark">{{ $lead->sub_source ?? '—' }}</span>
                                    </div>
                                </div>

                                <!-- Column 2: Campaign & Ad Group Structure -->
                                <div class="col-md-4">
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Campaign Name:</span>
                                        <strong class="text-dark">{{ $lead->campaign_name ?? '—' }}</strong>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Campaign ID:</span>
                                        <span class="font-monospace text-dark">{{ $lead->campaign_id ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Ad Set / Ad Group Name:</span>
                                        <span class="text-dark">{{ $lead->ads_set_name ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Ad Creative Name:</span>
                                        <span class="text-dark">{{ $lead->ads_name ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Ad ID:</span>
                                        <span class="font-monospace text-dark">{{ $lead->ads_id ?? '—' }}</span>
                                    </div>
                                </div>

                                <!-- Column 3: Keyword, Device & UTM -->
                                <div class="col-md-4">
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Search Keyword:</span>
                                        <span class="text-dark fw-medium">{{ $lead->ads_keyword ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Device / OS:</span>
                                        <span class="badge bg-light text-dark border">{{ $lead->device ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">UTM Source:</span>
                                        <span class="font-monospace text-dark">{{ $lead->utm_source ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">UTM Medium:</span>
                                        <span class="font-monospace text-dark">{{ $lead->utm_medium ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">UTM Campaign:</span>
                                        <span class="font-monospace text-dark">{{ $lead->utm_campaign ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">UTM Term:</span>
                                        <span class="font-monospace text-dark">{{ $lead->utm_term ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">UTM Content:</span>
                                        <span class="font-monospace text-dark">{{ $lead->utm_content ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border mb-2">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Google Click ID (GCLID):</span>
                                        <span class="font-monospace text-dark text-truncate d-block" style="font-size: 0.72rem;">{{ $lead->gclick_id ?? '—' }}</span>
                                    </div>
                                    <div class="p-2 bg-light rounded border">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Landing Page URL:</span>
                                        @if($lead->url)
                                            <a href="{{ $lead->url }}" target="_blank" class="text-truncate d-block small" style="font-size: 0.72rem;">{{ $lead->url }}</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: CRM Operations & Operational Lifecycle -->
                    <div class="col-12">
                        <div class="border rounded-2 p-3 bg-white">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <i class="bi bi-shield-check text-primary"></i>
                                <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">4. CRM Status, Assignment & Lifecycle</span>
                            </div>

                            <div class="row g-3" style="font-size: 0.8125rem;">
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-2 bg-light rounded border">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Current Pipeline Stage:</span>
                                        <span class="status-pill mt-1" style="background-color: {{ $lead->status?->color }}12; color: {{ $lead->status?->color }}; border-color: {{ $lead->status?->color }}30;">
                                            <span class="status-dot" style="background-color: {{ $lead->status?->color }};"></span>
                                            {{ $lead->status?->name ?? 'New' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-2 bg-light rounded border">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Assigned Sales Executive:</span>
                                        <strong class="text-dark">{{ $lead->assignedAgent?->name ?? 'Unassigned' }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-2 bg-light rounded border">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Lead Ingestion Origin:</span>
                                        <strong class="text-dark">{{ $lead->creator?->name ?? 'Public Webhook / Automated' }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-2 bg-light rounded border">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem;">Next Follow-up Reminder:</span>
                                        <strong class="text-danger">
                                            {{ $lead->next_follow_up_date ? $lead->next_follow_up_date->format('d M Y, h:i A') : 'None Scheduled' }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- TAB 2: FOLLOW-UP CALLS & INTERACTION TIMELINE -->
            <div class="tab-pane fade" id="timelineTabPane" role="tabpanel">
                <div class="row g-4">
                    <!-- Form to Log Follow-up -->
                    <div class="col-lg-5">
                        <div class="card border mb-3">
                            <div class="card-header py-2 px-3">
                                <h6 class="fw-semibold text-dark mb-0 small">Log New Follow-up Conversation</h6>
                            </div>
                            <div class="card-body p-3">
                                <form action="{{ route('leads.add-followup', $lead->id) }}" method="POST">
                                    @csrf
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Medium</label>
                                            <select name="follow_up_type" class="form-select form-select-sm">
                                                <option value="call">Phone Call</option>
                                                <option value="whatsapp">WhatsApp Message</option>
                                                <option value="meeting">In-Person Meeting</option>
                                                <option value="email">Email</option>
                                            </select>
                                        </div>

                                        <div class="col-6">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Response</label>
                                            <select name="call_response" class="form-select form-select-sm">
                                                <option value="Interested">Interested / Qualified</option>
                                                <option value="Call Back Later">Call Back Later / Busy</option>
                                                <option value="Site Visit Requested">Site Visit Requested</option>
                                                <option value="Budget Mismatch">Budget Mismatch</option>
                                                <option value="Not Reachable / Switch Off">Not Reachable</option>
                                                <option value="Wrong Number">Wrong Number</option>
                                                <option value="Already Purchased">Already Purchased</option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Next Follow-up Date & Time</label>
                                            <input type="datetime-local" name="next_follow_up_date" class="form-control form-control-sm">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Advance Pipeline Status</label>
                                            <select name="new_status_id" class="form-select form-select-sm">
                                                <option value="">Keep Current ({{ $lead->status?->name }})</option>
                                                @foreach($statuses as $st)
                                                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Conversation Notes <span class="text-danger">*</span></label>
                                            <textarea name="notes" rows="3" class="form-control form-control-sm" placeholder="Summarize client discussion..." required></textarea>
                                        </div>

                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bi bi-check-lg me-1"></i> Save Follow-up
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Records -->
                    <div class="col-lg-7">
                        <div class="card border">
                            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                                <h6 class="fw-semibold text-dark mb-0 small">Interaction Timeline</h6>
                                <span class="badge bg-light text-muted border">{{ $lead->followups->count() }} Entries</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="timeline position-relative ps-4" style="border-left: 2px solid #e2e8f0;">
                                    @forelse($lead->followups as $f)
                                        <div class="mb-3 position-relative">
                                            <div class="position-absolute rounded-circle bg-white border border-2 border-primary text-primary d-flex align-items-center justify-content-center"
                                                 style="left: -29px; top: 0; width: 22px; height: 22px; font-size: 0.7rem;">
                                                @if($f->follow_up_type === 'call')
                                                    <i class="bi bi-telephone-fill"></i>
                                                @elseif($f->follow_up_type === 'whatsapp')
                                                    <i class="bi bi-whatsapp"></i>
                                                @else
                                                    <i class="bi bi-chat-left-text"></i>
                                                @endif
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div>
                                                    <span class="fw-semibold text-dark small">{{ ucfirst($f->follow_up_type) }} Logged</span>
                                                    @if($f->call_response)
                                                        <span class="badge bg-light text-secondary border small ms-1">{{ $f->call_response }}</span>
                                                    @endif
                                                </div>
                                                <span class="text-muted" style="font-size: 0.72rem;">{{ $f->created_at->diffForHumans() }}</span>
                                            </div>

                                            <p class="text-dark small mb-1 bg-light p-2 rounded border" style="font-size: 0.8125rem;">{{ $f->notes }}</p>

                                            <div class="text-muted" style="font-size: 0.72rem;">
                                                By: <strong class="text-dark">{{ $f->user?->name ?? 'Sales Executive' }}</strong>
                                                @if($f->next_follow_up_date)
                                                    · Next: <span class="text-danger fw-semibold">{{ $f->next_follow_up_date->format('d M, h:i A') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted text-center py-4 small">
                                            No follow-up calls logged yet. Use the form to record your first conversation.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: SCHEDULED SITE VISITS -->
            <div class="tab-pane fade" id="visitsTabPane" role="tabpanel">
                <div class="row g-4">
                    <!-- Form to Schedule Site Visit -->
                    <div class="col-lg-5">
                        <div class="card border mb-3">
                            <div class="card-header py-2 px-3">
                                <h6 class="fw-semibold text-dark mb-0 small">Schedule Property Site Visit</h6>
                            </div>
                            <div class="card-body p-3">
                                <form action="{{ route('leads.schedule-visit', $lead->id) }}" method="POST">
                                    @csrf
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Select Project <span class="text-danger">*</span></label>
                                            <select name="project_id" class="form-select form-select-sm" required>
                                                <option value="">Choose Project</option>
                                                @foreach($projects as $proj)
                                                    <option value="{{ $proj->id }}" {{ $lead->project_id == $proj->id ? 'selected' : '' }}>
                                                        {{ $proj->name }} ({{ $proj->location }}, {{ $proj->city }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Visit Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" name="visit_date" class="form-control form-control-sm" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Escorting Executive</label>
                                            <select name="assigned_to" class="form-select form-select-sm">
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent->id }}" {{ ($lead->assigned_to ?? Auth::id()) == $agent->id ? 'selected' : '' }}>
                                                        {{ $agent->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox" name="cab_required" id="cabReq" value="1">
                                                <label class="form-check-label small" for="cabReq">
                                                    Cab Facility Required for Customer Pick & Drop
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-12 text-end mt-2">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bi bi-calendar2-plus me-1"></i> Schedule Visit
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduled Visits List -->
                    <div class="col-lg-7">
                        <div class="card border">
                            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                                <h6 class="fw-semibold text-dark mb-0 small">Scheduled Property Visits</h6>
                                <span class="badge bg-light text-muted border">{{ $lead->siteVisits->count() }} Visits</span>
                            </div>
                            <div class="card-body p-3">
                                @forelse($lead->siteVisits as $visit)
                                    <div class="border rounded p-3 mb-2 bg-light">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="fw-semibold text-dark mb-0 small">{{ $visit->project?->name ?? 'Property Visit' }}</h6>
                                            <span class="badge bg-primary-subtle text-primary">{{ ucfirst($visit->status) }}</span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.78rem;">
                                            <i class="bi bi-clock me-1 text-primary"></i> 
                                            Scheduled for: <strong>{{ $visit->visit_date->format('d M Y, h:i A') }}</strong>
                                        </div>
                                        @if($visit->assignedAgent)
                                            <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                                <i class="bi bi-person me-1"></i> Escorted by: {{ $visit->assignedAgent->name }}
                                            </div>
                                        @endif
                                        @if($visit->cab_required)
                                            <div class="text-info mt-1" style="font-size: 0.75rem;"><i class="bi bi-car-front me-1"></i> Cab Assistance Requested</div>
                                        @endif
                                        @if($visit->client_feedback)
                                            <p class="text-dark mt-2 mb-0 border-top pt-2" style="font-size: 0.78rem;">
                                                <em>Feedback: "{{ $visit->client_feedback }}"</em>
                                            </p>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                            @if($visit->rating)
                                                <div class="small text-warning" style="font-size: 0.75rem;">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="bi bi-star{{ $i <= $visit->rating ? '-fill' : '' }}"></i>
                                                    @endfor
                                                    <span class="text-dark fw-semibold ms-1">({{ $visit->rating }}/5)</span>
                                                </div>
                                            @else
                                                <span class="text-muted small" style="font-size: 0.72rem;">Sentiment not rated yet</span>
                                            @endif

                                            @if($canEdit)
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.72rem;" data-bs-toggle="modal" data-bs-target="#visitOutcomeModal{{ $visit->id }}">
                                                    <i class="bi bi-clipboard2-check me-1"></i>
                                                    {{ $visit->status === 'completed' ? 'Update Feedback' : 'Log Outcome & Feedback' }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted small">No property visits scheduled.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: AUDIT TRAIL (System Events & Full Lifecycle History) -->
            <div class="tab-pane fade" id="auditTabPane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bi bi-clock-history text-primary me-1"></i> System Audit Trail
                        </h6>
                        <p class="text-muted small mb-0">Chronological history of lead ingestion, status stage shifts, calls logged, and visits scheduled.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-secondary border">
                            <i class="bi bi-shield-check text-success me-1"></i> System Trail Active
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">
                            {{ $auditTrail->count() }} System Event(s)
                        </span>
                    </div>
                </div>

                @forelse($auditTrail as $act)
                    <div class="card border mb-3 shadow-none">
                        <div class="card-header py-2 px-3 bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @if($act->activity_type === 'status_changed')
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle fw-semibold">
                                        <i class="bi bi-arrow-repeat me-1"></i> Pipeline Stage Shift
                                    </span>
                                @elseif($act->activity_type === 'created')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold">
                                        <i class="bi bi-plus-circle me-1"></i> Lead Created / Ingested
                                    </span>
                                @elseif($act->activity_type === 'followup_added')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">
                                        <i class="bi bi-telephone-outbound me-1"></i> Follow-up Logged
                                    </span>
                                @elseif($act->activity_type === 'site_visit_scheduled')
                                    <span class="badge bg-secondary-subtle text-dark border fw-semibold">
                                        <i class="bi bi-calendar2-check me-1 text-primary"></i> Site Visit Scheduled
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border fw-semibold">
                                        <i class="bi bi-activity me-1"></i> {{ ucfirst(str_replace('_', ' ', $act->activity_type)) }}
                                    </span>
                                @endif

                                <span class="text-dark fw-semibold small">
                                    {{ $act->properties['editor_name'] ?? ($act->user?->name ?? 'System User') }}
                                </span>

                                @php
                                    $role = $act->properties['editor_role'] ?? ($act->user?->role ?? 'system');
                                @endphp
                                @if($role === 'admin')
                                    <span class="badge bg-danger text-white" style="font-size: 0.65rem;">Admin</span>
                                @elseif($role === 'manager')
                                    <span class="badge bg-primary text-white" style="font-size: 0.65rem;">Manager</span>
                                @elseif($role === 'sales_agent')
                                    <span class="badge bg-success text-white" style="font-size: 0.65rem;">Sales Agent</span>
                                @elseif($role === 'telecaller')
                                    <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Telecaller</span>
                                @else
                                    <span class="badge bg-secondary text-white" style="font-size: 0.65rem;">{{ ucfirst($role) }}</span>
                                @endif

                                @if(!empty($act->properties['ip_address']))
                                    <span class="text-muted small font-monospace d-none d-md-inline" style="font-size: 0.72rem;">
                                        <i class="bi bi-globe me-1"></i> IP: {{ $act->properties['ip_address'] }}
                                    </span>
                                @endif
                            </div>

                            <div class="text-muted small" style="font-size: 0.75rem;">
                                <i class="bi bi-clock me-1"></i> {{ $act->created_at->format('d M Y, h:i A') }}
                                <span class="text-secondary">({{ $act->created_at->diffForHumans() }})</span>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <div class="text-secondary small fw-medium">
                                {{ $act->description }}
                            </div>

                            @if(!empty($act->properties['changes']) && is_array($act->properties['changes']))
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.78rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 30%;">Transition Detail</th>
                                                <th style="width: 35%;" class="text-danger">From (Pehle)</th>
                                                <th style="width: 35%;" class="text-success">To (Abhi)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($act->properties['changes'] as $change)
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $change['label'] ?? $change['field'] }}</td>
                                                    <td class="text-danger"><del>{{ $change['old'] }}</del></td>
                                                    <td class="text-success fw-semibold">{{ $change['new'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted bg-white border rounded">
                        <i class="bi bi-clock-history fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        <p class="small mb-0">No system audit records found for this lead.</p>
                    </div>
                @endforelse
            </div>

            <!-- TAB 5: EDIT LOGS (Lead Parameter Modifications & Field Diffs) -->
            <div class="tab-pane fade" id="editLogsTabPane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bi bi-pencil-square text-danger me-1"></i> Parameter Edit Logs
                        </h6>
                        <p class="text-muted small mb-0">Detailed field-by-field change log showing who modified lead data, previous values, and new values.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-secondary border">
                            <i class="bi bi-shield-lock text-danger me-1"></i> Audit Verification Active
                        </span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold">
                            {{ $editLogs->count() }} Edit Session(s)
                        </span>
                    </div>
                </div>

                @forelse($editLogs as $act)
                    <div class="card border mb-3 shadow-none">
                        <div class="card-header py-2 px-3 bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-danger text-white fw-semibold" style="font-size: 0.72rem;">
                                    <i class="bi bi-pencil me-1"></i> Parameters Modified
                                </span>
                                <span class="text-dark fw-semibold small">
                                    {{ $act->properties['editor_name'] ?? ($act->user?->name ?? 'User') }}
                                </span>
                                @php
                                    $role = $act->properties['editor_role'] ?? ($act->user?->role ?? 'user');
                                @endphp
                                @if($role === 'admin')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.65rem;">Admin</span>
                                @elseif($role === 'manager')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">Manager</span>
                                @elseif($role === 'sales_agent')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">Sales Agent</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.65rem;">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                                @endif

                                @if(!empty($act->properties['ip_address']))
                                    <span class="text-muted small font-monospace d-none d-md-inline" style="font-size: 0.72rem;">
                                        <i class="bi bi-globe me-1"></i> IP: {{ $act->properties['ip_address'] }}
                                    </span>
                                @endif
                            </div>

                            <div class="text-muted small" style="font-size: 0.75rem;">
                                <i class="bi bi-clock me-1"></i> {{ $act->created_at->format('d M Y, h:i A') }}
                                <span class="text-secondary">({{ $act->created_at->diffForHumans() }})</span>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <div class="text-secondary small mb-2 fw-medium">
                                {{ $act->description }}
                            </div>

                            @if(!empty($act->properties['changes']) && is_array($act->properties['changes']))
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.78rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 28%;">Modified Parameter (Field)</th>
                                                <th style="width: 36%;" class="text-danger">
                                                    <i class="bi bi-dash-circle me-1"></i> Original Value (Pehle)
                                                </th>
                                                <th style="width: 36%;" class="text-success">
                                                    <i class="bi bi-check-circle me-1"></i> Updated Value (Abhi)
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($act->properties['changes'] as $change)
                                                <tr>
                                                    <td class="fw-semibold text-dark">
                                                        {{ $change['label'] ?? ucwords(str_replace('_', ' ', $change['field'])) }}
                                                    </td>
                                                    <td class="bg-danger-subtle bg-opacity-25 text-danger font-monospace">
                                                        <del>{{ $change['old'] !== '' ? $change['old'] : '— (Empty)' }}</del>
                                                    </td>
                                                    <td class="bg-success-subtle bg-opacity-25 text-success font-monospace fw-semibold">
                                                        {{ $change['new'] !== '' ? $change['new'] : '— (Empty)' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted bg-white border rounded">
                        <i class="bi bi-pencil-square fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        <h6 class="text-dark fw-semibold mb-1">No Parameter Edits Recorded</h6>
                        <p class="small text-muted mb-0">Whenever an agent or administrator modifies any lead parameters, the detailed field diff (old vs new value) will appear here.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</div>

<!-- Complete Edit Lead Modal (Supports editing all parameters) -->
<div class="modal fade" id="editLeadModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-semibold text-dark mb-0">Edit Complete Lead Parameters - {{ $lead->name }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('leads.update', $lead->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Group 1: Contact Details -->
                        <div class="col-12">
                            <span class="text-muted fw-bold small text-uppercase d-block border-bottom pb-1" style="font-size: 0.6875rem;">
                                1. Contact & Demographic Parameters
                            </span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Customer Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $lead->name }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-secondary mb-1">Country Code</label>
                            <input type="text" name="phone_country_code" class="form-control form-control-sm" value="{{ $lead->phone_country_code }}" placeholder="+91">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Primary Phone <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control form-control-sm" value="{{ $lead->phone }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Alternate Phone</label>
                            <input type="tel" name="alt_phone" class="form-control form-control-sm" value="{{ $lead->alt_phone }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-sm" value="{{ $lead->email }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">City</label>
                            <input type="text" name="city" class="form-control form-control-sm" value="{{ $lead->city }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Physical Location</label>
                            <input type="text" name="physical_location" class="form-control form-control-sm" value="{{ $lead->physical_location }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Preferred Location</label>
                            <input type="text" name="preferred_location" class="form-control form-control-sm" value="{{ $lead->preferred_location }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">IP Address</label>
                            <input type="text" name="ip_address" class="form-control form-control-sm" value="{{ $lead->ip_address }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">IP Country Code</label>
                            <input type="text" name="ip_country_code" class="form-control form-control-sm" value="{{ $lead->ip_country_code }}">
                        </div>

                        <!-- Group 2: Property & Enquiry Specs -->
                        <div class="col-12 mt-3">
                            <span class="text-muted fw-bold small text-uppercase d-block border-bottom pb-1" style="font-size: 0.6875rem;">
                                2. Property & Enquiry Parameters
                            </span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Enquiry Title / Reference</label>
                            <input type="text" name="enquiry_name" class="form-control form-control-sm" value="{{ $lead->enquiry_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Sub-Project / Tower / Unit</label>
                            <input type="text" name="sub_project_name" class="form-control form-control-sm" value="{{ $lead->sub_project_name }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Property Type</label>
                            <select name="property_type" class="form-select form-select-sm">
                                <option value="">Select Type</option>
                                <option value="Apartment" {{ $lead->property_type == 'Apartment' ? 'selected' : '' }}>Apartment</option>
                                <option value="Villa" {{ $lead->property_type == 'Villa' ? 'selected' : '' }}>Villa</option>
                                <option value="Plot" {{ $lead->property_type == 'Plot' ? 'selected' : '' }}>Plot</option>
                                <option value="Commercial" {{ $lead->property_type == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Configuration</label>
                            <select name="configuration" class="form-select form-select-sm">
                                <option value="">Select Configuration</option>
                                <option value="1 BHK" {{ $lead->configuration == '1 BHK' ? 'selected' : '' }}>1 BHK</option>
                                <option value="2 BHK" {{ $lead->configuration == '2 BHK' ? 'selected' : '' }}>2 BHK</option>
                                <option value="3 BHK" {{ $lead->configuration == '3 BHK' ? 'selected' : '' }}>3 BHK</option>
                                <option value="4+ BHK" {{ $lead->configuration == '4+ BHK' ? 'selected' : '' }}>4+ BHK</option>
                                <option value="Penthouse" {{ $lead->configuration == 'Penthouse' ? 'selected' : '' }}>Penthouse</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Tagged Project</label>
                            <select name="project_id" class="form-select form-select-sm">
                                <option value="">None / Open Requirement</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ $lead->project_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Min Budget (₹ in Lakhs)</label>
                            <input type="number" step="0.01" name="budget_min" class="form-control form-control-sm" value="{{ $lead->budget_min }}" placeholder="e.g. 120">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Max Budget (₹ in Lakhs)</label>
                            <input type="number" step="0.01" name="budget_max" class="form-control form-control-sm" value="{{ $lead->budget_max }}" placeholder="e.g. 180">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Sourcing Price (₹ in Lakhs)</label>
                            <input type="number" step="0.01" name="sourcing_price" class="form-control form-control-sm" value="{{ $lead->sourcing_price }}" placeholder="e.g. 115">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Client Requirement Description</label>
                            <textarea name="description" rows="2" class="form-control form-control-sm">{{ $lead->description }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Internal Notes</label>
                            <textarea name="notes" rows="2" class="form-control form-control-sm">{{ $lead->notes }}</textarea>
                        </div>

                        <!-- Feature 5: Smart Tags -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                <i class="bi bi-tags text-primary me-1"></i> Smart Tags & Custom Labels (Comma-separated)
                            </label>
                            <input type="text" name="tags" class="form-control form-control-sm" value="{{ !empty($lead->tags) && is_array($lead->tags) ? implode(', ', $lead->tags) : '' }}" placeholder="e.g. NRI, Investor, Immediate-Possession, High Networth">
                            <span class="text-muted" style="font-size: 0.7rem;">Tags allow rapid segment filtering and classification across campaigns and buyer personas.</span>
                        </div>

                        <!-- Group 3: Marketing & Ad Attribution Tracking -->
                        <div class="col-12 mt-3">
                            <span class="text-muted fw-bold small text-uppercase d-block border-bottom pb-1" style="font-size: 0.6875rem;">
                                3. Digital Marketing & Ad Attribution Parameters
                            </span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Ad Platform</label>
                            <input type="text" name="platform" class="form-control form-control-sm" value="{{ $lead->platform }}" placeholder="google_ads, meta_ads">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Lead Source Category</label>
                            <select name="source_id" class="form-select form-select-sm">
                                <option value="">Select Category</option>
                                @foreach($sources as $src)
                                    <option value="{{ $src->id }}" {{ $lead->source_id == $src->id ? 'selected' : '' }}>{{ $src->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Source / Channel Name</label>
                            <input type="text" name="source" class="form-control form-control-sm" value="{{ $lead->source }}" placeholder="Landing Page, FB Ad">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Source Key</label>
                            <input type="text" name="source_key" class="form-control form-control-sm" value="{{ $lead->source_key }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Sub-Source</label>
                            <input type="text" name="sub_source" class="form-control form-control-sm" value="{{ $lead->sub_source }}" placeholder="sem, reels">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Campaign Name</label>
                            <input type="text" name="campaign_name" class="form-control form-control-sm" value="{{ $lead->campaign_name }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Campaign ID</label>
                            <input type="text" name="campaign_id" class="form-control form-control-sm" value="{{ $lead->campaign_id }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Ad Set / Group Name</label>
                            <input type="text" name="ads_set_name" class="form-control form-control-sm" value="{{ $lead->ads_set_name }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Ad Creative Name</label>
                            <input type="text" name="ads_name" class="form-control form-control-sm" value="{{ $lead->ads_name }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Ad ID</label>
                            <input type="text" name="ads_id" class="form-control form-control-sm" value="{{ $lead->ads_id }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Search Keyword</label>
                            <input type="text" name="ads_keyword" class="form-control form-control-sm" value="{{ $lead->ads_keyword }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Device</label>
                            <input type="text" name="device" class="form-control form-control-sm" value="{{ $lead->device }}" placeholder="mobile, desktop">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">UTM Source</label>
                            <input type="text" name="utm_source" class="form-control form-control-sm" value="{{ $lead->utm_source }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">UTM Medium</label>
                            <input type="text" name="utm_medium" class="form-control form-control-sm" value="{{ $lead->utm_medium }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">UTM Campaign</label>
                            <input type="text" name="utm_campaign" class="form-control form-control-sm" value="{{ $lead->utm_campaign }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Google Click ID (GCLID)</label>
                            <input type="text" name="gclick_id" class="form-control form-control-sm" value="{{ $lead->gclick_id }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Landing Page URL</label>
                            <input type="text" name="url" class="form-control form-control-sm" value="{{ $lead->url }}">
                        </div>

                        <!-- Group 4: Pipeline & Operational Assignment -->
                        <div class="col-12 mt-3">
                            <span class="text-muted fw-bold small text-uppercase d-block border-bottom pb-1" style="font-size: 0.6875rem;">
                                4. CRM Assignment & Status
                            </span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Pipeline Status</label>
                            <select name="status_id" class="form-select form-select-sm">
                                @foreach($statuses as $st)
                                    <option value="{{ $st->id }}" {{ $lead->status_id == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                Assigned Executive
                                @if(!$canReassign)
                                    <i class="bi bi-lock-fill text-muted ms-1" title="Only Admin/Manager can reassign"></i>
                                @endif
                            </label>
                            @if($canReassign)
                                <select name="assigned_to" class="form-select form-select-sm">
                                    <option value="">Unassigned</option>
                                    @foreach($agents as $a)
                                        <option value="{{ $a->id }}" {{ $lead->assigned_to == $a->id ? 'selected' : '' }}>{{ $a->name }} ({{ ucfirst(str_replace('_', ' ', $a->role)) }})</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control form-control-sm bg-light" value="{{ $lead->assignedAgent?->name ?? 'Unassigned' }}" readonly>
                                <small class="text-muted" style="font-size: 0.6875rem;">
                                    <i class="bi bi-info-circle me-1"></i> Reassignment requires Admin or Manager privilege.
                                </small>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Next Follow-up Date</label>
                            <input type="datetime-local" name="next_follow_up_date" class="form-control form-control-sm" value="{{ $lead->next_follow_up_date ? $lead->next_follow_up_date->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-center mt-3">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="checkbox" name="is_hot" id="editIsHot" value="1" {{ $lead->is_hot ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold text-danger" for="editIsHot">
                                    <i class="bi bi-fire"></i> Hot Priority Lead
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_organic" id="editIsOrganic" value="1" {{ $lead->is_organic ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold text-success" for="editIsOrganic">
                                    <i class="bi bi-check-circle"></i> Organic Lead
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save All Parameters</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- WhatsApp Quick Templates Modal (Feature 1) -->
<div class="modal fade" id="whatsappTemplatesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-semibold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-whatsapp text-success fs-5"></i>
                    <span>Quick WhatsApp Messages & Templates - {{ $lead->name }}</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary mb-2">Choose Pre-composed Real Estate Template:</label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="p-2.5 border rounded cursor-pointer template-card bg-light border-primary" onclick="selectWaTemplate(1)" style="cursor: pointer;">
                                <div class="fw-semibold text-dark small"><i class="bi bi-file-earmark-pdf text-danger me-1"></i> 1. Project Brochure & Overview</div>
                                <span class="text-muted" style="font-size: 0.72rem;">Share digital brochure, configuration, and project highlights.</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2.5 border rounded cursor-pointer template-card" onclick="selectWaTemplate(2)" style="cursor: pointer;">
                                <div class="fw-semibold text-dark small"><i class="bi bi-geo-alt-fill text-primary me-1"></i> 2. Site Visit Confirmation</div>
                                <span class="text-muted" style="font-size: 0.72rem;">Confirm visit timing, sales lounge location, and directions.</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2.5 border rounded cursor-pointer template-card" onclick="selectWaTemplate(3)" style="cursor: pointer;">
                                <div class="fw-semibold text-dark small"><i class="bi bi-cash-stack text-success me-1"></i> 3. Pricing & Payment Plan</div>
                                <span class="text-muted" style="font-size: 0.72rem;">Cost sheet, installment plan, and exclusive booking offers.</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2.5 border rounded cursor-pointer template-card" onclick="selectWaTemplate(4)" style="cursor: pointer;">
                                <div class="fw-semibold text-dark small"><i class="bi bi-chat-dots text-warning me-1"></i> 4. Friendly Follow-up</div>
                                <span class="text-muted" style="font-size: 0.72rem;">Check in on client's decision and inquire about next steps.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-semibold text-secondary mb-0">Message Preview & Customization:</label>
                        <span class="text-muted" style="font-size: 0.72rem;">You can edit or add notes before launching</span>
                    </div>
                    <textarea id="waMessageText" class="form-control form-control-sm font-monospace" rows="5" style="font-size: 0.8125rem;"></textarea>
                </div>

                <div class="p-2 bg-light rounded border d-flex justify-content-between align-items-center">
                    <span class="text-muted small" style="font-size: 0.75rem;">
                        <i class="bi bi-phone me-1"></i> Destination: <strong class="text-dark">{{ $lead->phone_country_code ?? '+91' }} {{ $lead->phone }}</strong>
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-check2-all me-1"></i> 1-Click Web Launch
                    </span>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-4">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm btn-success" onclick="launchWhatsApp()">
                    <i class="bi bi-whatsapp me-1"></i> Launch in WhatsApp Web
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modals to Log Visit Outcome & Feedback (Feature 2) -->
@foreach($lead->siteVisits as $visit)
    <div class="modal fade" id="visitOutcomeModal{{ $visit->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-3">
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-semibold text-dark mb-0">
                        <i class="bi bi-clipboard2-check text-primary me-2"></i> Log Visit Outcome - {{ $visit->project?->name ?? 'Property Visit' }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('leads.site-visit-outcome', ['id' => $lead->id, 'visitId' => $visit->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Visit Result / Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="completed" {{ $visit->status === 'completed' ? 'selected' : '' }}>✅ Visit Conducted / Completed</option>
                                <option value="rescheduled" {{ $visit->status === 'rescheduled' ? 'selected' : '' }}>🔄 Rescheduled for Later</option>
                                <option value="cancelled" {{ $visit->status === 'cancelled' ? 'selected' : '' }}>❌ Client Cancelled / No-Show</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Client Sentiment / Rating</label>
                            <select name="rating" class="form-select form-select-sm">
                                <option value="">Select Sentiment</option>
                                <option value="5" {{ $visit->rating == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ 5 - Highly Interested / Ready to Book</option>
                                <option value="4" {{ $visit->rating == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ 4 - Positive / Evaluating Floor Plans</option>
                                <option value="3" {{ $visit->rating == 3 ? 'selected' : '' }}>⭐⭐⭐ 3 - Neutral / Comparing with Competitors</option>
                                <option value="2" {{ $visit->rating == 2 ? 'selected' : '' }}>⭐⭐ 2 - Budget / Location Concern</option>
                                <option value="1" {{ $visit->rating == 1 ? 'selected' : '' }}>⭐ 1 - Not Interested / Rejected</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Advance Pipeline Stage (Optional)</label>
                            <select name="new_status_id" class="form-select form-select-sm">
                                <option value="">Keep Current Stage ({{ $lead->status?->name }})</option>
                                @foreach($statuses as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Client Feedback & Discussion Remarks <span class="text-danger">*</span></label>
                            <textarea name="client_feedback" class="form-control form-control-sm" rows="3" placeholder="Summarize client reaction, preferences, specific units liked, or objections..." required>{{ $visit->client_feedback }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2 px-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Visit Outcome</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Feature 1: Interactive Home Loan EMI & Financing Calculator Modal -->
<div class="modal fade" id="emiCalculatorModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-bottom py-3 px-4 bg-light">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5">💰</span>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0">Home Loan EMI & Financing Calculator</h6>
                        <span class="text-muted small" style="font-size: 0.72rem;">Calculate live monthly installments and share bank loan quote on WhatsApp</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Left: Sliders & Parameter Inputs -->
                    <div class="col-md-6 border-end">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1 d-flex justify-content-between">
                                <span>Property Purchase Price (₹)</span>
                                <span class="text-primary fw-bold" id="emiPropPriceText">₹ 85,00,000</span>
                            </label>
                            @php
                                $defaultPrice = 8500000;
                                if (!empty($lead->budget_max) && $lead->budget_max > 0) {
                                    $defaultPrice = $lead->budget_max > 1000 ? $lead->budget_max : $lead->budget_max * 100000;
                                } elseif (!empty($lead->budget_min) && $lead->budget_min > 0) {
                                    $defaultPrice = $lead->budget_min > 1000 ? $lead->budget_min : $lead->budget_min * 100000;
                                }
                            @endphp
                            <input type="number" id="emiPropPrice" class="form-control form-control-sm fw-semibold" value="{{ (int)$defaultPrice }}" step="50000" oninput="calculateEMI()">
                            <div class="d-flex gap-1 mt-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size: 0.68rem;" onclick="setPropPrice(5000000)">₹50L</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size: 0.68rem;" onclick="setPropPrice(7500000)">₹75L</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size: 0.68rem;" onclick="setPropPrice(10000000)">₹1Cr</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size: 0.68rem;" onclick="setPropPrice(15000000)">₹1.5Cr</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size: 0.68rem;" onclick="setPropPrice(20000000)">₹2Cr</button>
                            </div>
                        </div>

                        <!-- Down Payment Slider -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-secondary mb-0">Down Payment</label>
                                <span class="badge bg-light text-dark border fw-bold" id="emiDownPayBadge">20% (₹ 17,00,000)</span>
                            </div>
                            <input type="range" class="form-range" id="emiDownPayPct" min="10" max="50" step="5" value="20" oninput="calculateEMI()">
                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.68rem;">
                                <span>10% (Min)</span>
                                <span>25%</span>
                                <span>50% (Max)</span>
                            </div>
                        </div>

                        <!-- Loan Tenure Slider -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-secondary mb-0">Loan Tenure</label>
                                <span class="badge bg-light text-dark border fw-bold" id="emiTenureBadge">20 Years</span>
                            </div>
                            <input type="range" class="form-range" id="emiTenure" min="5" max="30" step="1" value="20" oninput="calculateEMI()">
                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.68rem;">
                                <span>5 Yrs</span>
                                <span>15 Yrs</span>
                                <span>30 Yrs</span>
                            </div>
                        </div>

                        <!-- Interest Rate Slider -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-secondary mb-0">Interest Rate (% p.a.)</label>
                                <span class="badge bg-primary text-white fw-bold" id="emiRateBadge">8.50%</span>
                            </div>
                            <input type="range" class="form-range" id="emiRate" min="6.5" max="14.0" step="0.1" value="8.5" oninput="calculateEMI()">
                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.68rem;">
                                <span>6.5%</span>
                                <span>8.5% (Market Avg)</span>
                                <span>14.0%</span>
                            </div>
                        </div>

                        <!-- Bank Quick Select -->
                        <div>
                            <span class="text-muted small d-block mb-1" style="font-size: 0.7rem;">Bank Tie-up Benchmark Rates:</span>
                            <div class="d-flex gap-1 flex-wrap">
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.68rem;" onclick="setBankRate(8.50, 'SBI Home Finance')">SBI: 8.50%</button>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.68rem;" onclick="setBankRate(8.60, 'HDFC Bank')">HDFC: 8.60%</button>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.68rem;" onclick="setBankRate(8.75, 'ICICI Bank')">ICICI: 8.75%</button>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.68rem;" onclick="setBankRate(8.85, 'Axis Bank')">Axis: 8.85%</button>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Visual Outcome & Schedule -->
                    <div class="col-md-6 d-flex flex-column justify-content-between">
                        <div>
                            <div class="bg-primary-subtle border border-primary-subtle rounded-3 p-3 text-center mb-3">
                                <span class="text-secondary small fw-semibold text-uppercase d-block" style="letter-spacing: 0.05em; font-size: 0.72rem;">Estimated Monthly Installment (EMI)</span>
                                <h3 class="fw-bold text-primary mb-0 my-1" id="emiMonthlyDisplay">₹ 59,008</h3>
                                <span class="text-muted small" style="font-size: 0.7rem;">per month for <span id="emiMonthsText">240</span> months</span>
                            </div>

                            <table class="table table-sm table-bordered align-middle mb-3" style="font-size: 0.78rem;">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" style="width: 50%;">Down Payment Required:</td>
                                        <td class="fw-bold text-dark text-end" id="emiDownPayVal">₹ 17,00,000</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Net Home Loan Principal:</td>
                                        <td class="fw-bold text-dark text-end" id="emiLoanPrincipalVal">₹ 68,00,000</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Total Interest Amount:</td>
                                        <td class="fw-bold text-danger text-end" id="emiTotalInterestVal">₹ 73,61,920</td>
                                    </tr>
                                    <tr class="table-light">
                                        <td class="fw-bold text-dark">Total Repayment (Principal + Interest):</td>
                                        <td class="fw-bold text-success text-end" id="emiTotalRepaymentVal">₹ 1,41,61,920</td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Visual Stacked Bar -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 0.7rem;">
                                    <span>Principal: <strong class="text-primary" id="emiPrincipalPctText">48%</strong></span>
                                    <span>Interest: <strong class="text-danger" id="emiInterestPctText">52%</strong></span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-primary" id="emiPrincipalBar" role="progressbar" style="width: 48%;"></div>
                                    <div class="progress-bar bg-danger" id="emiInterestBar" role="progressbar" style="width: 52%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- 1-Click WhatsApp Share -->
                        <div class="border-top pt-3">
                            <button type="button" class="btn btn-success w-100 fw-semibold d-flex align-items-center justify-content-center gap-2 py-2" onclick="shareEMIWhatsApp()">
                                <i class="bi bi-whatsapp fs-6"></i>
                                <span>Share EMI Breakdown with {{ $lead->name }}</span>
                            </button>
                            <span class="text-muted small d-block text-center mt-1" style="font-size: 0.68rem;">Sends clean, personalized EMI quotation directly to {{ $lead->phone }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-4">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Feature 6: Branded Real Estate Cost Sheet & Quotation Generator Modal -->
<div class="modal fade" id="costSheetModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-bottom py-3 px-4 bg-light d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5">📄</span>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0">Official Property Cost Sheet & Quotation</h6>
                        <span class="text-muted small" style="font-size: 0.72rem;">All-inclusive pricing breakdown with agreement value, stamp duty, GST & parking</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="printCostSheet()">
                        <i class="bi bi-printer me-1"></i> Print / PDF
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="shareCostSheetWhatsApp()">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <!-- Cost Sheet Printable Area -->
            <div class="modal-body p-4" id="printableCostSheet">
                <!-- Quotation Letterhead -->
                <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4 text-primary">🏢</span>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">EstatePulse Realty Group</h5>
                                <span class="text-muted small" style="font-size: 0.72rem;">Premier Real Estate Developers & Advisory | RERA Registered</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-light text-secondary border fw-semibold mb-1">OFFICIAL ESTIMATE</span>
                        <div class="text-muted small" style="font-size: 0.72rem;">Ref #: EP-QUOTE-{{ $lead->id }}-{{ date('Ymd') }}</div>
                        <div class="text-muted small" style="font-size: 0.72rem;">Date: {{ now()->format('d M Y') }}</div>
                    </div>
                </div>

                <!-- Customer & Property Header -->
                <div class="row g-2 mb-3 bg-light rounded p-2 border" style="font-size: 0.78rem;">
                    <div class="col-sm-6">
                        <span class="text-muted d-block" style="font-size: 0.7rem;">Client Name:</span>
                        <strong class="text-dark">{{ $lead->name }}</strong>
                        <div class="text-muted" style="font-size: 0.72rem;">Ph: {{ $lead->phone }} @if($lead->email) | {{ $lead->email }} @endif</div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <span class="text-muted d-block" style="font-size: 0.7rem;">Project & Unit:</span>
                        <strong class="text-primary">{{ $lead->project?->name ?? ($lead->enquiry_name ?? 'Premier Luxury Residences') }}</strong>
                        <div class="text-muted" style="font-size: 0.72rem;">Type: {{ $lead->configuration ?? '2 BHK' }} ({{ $lead->property_type ?? 'Apartment' }})</div>
                    </div>
                </div>

                @php
                    $leadBudget = 8500000;
                    if (!empty($lead->budget_max) && $lead->budget_max > 0) {
                        $leadBudget = $lead->budget_max > 1000 ? $lead->budget_max : $lead->budget_max * 100000;
                    } elseif (!empty($lead->budget_min) && $lead->budget_min > 0) {
                        $leadBudget = $lead->budget_min > 1000 ? $lead->budget_min : $lead->budget_min * 100000;
                    }
                    $basePrice = round($leadBudget * 0.85);
                    $floorRise = round($leadBudget * 0.03);
                    $carParking = 350000;
                    $clubHouse = 200000;
                    $agreementValue = $basePrice + $floorRise + $carParking + $clubHouse;

                    // Statutory & Govt taxes
                    $stampDuty = round($agreementValue * 0.05); // 5%
                    $registration = 30000;
                    $gst = round($agreementValue * 0.05); // 5%
                    $advanceMaint = 120000;
                    $grandTotal = $agreementValue + $stampDuty + $registration + $gst + $advanceMaint;
                @endphp

                <!-- Itemized Cost Breakdown Table -->
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.78rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 8%;" class="text-center">Sr.</th>
                                <th style="width: 57%;">Particulars / Component Description</th>
                                <th style="width: 35%;" class="text-end">Amount (INR ₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">1</td>
                                <td>
                                    <strong class="text-dark">Base Consideration Value</strong>
                                    <span class="text-muted d-block small" style="font-size: 0.7rem;">Carpet Area consideration rate based on master developer tier</span>
                                </td>
                                <td class="text-end fw-semibold">₹ {{ number_format($basePrice) }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">2</td>
                                <td>
                                    <strong>Floor Rise Charges (Middle to Higher Floors)</strong>
                                    <span class="text-muted d-block small" style="font-size: 0.7rem;">Premium per sqft for panoramic elevation & view</span>
                                </td>
                                <td class="text-end fw-semibold">₹ {{ number_format($floorRise) }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">3</td>
                                <td>
                                    <strong>Exclusive Covered Car Parking Space</strong>
                                    <span class="text-muted d-block small" style="font-size: 0.7rem;">Reserved stilt / basement car parking slot</span>
                                </td>
                                <td class="text-end fw-semibold">₹ {{ number_format($carParking) }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">4</td>
                                <td>
                                    <strong>Clubhouse & Infrastructure Development Charges</strong>
                                    <span class="text-muted d-block small" style="font-size: 0.7rem;">Gymnasium, swimming pool, power backup & DG infrastructure</span>
                                </td>
                                <td class="text-end fw-semibold">₹ {{ number_format($clubHouse) }}</td>
                            </tr>
                            <tr class="table-primary fw-bold">
                                <td class="text-center">A</td>
                                <td class="text-dark">SUB-TOTAL: AGREEMENT VALUE</td>
                                <td class="text-end text-primary" style="font-size: 0.85rem;">₹ {{ number_format($agreementValue) }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">5</td>
                                <td>
                                    <strong>Stamp Duty Charges (5% of Agreement Value)</strong>
                                    <span class="text-muted d-block small" style="font-size: 0.7rem;">Payable directly to State Revenue Dept at time of registration</span>
                                </td>
                                <td class="text-end fw-semibold text-danger">₹ {{ number_format($stampDuty) }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">6</td>
                                <td>
                                    <strong>Government Registration & Document Charges</strong>
                                    <span class="text-muted d-block small" style="font-size: 0.7rem;">Standard govt registration fee and e-filing scan dues</span>
                                </td>
                                <td class="text-end fw-semibold text-danger">₹ {{ number_format($registration) }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">7</td>
                                <td>
                                    <strong>Goods & Services Tax (GST @ 5%)</strong>
                                    <span class="text-muted d-block small" style="font-size: 0.7rem;">Applicable on under-construction real estate units without ITC</span>
                                </td>
                                <td class="text-end fw-semibold text-danger">₹ {{ number_format($gst) }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">8</td>
                                <td>
                                    <strong>Advance Maintenance & Society Sinking Deposit</strong>
                                    <span class="text-muted d-block small" style="font-size: 0.7rem;">12-month advance common area maintenance & water connection</span>
                                </td>
                                <td class="text-end fw-semibold">₹ {{ number_format($advanceMaint) }}</td>
                            </tr>
                            <tr class="table-success fw-bold">
                                <td class="text-center">★</td>
                                <td class="text-dark fs-6">ALL-INCLUSIVE ON-ROAD ESTIMATED TOTAL</td>
                                <td class="text-end text-success fs-6">₹ {{ number_format($grandTotal) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Construction-Linked Payment Milestones -->
                <div class="border rounded p-3 bg-light mb-3">
                    <span class="fw-bold text-dark small text-uppercase d-block mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                        Standard Construction Linked Payment Schedule (CLP)
                    </span>
                    <div class="row g-2 text-center" style="font-size: 0.72rem;">
                        <div class="col">
                            <div class="bg-white p-2 rounded border">
                                <span class="text-muted d-block">Booking Token</span>
                                <strong class="text-dark">10%</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-white p-2 rounded border">
                                <span class="text-muted d-block">On Agreement</span>
                                <strong class="text-dark">10%</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-white p-2 rounded border">
                                <span class="text-muted d-block">Plinth Completion</span>
                                <strong class="text-dark">15%</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-white p-2 rounded border">
                                <span class="text-muted d-block">Slab Stages</span>
                                <strong class="text-dark">35%</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-white p-2 rounded border">
                                <span class="text-muted d-block">Finishing & MEP</span>
                                <strong class="text-dark">20%</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-white p-2 rounded border">
                                <span class="text-muted d-block">On Handover</span>
                                <strong class="text-dark">10%</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Terms -->
                <div class="d-flex justify-content-between align-items-center text-muted small" style="font-size: 0.68rem;">
                    <span>* Prices are subject to revision without prior notice. Govt taxes are indicative and subject to prevailing norms.</span>
                    <span>Prepared by: <strong>{{ Auth::user()->name }}</strong> (Sales Executive)</span>
                </div>
            </div>

            <div class="modal-footer border-top py-2 px-4">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const waLeadData = {
    name: "{{ addslashes($lead->name) }}",
    phone: "{{ preg_replace('/[^0-9]/', '', $lead->phone) }}",
    project: "{{ addslashes($lead->project?->name ?? ($lead->enquiry_name ?? 'our premier luxury project')) }}",
    config: "{{ addslashes($lead->configuration ?? 'residence') }}",
    budget: "{{ $lead->budget_max ? '₹ ' . number_format($lead->budget_max, 1) . 'L' : '' }}",
    agent: "{{ addslashes(Auth::user()->name) }}",
    company: "EstatePulse Realty"
};

const waTemplates = {
    1: `Hello ${waLeadData.name}, thank you for your interest in ${waLeadData.project} (${waLeadData.config}). We are pleased to share the project brochure, floor plans, and amenities detail with you. Please let us know a convenient time to discuss your requirements.\n\nBest regards,\n${waLeadData.agent} | ${waLeadData.company}`,
    2: `Hello ${waLeadData.name}, your exclusive property site visit for ${waLeadData.project} has been confirmed. Our sales team will assist you at the project sales lounge. Please let us know if you require cab assistance or directions to the site.\n\nLooking forward to hosting you!\n${waLeadData.agent} | ${waLeadData.company}`,
    3: `Hello ${waLeadData.name}, following our discussion regarding ${waLeadData.project}, here is the customized cost sheet and flexible payment schedule for the ${waLeadData.config} unit. Feel free to reach out if you have any questions.\n\nBest regards,\n${waLeadData.agent}`,
    4: `Hello ${waLeadData.name}, hope you are doing well! Just following up regarding your property inquiry for ${waLeadData.project}. Were you able to review the project details we shared earlier? Let us know if you would like to schedule a site visit this week.\n\nWarm regards,\n${waLeadData.agent}`
};

function selectWaTemplate(id) {
    document.querySelectorAll('.template-card').forEach(c => c.classList.remove('bg-light', 'border-primary'));
    event.currentTarget.classList.add('bg-light', 'border-primary');
    document.getElementById('waMessageText').value = waTemplates[id];
}

function launchWhatsApp() {
    const text = document.getElementById('waMessageText').value;
    const phone = waLeadData.phone.length === 10 ? '91' + waLeadData.phone : waLeadData.phone;
    const url = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(text);
    window.open(url, '_blank');
    const modalEl = document.getElementById('whatsappTemplatesModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

document.addEventListener('DOMContentLoaded', function() {
    const waText = document.getElementById('waMessageText');
    if (waText && waTemplates[1]) {
        waText.value = waTemplates[1];
    }
    // Initialize EMI calculator
    calculateEMI();
});

// Feature 1: Live EMI Calculation Engine
function formatINR(val) {
    return '₹ ' + Math.round(val).toLocaleString('en-IN');
}

function calculateEMI() {
    const price = parseFloat(document.getElementById('emiPropPrice').value) || 0;
    const downPct = parseFloat(document.getElementById('emiDownPayPct').value) || 20;
    const tenureYrs = parseFloat(document.getElementById('emiTenure').value) || 20;
    const annualRate = parseFloat(document.getElementById('emiRate').value) || 8.5;

    // Update Label texts
    document.getElementById('emiPropPriceText').innerText = formatINR(price);
    const downAmt = (price * downPct) / 100;
    document.getElementById('emiDownPayBadge').innerText = `${downPct}% (${formatINR(downAmt)})`;
    document.getElementById('emiDownPayVal').innerText = formatINR(downAmt);

    const principal = Math.max(0, price - downAmt);
    document.getElementById('emiLoanPrincipalVal').innerText = formatINR(principal);
    document.getElementById('emiTenureBadge').innerText = `${tenureYrs} Years (${tenureYrs * 12} Months)`;
    document.getElementById('emiMonthsText').innerText = tenureYrs * 12;
    document.getElementById('emiRateBadge').innerText = `${annualRate.toFixed(2)}%`;

    if (principal <= 0) {
        document.getElementById('emiMonthlyDisplay').innerText = '₹ 0';
        document.getElementById('emiTotalInterestVal').innerText = '₹ 0';
        document.getElementById('emiTotalRepaymentVal').innerText = '₹ 0';
        return;
    }

    const monthlyRate = annualRate / (12 * 100);
    const totalMonths = tenureYrs * 12;

    // EMI = [P x R x (1+R)^N]/[(1+R)^N-1]
    const emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, totalMonths)) / (Math.pow(1 + monthlyRate, totalMonths) - 1);
    const totalRepay = emi * totalMonths;
    const totalInterest = Math.max(0, totalRepay - principal);

    document.getElementById('emiMonthlyDisplay').innerText = formatINR(emi) + ' / mo';
    document.getElementById('emiTotalInterestVal').innerText = formatINR(totalInterest);
    document.getElementById('emiTotalRepaymentVal').innerText = formatINR(totalRepay);

    // Visual Bar percentages
    const principalPct = Math.round((principal / totalRepay) * 100) || 50;
    const interestPct = Math.max(0, 100 - principalPct);
    document.getElementById('emiPrincipalPctText').innerText = `${principalPct}%`;
    document.getElementById('emiInterestPctText').innerText = `${interestPct}%`;
    document.getElementById('emiPrincipalBar').style.width = `${principalPct}%`;
    document.getElementById('emiInterestBar').style.width = `${interestPct}%`;
}

function setPropPrice(val) {
    document.getElementById('emiPropPrice').value = val;
    calculateEMI();
}

function setBankRate(rate, bankName) {
    document.getElementById('emiRate').value = rate;
    calculateEMI();
}

function shareEMIWhatsApp() {
    const price = document.getElementById('emiPropPriceText').innerText;
    const downPay = document.getElementById('emiDownPayVal').innerText;
    const loan = document.getElementById('emiLoanPrincipalVal').innerText;
    const tenure = document.getElementById('emiTenureBadge').innerText;
    const rate = document.getElementById('emiRateBadge').innerText;
    const emi = document.getElementById('emiMonthlyDisplay').innerText;
    const interest = document.getElementById('emiTotalInterestVal').innerText;
    const total = document.getElementById('emiTotalRepaymentVal').innerText;

    const msg = `*EstatePulse Realty - Home Loan Financing Estimate*\n\n` +
        `Dear *${waLeadData.name}*,\n` +
        `Here is the estimated Home Loan EMI Breakdown for *${waLeadData.project}* (${waLeadData.config}):\n\n` +
        `🏡 *Property Value:* ${price}\n` +
        `💵 *Down Payment:* ${downPay}\n` +
        `🏦 *Bank Loan Principal:* ${loan}\n` +
        `📅 *Tenure:* ${tenure} @ ${rate} p.a.\n\n` +
        `✨ *Estimated Monthly EMI: ${emi}*\n\n` +
        `📊 *Total Interest:* ${interest}\n` +
        `💰 *Total Repayment:* ${total}\n\n` +
        `We have instant sanction banking tie-ups with SBI, HDFC & ICICI Bank. Please let us know if you'd like our home loan specialist to assist with pre-approval.\n\n` +
        `Best regards,\n${waLeadData.agent} | EstatePulse Realty`;

    const phone = waLeadData.phone.length === 10 ? '91' + waLeadData.phone : waLeadData.phone;
    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`, '_blank');
}

// Feature 6: Branded Cost Sheet Actions
function printCostSheet() {
    const printContent = document.getElementById('printableCostSheet').innerHTML;
    const printWindow = window.open('', '', 'height=850,width=920');
    printWindow.document.write('<html><head><title>Cost Sheet - ' + waLeadData.name + '</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('<style>body { font-family: Inter, sans-serif; padding: 25px; color: #1e293b; } .table { font-size: 12px; } @page { margin: 12mm; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 450);
}

function shareCostSheetWhatsApp() {
    const agreement = "{{ number_format($agreementValue) }}";
    const stamp = "{{ number_format($stampDuty) }}";
    const gst = "{{ number_format($gst) }}";
    const grand = "{{ number_format($grandTotal) }}";

    const msg = `*EstatePulse Realty - Official Property Cost Sheet*\n\n` +
        `Dear *${waLeadData.name}*,\n` +
        `Greetings! Here is the itemized cost quotation for *${waLeadData.project}* (${waLeadData.config}):\n\n` +
        `📑 *Agreement Value:* ₹ ${agreement}\n` +
        `🏛️ *Stamp Duty (5%) + Govt Reg:* ₹ ${stamp} + ₹ 30,000\n` +
        `⚖️ *GST (5%):* ₹ ${gst}\n` +
        `-----------------------------------------\n` +
        `💎 *All-Inclusive On-Road Total: ₹ ${grand}*\n` +
        `-----------------------------------------\n\n` +
        `Includes reserved covered car parking, club house privileges, and infrastructure fees.\n` +
        `Flexible construction-linked payment plan (CLP) available.\n\n` +
        `Best regards,\n${waLeadData.agent} | EstatePulse Realty`;

    const phone = waLeadData.phone.length === 10 ? '91' + waLeadData.phone : waLeadData.phone;
    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`, '_blank');
}
</script>

@endsection
