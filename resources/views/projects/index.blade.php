@extends('layouts.app')

@section('title', 'Projects & Inventory')
@section('page-title', 'Real Estate Developments & Inventory')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
        <h5 class="fw-bold mb-0 text-dark">Properties & Developments</h5>
        <span class="text-muted small">Manage real estate inventory, developer pricing tiers, and active buyer interest</span>
    </div>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
        <i class="bi bi-plus-lg"></i>
        <span>Add Project</span>
    </button>
</div>

<div class="row g-3">
    @forelse($projects as $p)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
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
                        <a href="{{ route('leads.index', ['project_id' => $p->id]) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;">
                            View Leads <i class="bi bi-arrow-right ms-1"></i>
                        </a>
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
