@extends('layouts.app')

@section('title', 'Projects & Inventory')
@section('page-title', 'Real Estate Developments & Unit Inventory')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
        <h5 class="fw-bold mb-0 text-dark">Properties & Unit Inventory</h5>
        <span class="text-muted small">Manage real estate inventory, developer pricing tiers, and floor-by-floor unit booking matrix</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
            <i class="bi bi-plus-lg"></i>
            <span>Add Project</span>
        </button>
    </div>
</div>

<!-- Tabs: 1) Projects Overview, 2) Visual Unit Inventory Matrix -->
<div class="card mb-4">
    <div class="card-header border-bottom p-0">
        <ul class="nav nav-tabs border-0" id="projectTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ request('project_id') ? '' : 'active' }} fw-semibold small py-3 px-4 border-0 rounded-0" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projectsTabPane" type="button" role="tab">
                    <i class="bi bi-buildings me-1 text-primary"></i> Master Projects ({{ $projects->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ request('project_id') ? 'active' : '' }} fw-semibold small py-3 px-4 border-0 rounded-0" id="units-tab" data-bs-toggle="tab" data-bs-target="#unitsTabPane" type="button" role="tab">
                    <i class="bi bi-grid-3x3-gap-fill me-1 text-success"></i> Visual Unit Inventory & Flat Booking Matrix
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-4">
        <div class="tab-content" id="projectTabsContent">

            <!-- TAB 1: MASTER PROJECTS OVERVIEW -->
            <div class="tab-pane fade {{ request('project_id') ? '' : 'show active' }}" id="projectsTabPane" role="tabpanel">
                <div class="row g-3">
                    @forelse($projects as $p)
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100 shadow-none">
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-light text-secondary border fw-medium mb-1" style="font-size: 0.7rem;">{{ $p->property_type }}</span>
                                                <h6 class="fw-bold text-dark mb-0">{{ $p->name }}</h6>
                                                <span class="text-muted" style="font-size: 0.75rem;">Developer: <strong>{{ $p->builder_name ?? 'Reputed Developer' }}</strong></span>
                                            </div>
                                            <span class="badge {{ $p->status === 'Ready to Move' ? 'bg-success-subtle text-success' : ($p->status === 'Ongoing' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-dark') }} fw-semibold" style="font-size: 0.7rem;">
                                                {{ $p->status }}
                                            </span>
                                        </div>

                                        <div class="text-muted my-2 d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                                            <i class="bi bi-geo-alt text-danger"></i>
                                            <span class="fw-medium text-dark">{{ $p->location }}</span>, <span>{{ $p->city }}</span>
                                        </div>

                                        <div class="p-2 bg-light rounded border mb-2" style="font-size: 0.78rem;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Starting Price:</span>
                                                <span class="fw-bold text-dark">
                                                    ₹ {{ number_format(($p->price_starting ?? 0) / 100000, 1) }} Lakhs
                                                </span>
                                            </div>
                                            @if($p->price_max)
                                                <div class="d-flex justify-content-between align-items-center mt-1 pt-1 border-top border-light-subtle">
                                                    <span class="text-muted">Maximum Price:</span>
                                                    <span class="fw-bold text-dark">
                                                        ₹ {{ number_format(($p->price_max ?? 0) / 100000, 1) }} Lakhs
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <p class="text-muted mb-3" style="min-height: 38px; font-size: 0.78rem; line-height: 1.45;">
                                            {{ \Illuminate\Support\Str::limit($p->description, 110) }}
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-auto">
                                        <div class="text-muted" style="font-size: 0.78rem;">
                                            <i class="bi bi-people text-primary me-1"></i>
                                            <strong class="text-dark">{{ $p->leads_count }}</strong> Leads Interested
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="{{ route('projects.index', ['project_id' => $p->id]) }}" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size: 0.72rem;">
                                                <i class="bi bi-grid-3x3-gap me-1"></i> Units ({{ $p->units_count }})
                                            </a>
                                            <a href="{{ route('leads.index', ['project_id' => $p->id]) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.72rem;">
                                                Leads
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 text-muted">
                            No projects added yet. Click "Add Project" above to create your inventory.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 2: VISUAL UNIT INVENTORY & FLAT BOOKING MATRIX -->
            <div class="tab-pane fade {{ request('project_id') ? 'show active' : '' }}" id="unitsTabPane" role="tabpanel">
                @if($selectedProject)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <form action="{{ route('projects.index') }}" method="GET" class="d-inline">
                                <select name="project_id" class="form-select form-select-sm fw-bold border-primary text-primary" onchange="this.form.submit()" style="min-width: 220px;">
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ $selectedProject->id == $proj->id ? 'selected' : '' }}>
                                            🏢 {{ $proj->name }} ({{ $proj->city }})
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            <span class="text-muted small">
                                Developer: <strong class="text-dark">{{ $selectedProject->builder_name ?? 'Reputed' }}</strong> · Location: <strong class="text-dark">{{ $selectedProject->location }}</strong>
                            </span>
                        </div>

                        <!-- Inventory Status KPI Legend -->
                        <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.78rem;">
                            @php
                                $totalUnits = $selectedProject->units->count();
                                $availUnits = $selectedProject->units->where('status', 'available')->count();
                                $blockedUnits = $selectedProject->units->where('status', 'blocked')->count();
                                $soldUnits = $selectedProject->units->where('status', 'sold')->count();
                            @endphp
                            <span class="badge bg-light text-dark border px-2 py-1.5">
                                Total: <strong>{{ $totalUnits }}</strong> Units
                            </span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1.5">
                                <span class="d-inline-block rounded-circle bg-success me-1" style="width: 8px; height: 8px;"></span>
                                Available: <strong>{{ $availUnits }}</strong>
                            </span>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1.5">
                                <span class="d-inline-block rounded-circle bg-warning me-1" style="width: 8px; height: 8px;"></span>
                                Blocked / Token: <strong>{{ $blockedUnits }}</strong>
                            </span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1.5">
                                <span class="d-inline-block rounded-circle bg-danger me-1" style="width: 8px; height: 8px;"></span>
                                Booked / Sold: <strong>{{ $soldUnits }}</strong>
                            </span>
                        </div>
                    </div>

                    @if($selectedProject->units->isNotEmpty())
                        <!-- Floor-by-Floor Interactive Matrix -->
                        @php
                            $floors = $selectedProject->units->groupBy('floor')->sortKeysDesc();
                        @endphp

                        <div class="inventory-matrix">
                            @foreach($floors as $floorNum => $unitsOnFloor)
                                <div class="card mb-2 border shadow-none">
                                    <div class="card-header py-1.5 px-3 bg-light d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-dark small" style="font-size: 0.75rem;">
                                            <i class="bi bi-layers me-1 text-primary"></i> Floor {{ $floorNum }}
                                        </span>
                                        <span class="text-muted small" style="font-size: 0.7rem;">{{ $unitsOnFloor->count() }} Units</span>
                                    </div>
                                    <div class="card-body p-2.5">
                                        <div class="row g-2">
                                            @foreach($unitsOnFloor as $unit)
                                                @php
                                                    $isAvail = $unit->status === 'available';
                                                    $isBlocked = $unit->status === 'blocked';
                                                    $isSold = $unit->status === 'sold';
                                                    $borderCol = $isAvail ? '#10b981' : ($isBlocked ? '#f59e0b' : '#ef4444');
                                                    $bgCol = $isAvail ? '#ecfdf5' : ($isBlocked ? '#fffbeb' : '#fef2f2');
                                                @endphp
                                                <div class="col-6 col-md-3">
                                                    <div class="p-2 rounded border h-100 position-relative" style="background-color: {{ $bgCol }}; border-color: {{ $borderCol }} !important; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#unitStatusModal{{ $unit->id }}">
                                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                                            <strong class="text-dark fs-6">Unit {{ $unit->unit_number }}</strong>
                                                            <span class="badge {{ $isAvail ? 'bg-success' : ($isBlocked ? 'bg-warning text-dark' : 'bg-danger') }}" style="font-size: 0.65rem;">
                                                                {{ ucfirst($unit->status) }}
                                                            </span>
                                                        </div>
                                                        <div class="text-secondary small" style="font-size: 0.75rem;">
                                                            <span>{{ $unit->configuration }}</span> · <span>{{ $unit->carpet_area }} sq.ft</span>
                                                        </div>
                                                        <div class="fw-bold text-dark mt-1" style="font-size: 0.8125rem;">
                                                            ₹ {{ number_format($unit->base_price / 100000, 1) }} Lakhs
                                                        </div>
                                                        @if(!$isAvail && $unit->bookedLead)
                                                            <div class="text-muted small mt-1 pt-1 border-top border-secondary-subtle" style="font-size: 0.7rem;">
                                                                Buyer: <strong class="text-dark">{{ $unit->bookedLead->name }}</strong>
                                                                @if($unit->token_amount)
                                                                    (Token: ₹{{ number_format($unit->token_amount / 100000, 1) }}L)
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Unit Booking / Status Modal -->
                                                <div class="modal fade" id="unitStatusModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow rounded-3">
                                                            <div class="modal-header border-bottom py-3 px-4">
                                                                <h6 class="modal-title fw-semibold text-dark mb-0">
                                                                    Unit {{ $unit->unit_number }} (Floor {{ $unit->floor }}) - {{ $selectedProject->name }}
                                                                </h6>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="{{ route('projects.units.status', $unit->id) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-body p-4">
                                                                    <div class="p-3 bg-light rounded border mb-3">
                                                                        <div class="row g-2" style="font-size: 0.8125rem;">
                                                                            <div class="col-6">Config: <strong>{{ $unit->configuration }}</strong></div>
                                                                            <div class="col-6">Carpet: <strong>{{ $unit->carpet_area }} sq.ft</strong></div>
                                                                            <div class="col-6">Price: <strong>₹ {{ number_format($unit->base_price / 100000, 2) }} Lakhs</strong></div>
                                                                            <div class="col-6">Tower: <strong>{{ $unit->tower }}</strong></div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-semibold text-secondary mb-1">Unit Availability Status <span class="text-danger">*</span></label>
                                                                        <select name="status" class="form-select form-select-sm" required>
                                                                            <option value="available" {{ $unit->status === 'available' ? 'selected' : '' }}>🟢 Available for Sale</option>
                                                                            <option value="blocked" {{ $unit->status === 'blocked' ? 'selected' : '' }}>🟡 Blocked / Token Received</option>
                                                                            <option value="sold" {{ $unit->status === 'sold' ? 'selected' : '' }}>🔴 Booked & Sold</option>
                                                                        </select>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-semibold text-secondary mb-1">Tag Client / Buyer Lead</label>
                                                                        <select name="booked_lead_id" class="form-select form-select-sm">
                                                                            <option value="">None / Open Booking</option>
                                                                            @foreach($leads as $ld)
                                                                                <option value="{{ $ld->id }}" {{ $unit->booked_lead_id == $ld->id ? 'selected' : '' }}>
                                                                                    #{{ $ld->id }} {{ $ld->name }} ({{ $ld->phone }})
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-semibold text-secondary mb-1">Token / Advance Amount (₹)</label>
                                                                        <input type="number" name="token_amount" class="form-control form-control-sm" value="{{ $unit->token_amount }}" placeholder="e.g. 500000">
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-semibold text-secondary mb-1">Notes / Terms</label>
                                                                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Booking terms, agreement deadline...">{{ $unit->notes }}</textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer border-top py-2 px-4">
                                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-sm btn-primary">Update Unit Status</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-grid-3x3-gap fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <p class="small mb-0">No units initialized for this project yet.</p>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5 text-muted">
                        No projects available.
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-semibold text-dark mb-0">Add New Real Estate Project</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary mb-1">Project Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Skyline Towers" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary mb-1">Builder / Developer Name</label>
                            <input type="text" name="builder_name" class="form-control form-control-sm" placeholder="e.g. Godrej, Lodha, Sobha">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Property Type <span class="text-danger">*</span></label>
                            <select name="property_type" class="form-select form-select-sm" required>
                                <option value="Apartment">Apartment / Flat</option>
                                <option value="Villa">Villa / Row House</option>
                                <option value="Plot">Residential Plot</option>
                                <option value="Commercial">Commercial Space</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Project Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="Ongoing">Ongoing / Under Construction</option>
                                <option value="Ready to Move">Ready to Move</option>
                                <option value="Upcoming">Upcoming / Pre-Launch</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Location / Area <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control form-control-sm" placeholder="e.g. Bandra West" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control form-control-sm" placeholder="e.g. Mumbai" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Starting Price (₹)</label>
                            <input type="number" name="price_starting" class="form-control form-control-sm" placeholder="e.g. 7500000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Max Price (₹)</label>
                            <input type="number" name="price_max" class="form-control form-control-sm" placeholder="e.g. 15000000">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary mb-1">Overview / Description</label>
                            <textarea name="description" rows="3" class="form-control form-control-sm" placeholder="Amenities, key highlights, nearby landmarks..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
