@extends('layouts.app')

@section('title', 'Property Bookings & Payment Milestones')
@section('page-title', 'Bookings & Milestones')

@section('content')
<div class="container-fluid px-0">

    <!-- Header Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #0f172a; letter-spacing: -0.02em;">Property Bookings & Payment Milestones</h4>
            <p class="text-muted small mb-0">Track real estate deal closures, token receipts, and construction-linked payment milestones.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> New Property Booking
            </a>
        </div>
    </div>

    <!-- Financial KPI Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-primary border-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Confirmed Deals</div>
                        <div class="kpi-value text-primary">{{ number_format($stats['total_bookings']) }}</div>
                        <div class="text-muted small mt-1" style="font-size: 0.72rem;">Active property bookings</div>
                    </div>
                    <div class="rounded-3 p-2 bg-primary-subtle text-primary">
                        <i class="bi bi-receipt-cutoff fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-info border-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Total Agreement Value</div>
                        <div class="kpi-value text-dark">₹{{ number_format($stats['total_deal_value']) }}</div>
                        <div class="text-muted small mt-1" style="font-size: 0.72rem;">Gross value of sold units</div>
                    </div>
                    <div class="rounded-3 p-2 bg-info-subtle text-info">
                        <i class="bi bi-buildings fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-success border-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Total Collected</div>
                        <div class="kpi-value text-success">₹{{ number_format($stats['total_collected']) }}</div>
                        <div class="text-muted small mt-1" style="font-size: 0.72rem;">Milestones paid to date</div>
                    </div>
                    <div class="rounded-3 p-2 bg-success-subtle text-success">
                        <i class="bi bi-cash-coin fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-warning border-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Pending Collection</div>
                        <div class="kpi-value text-warning">₹{{ number_format($stats['total_pending']) }}</div>
                        <div class="text-muted small mt-1" style="font-size: 0.72rem;">Upcoming milestone dues</div>
                    </div>
                    <div class="rounded-3 p-2 bg-warning-subtle text-warning">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card border mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('bookings.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search reference, customer, unit..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Booking Statuses</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="under_registration" {{ request('status') === 'under_registration' ? 'selected' : '' }}>Under Registration</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Possession Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Filter</button>
                    @if(request()->hasAny(['search', 'project_id', 'status', 'payment_status']))
                        <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card border">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Booking Ref</th>
                        <th>Customer Details</th>
                        <th>Project & Unit</th>
                        <th>Closing Agent</th>
                        <th>Agreement Value</th>
                        <th style="min-width: 170px;">Payment Progress</th>
                        <th>Lifecycle Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        @php
                            $stBadge = $b->status_badge;
                            $pct = $b->progress_percentage;
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('bookings.show', $b->id) }}" class="fw-bold text-decoration-none text-primary">
                                    {{ $b->booking_reference }}
                                </a>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    {{ $b->booking_date->format('d M Y') }}
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">{{ $b->customer_name }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    <i class="bi bi-telephone text-secondary me-1"></i>{{ $b->customer_phone }}
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">{{ $b->project?->name ?? 'N/A' }}</div>
                                <div class="text-muted small" style="font-size: 0.72rem;">
                                    @if($b->unit)
                                        <span class="badge bg-light text-dark border">
                                            {{ $b->unit->tower }} - Unit {{ $b->unit->unit_number }} ({{ $b->unit->configuration }})
                                        </span>
                                    @else
                                        <span class="text-muted fst-italic">Unit Unassigned</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-person-circle text-secondary" style="font-size: 0.85rem;"></i>
                                    <span class="small fw-medium">{{ $b->agent?->name ?? 'Unassigned' }}</span>
                                </div>
                            </td>

                            <td>
                                <div class="fw-bold text-dark">₹{{ number_format($b->agreement_value) }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    Token: ₹{{ number_format($b->token_amount) }}
                                </div>
                            </td>

                            <td>
                                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.72rem;">
                                    <span class="text-success fw-semibold">₹{{ number_format($b->total_paid) }}</span>
                                    <span class="fw-bold text-dark">{{ $pct }}%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-muted mt-1" style="font-size: 0.68rem;">
                                    Pending: ₹{{ number_format($b->total_pending) }}
                                </div>
                            </td>

                            <td>
                                <span class="badge {{ $stBadge['badge'] }} px-2 py-1" style="font-size: 0.72rem;">
                                    <i class="bi {{ $stBadge['icon'] }} me-1"></i>{{ $stBadge['label'] }}
                                </span>
                            </td>

                            <td class="text-end pe-3">
                                <a href="{{ route('bookings.show', $b->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="View Booking & Milestones">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-receipt-cutoff fs-1"></i></div>
                                <h6 class="fw-bold text-dark">No Property Bookings Found</h6>
                                <p class="text-muted small mb-3">No bookings match the selected filters. Record your first sale closure!</p>
                                <a href="{{ route('bookings.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Record New Booking
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-end">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection
