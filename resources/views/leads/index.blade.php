@extends('layouts.app')

@section('title', 'Leads Management')
@section('page-title', 'Lead Directory & Pipeline')

@section('content')

@if(isset($isAgent) && $isAgent)
    <div class="alert alert-info py-2 px-3 small mb-3 d-flex align-items-center justify-content-between rounded-2 border-0" style="background-color: #f0f9ff; border-left: 3px solid #0284c7 !important;">
        <div>
            <i class="bi bi-info-circle me-1 text-info"></i>
            Sales Executive View: <strong>{{ Auth::user()->name }}</strong>. Displaying leads assigned to your account.
        </div>
        <span class="badge bg-primary rounded-pill">Total: {{ $leads->total() }}</span>
    </div>
@endif

<!-- Action Toolbar -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <h6 class="fw-bold mb-0 text-dark">Lead Records</h6>
        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.72rem;">
            {{ number_format($leads->total()) }} Total
        </span>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('leads.sample-csv') }}" class="btn btn-sm btn-outline-secondary" title="Download Template">
            <i class="bi bi-download"></i> Sample CSV
        </a>

        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importLeadsModal">
            <i class="bi bi-upload"></i> Import
        </button>

        <a href="{{ route('leads.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary" title="Export to CSV">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export
        </a>

        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createLeadModal">
            <i class="bi bi-plus-lg"></i> Add Lead
        </button>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('leads.index') }}" class="row g-2 align-items-end">
            <!-- Search -->
            <div class="{{ isset($isAgent) && $isAgent ? 'col-md-4' : 'col-md-3' }} col-12">
                <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.72rem;">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Name, phone, city..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2 col-6">
                <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.72rem;">Status</label>
                <select name="status_id" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st->id }}" {{ request('status_id') == $st->id ? 'selected' : '' }}>
                            {{ $st->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Source Filter -->
            <div class="col-md-2 col-6">
                <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.72rem;">Source</label>
                <select name="source_id" class="form-select form-select-sm">
                    <option value="">All Sources</option>
                    @foreach($sources as $src)
                        <option value="{{ $src->id }}" {{ request('source_id') == $src->id ? 'selected' : '' }}>
                            {{ $src->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Agent Filter (Only visible to Admin) -->
            @if(!isset($isAgent) || !$isAgent)
                <div class="col-md-2 col-6">
                    <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.72rem;">Agent</label>
                    <select name="assigned_to" class="form-select form-select-sm">
                        <option value="">All Agents</option>
                        @foreach($agents as $ag)
                            <option value="{{ $ag->id }}" {{ request('assigned_to') == $ag->id ? 'selected' : '' }}>
                                {{ $ag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Project Filter -->
            <div class="{{ isset($isAgent) && $isAgent ? 'col-md-3' : 'col-md-2' }} col-6">
                <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.72rem;">Project</label>
                <select name="project_id" class="form-select form-select-sm">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Feature 5: Smart Tag Filter -->
            <div class="col-md-2 col-6">
                <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.72rem;">Smart Tag</label>
                <select name="tag" class="form-select form-select-sm">
                    <option value="">All Tags</option>
                    @if(isset($allTags))
                        @foreach($allTags as $tg)
                            <option value="{{ $tg }}" {{ request('tag') == $tg ? 'selected' : '' }}>
                                #{{ $tg }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-1 col-12 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Apply Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                @if(request()->hasAny(['search', 'status_id', 'source_id', 'assigned_to', 'project_id', 'tag', 'is_hot', 'due_today']))
                    <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>

            <!-- Quick Filter Chips -->
            <div class="col-12 mt-2 pt-2 border-top d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted fw-semibold" style="font-size: 0.6875rem; text-transform: uppercase;">Quick Filter:</span>
                <a href="{{ route('leads.index') }}" class="btn btn-sm {{ !request()->hasAny(['is_hot', 'due_today', 'tag']) && !request('status_id') ? 'btn-dark' : 'btn-outline-secondary' }} py-0 px-2" style="font-size: 0.72rem;">
                    All ({{ $leads->total() }})
                </a>
                <a href="{{ route('leads.index', array_merge(request()->query(), ['is_hot' => 1])) }}" class="btn btn-sm {{ request('is_hot') ? 'btn-danger' : 'btn-outline-danger' }} py-0 px-2" style="font-size: 0.72rem;">
                    <i class="bi bi-fire"></i> Hot Leads
                </a>
                <a href="{{ route('leads.index', array_merge(request()->query(), ['due_today' => 1])) }}" class="btn btn-sm {{ request('due_today') ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }} py-0 px-2" style="font-size: 0.72rem;">
                    <i class="bi bi-clock"></i> Due Today
                </a>
                @if(isset($allTags) && count($allTags) > 0)
                    <span class="text-muted small ms-2" style="font-size: 0.6875rem;">Tags:</span>
                    @foreach(array_slice($allTags, 0, 4) as $quickTag)
                        <a href="{{ route('leads.index', ['tag' => $quickTag]) }}" class="btn btn-sm {{ request('tag') === $quickTag ? 'btn-secondary text-white' : 'btn-outline-secondary' }} py-0 px-2" style="font-size: 0.7rem;">
                            #{{ $quickTag }}
                        </a>
                    @endforeach
                @endif
            </div>
        </form>
    </div>
</div>

@php
    $canBulkManage = Auth::check() && Auth::user()->canReassignLead();
@endphp

<!-- Bulk Actions Toolbar (Active when 1 or more leads checked) -->
@if($canBulkManage)
    <div id="bulkActionsToolbar" class="card mb-3 p-2 bg-primary-subtle border-primary d-none shadow-sm">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white" id="selectedCountBadge">0 Selected</span>
                <span class="small fw-semibold text-primary-emphasis">Bulk Management:</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Reassign Form -->
                <form action="{{ route('leads.bulk-reassign') }}" method="POST" id="bulkReassignForm" class="d-flex align-items-center gap-1">
                    @csrf
                    <select name="assigned_to" class="form-select form-select-sm" required style="min-width: 170px;">
                        <option value="">Reassign To Executive...</option>
                        @foreach($agents as $ag)
                            <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-person-check me-1"></i> Reassign
                    </button>
                </form>

                <!-- Bulk Status Form -->
                <form action="{{ route('leads.bulk-status') }}" method="POST" id="bulkStatusForm" class="d-flex align-items-center gap-1">
                    @csrf
                    <select name="status_id" class="form-select form-select-sm" required style="min-width: 150px;">
                        <option value="">Change Stage...</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        Update Stage
                    </button>
                </form>

                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                    Clear
                </button>
            </div>
        </div>
    </div>
@endif

<!-- Compact, 100% Screen-Fitted Leads Table (Zero Horizontal Scroll) -->
<div class="card overflow-hidden">
    <div class="table-responsive" style="overflow-x: hidden !important;">
        <table class="table table-hover align-middle mb-0" style="width: 100%; table-layout: auto;">
            <thead>
                <tr>
                    @if($canBulkManage)
                        <th style="width: 32px;" class="ps-3 pe-0">
                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input" title="Select All On This Page">
                        </th>
                    @endif
                    <th style="width: {{ $canBulkManage ? '23%' : '25%' }};">Prospect & Contact</th>
                    <th style="width: 23%;">Requirement & Budget</th>
                    <th style="width: 14%;">Source</th>
                    <th style="width: 15%;">Stage</th>
                    <th style="width: 11%;">Assigned</th>
                    <th style="width: 12%;" class="text-end pe-3">Next Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    <tr>
                        @if($canBulkManage)
                            <td class="ps-3 pe-0">
                                <input type="checkbox" class="form-check-input lead-select-checkbox" value="{{ $lead->id }}">
                            </td>
                        @endif

                        <!-- Prospect & Contact Column -->
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted small me-1 fw-medium">#{{ $lead->id }}</span>
                                <a href="{{ route('leads.show', $lead->id) }}" class="fw-semibold text-dark text-decoration-none text-truncate" style="max-width: 150px;">
                                    {{ $lead->name }}
                                </a>
                                @if($lead->is_hot)
                                    <span class="badge-hot-pro ms-1"><i class="bi bi-fire"></i> Hot</span>
                                @endif
                                @if(isset($duplicatePhones) && in_array($lead->phone, $duplicatePhones))
                                    <span class="badge bg-success-subtle text-success border border-success-subtle py-0 px-1 ms-1" style="font-size: 0.62rem;" title="Cross-Project Buyer: Multiple project inquiries">
                                        <i class="bi bi-diagram-3"></i> Multi-Project
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-1 small" style="font-size: 0.75rem;">
                                <span class="text-dark fw-medium">{{ $lead->phone }}</span>
                                <a href="tel:{{ $lead->phone }}" class="text-primary text-decoration-none" title="Dial">
                                    <i class="bi bi-telephone-fill"></i>
                                </a>
                                <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="text-success text-decoration-none" title="WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                @if($lead->city || $lead->preferred_location)
                                    <span class="text-muted text-truncate" style="max-width: 90px;">· {{ $lead->preferred_location ?? $lead->city }}</span>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                <!-- Feature 7: Intent Score -->
                                <span class="badge {{ $lead->intent_label['badge'] }} py-0 px-1" style="font-size: 0.62rem;" title="AI Intent Score: {{ $lead->intent_score }}/100 ({{ $lead->intent_label['label'] }})">
                                    <i class="bi {{ $lead->intent_label['icon'] }} me-0.5"></i> {{ $lead->intent_score }}
                                </span>
                                <!-- Feature 5: Smart Tags -->
                                @if(!empty($lead->tags) && is_array($lead->tags))
                                    @foreach(array_slice($lead->tags, 0, 2) as $tg)
                                        <a href="{{ route('leads.index', ['tag' => $tg]) }}" class="badge bg-light text-secondary border text-decoration-none py-0 px-1" style="font-size: 0.62rem;" title="Filter by #{{ $tg }}">
                                            #{{ $tg }}
                                        </a>
                                    @endforeach
                                    @if(count($lead->tags) > 2)
                                        <span class="text-muted" style="font-size: 0.62rem;">+{{ count($lead->tags) - 2 }}</span>
                                    @endif
                                @endif
                            </div>
                        </td>

                        <!-- Requirement & Budget Column -->
                        <td>
                            <div class="d-flex align-items-center gap-1 text-truncate">
                                <span class="fw-medium text-dark">{{ $lead->property_type ?? 'Residential' }}</span>
                                @if($lead->configuration)
                                    <span class="badge bg-light text-secondary border py-0 px-1" style="font-size: 0.68rem;">{{ $lead->configuration }}</span>
                                @endif
                            </div>
                            <div class="small mt-0.5" style="font-size: 0.75rem;">
                                @if($lead->budget_max)
                                    <span class="fw-bold text-dark">₹ {{ number_format($lead->budget_max / 100000, 1) }}L</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                                @if($lead->enquiry_name || $lead->project)
                                    <span class="text-muted text-truncate d-inline-block" style="max-width: 130px; vertical-align: bottom;">
                                        · {{ $lead->enquiry_name ?? $lead->project?->name }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Source Column -->
                        <td>
                            <span class="badge bg-light text-secondary border fw-normal text-truncate d-inline-block" style="font-size: 0.72rem; max-width: 125px;">
                                {{ $lead->source_name }}
                            </span>
                        </td>

                        <!-- Pipeline Stage Column -->
                        <td>
                            <span class="status-pill text-truncate d-inline-flex" style="background-color: {{ $lead->status?->color }}12; color: {{ $lead->status?->color }}; border-color: {{ $lead->status?->color }}30; max-width: 135px;">
                                <span class="status-dot" style="background-color: {{ $lead->status?->color }};"></span>
                                <span class="text-truncate">{{ $lead->status?->name ?? 'New' }}</span>
                            </span>
                        </td>

                        <!-- Assigned Agent Column -->
                        <td>
                            <span class="text-dark small text-truncate d-inline-block" style="max-width: 95px; font-size: 0.78rem;">
                                {{ $lead->assignedAgent?->name ?? 'Unassigned' }}
                            </span>
                        </td>

                        <!-- Next Action Column -->
                        <td class="text-end pe-3">
                            @if($lead->next_follow_up_date)
                                @php
                                    $isOverdue = $lead->next_follow_up_date->isPast();
                                    $isToday = $lead->next_follow_up_date->isToday();
                                @endphp
                                <div class="{{ $isOverdue ? 'text-danger fw-semibold' : ($isToday ? 'text-warning-emphasis fw-semibold' : 'text-muted') }}" style="font-size: 0.72rem; line-height: 1.2;">
                                    {{ $lead->next_follow_up_date->format('d M, h:i A') }}
                                </div>
                                @if($isOverdue)
                                    <span class="badge bg-danger-subtle text-danger py-0 px-1" style="font-size: 0.62rem;">Overdue</span>
                                @elseif($isToday)
                                    <span class="badge bg-warning-subtle text-dark py-0 px-1" style="font-size: 0.62rem;">Today</span>
                                @endif
                            @else
                                <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.72rem;">
                                    View
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted small">
                            No lead records matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($leads->hasPages())
        <div class="card-footer bg-white border-top py-2 px-3">
            {{ $leads->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- Modal: Import Leads from CSV -->
<div class="modal fade" id="importLeadsModal" tabindex="-1" aria-labelledby="importLeadsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-semibold text-dark" id="importLeadsModalLabel">
                    <i class="bi bi-cloud-arrow-up text-primary me-2"></i> Import Leads (CSV)
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('leads.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv,text/csv,text/plain" required>
                        <span class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                            Headers: <code>name, phone, email, city, property_type, configuration, budget_min, budget_max, project_name, notes</code>
                        </span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Default Lead Source</label>
                        <select name="source_id" class="form-select form-select-sm">
                            @foreach($sources as $src)
                                <option value="{{ $src->id }}" {{ $src->code === 'walk_in' ? 'selected' : '' }}>
                                    {{ $src->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Assign Leads To</label>
                        <select name="assigned_to" class="form-select form-select-sm">
                            <option value="">Auto-Assign (Round-Robin to Active Executives)</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-semibold small d-block text-dark">Need standard CSV format?</span>
                            <span class="text-muted" style="font-size: 0.72rem;">Download pre-filled column template.</span>
                        </div>
                        <a href="{{ route('leads.sample-csv') }}" class="btn btn-sm btn-outline-secondary flex-shrink-0">
                            <i class="bi bi-download me-1"></i> Sample CSV
                        </a>
                    </div>
                </div>

                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-upload me-1"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($canBulkManage)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.lead-select-checkbox');
    const toolbar = document.getElementById('bulkActionsToolbar');
    const badge = document.getElementById('selectedCountBadge');
    const deselectBtn = document.getElementById('deselectAllBtn');
    const reassignForm = document.getElementById('bulkReassignForm');
    const statusForm = document.getElementById('bulkStatusForm');

    function updateToolbar() {
        const checked = document.querySelectorAll('.lead-select-checkbox:checked');
        const count = checked.length;
        if (count > 0) {
            toolbar.classList.remove('d-none');
            badge.textContent = count + ' Selected';
        } else {
            toolbar.classList.add('d-none');
            if (selectAll) selectAll.checked = false;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateToolbar();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateToolbar);
    });

    if (deselectBtn) {
        deselectBtn.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            if (selectAll) selectAll.checked = false;
            updateToolbar();
        });
    }

    function appendLeadIds(form) {
        form.querySelectorAll('input[name="lead_ids[]"]').forEach(el => el.remove());
        const checked = document.querySelectorAll('.lead-select-checkbox:checked');
        if (checked.length === 0) {
            alert('Please select at least one lead.');
            return false;
        }
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'lead_ids[]';
            input.value = cb.value;
            form.appendChild(input);
        });
        return true;
    }

    if (reassignForm) {
        reassignForm.addEventListener('submit', function(e) {
            if (!appendLeadIds(reassignForm)) e.preventDefault();
        });
    }

    if (statusForm) {
        statusForm.addEventListener('submit', function(e) {
            if (!appendLeadIds(statusForm)) e.preventDefault();
        });
    }
});
</script>
@endif

@endsection
