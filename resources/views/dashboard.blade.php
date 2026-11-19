@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Sales Performance & Pipeline Dashboard')

@section('content')

<!-- Corporate Sub-Header / Filter Bar -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h6 class="fw-bold mb-0 text-dark">Sales Performance Overview</h6>
        <span class="text-muted small" style="font-size: 0.75rem;">Real-time pipeline metrics, conversion velocity, and daily call queues</span>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('leads.index', ['due_today' => 1]) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-clock text-danger"></i>
            <span>Due Today ({{ count($pendingFollowups) }})</span>
        </a>
        <a href="{{ route('leads.index', ['is_hot' => 1]) }}" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-fire"></i>
            <span>Hot Leads ({{ $hotLeads }})</span>
        </a>
        <a href="{{ route('leads.index') }}" class="btn btn-sm btn-primary">
            <span>View All Leads</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

<!-- 4 Core Executive KPI Cards -->
<div class="row g-2 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">Total Active Leads</span>
                <span class="badge bg-light text-secondary border">Database</span>
            </div>
            <div class="kpi-value my-1">{{ number_format($totalLeads) }}</div>
            <div class="small text-muted d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                <i class="bi bi-arrow-up-right text-success fw-bold"></i>
                <span class="text-success fw-medium">All campaigns</span>
                <span>across platforms</span>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">New Enquiries Today</span>
                <span class="badge bg-primary-subtle text-primary">Fresh</span>
            </div>
            <div class="kpi-value my-1 text-primary">{{ number_format($newLeadsToday) }}</div>
            <div class="small text-muted d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                <i class="bi bi-lightning-charge text-primary"></i>
                <span>Direct & meta ad leads</span>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">Scheduled Site Visits</span>
                <span class="badge bg-warning-subtle text-dark">Upcoming</span>
            </div>
            <div class="kpi-value my-1 text-dark">{{ number_format($scheduledVisits) }}</div>
            <div class="small text-muted d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                <i class="bi bi-geo-alt text-warning"></i>
                <span>Property walk-throughs</span>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">Deals Closed (Won)</span>
                <span class="badge bg-success-subtle text-success">Converted</span>
            </div>
            <div class="kpi-value my-1 text-success">{{ number_format($totalWon) }}</div>
            <div class="small text-muted d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                <span class="badge-hot-pro"><i class="bi bi-fire"></i> {{ $hotLeads }} Hot Prospects</span>
            </div>
        </div>
    </div>
</div>

<!-- Pipeline Stage Ribbon -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-bar-chart-steps text-primary"></i>
            <span class="fw-semibold text-dark small">Sales Pipeline Funnel</span>
        </div>
        <span class="text-muted small" style="font-size: 0.72rem;">Conversion stages from initial contact to deal closure</span>
    </div>
    <div class="card-body p-2">
        <div class="row g-2 text-center">
            @foreach($statuses as $st)
                <div class="col">
                    <a href="{{ route('leads.index', ['status_id' => $st->id]) }}" class="text-decoration-none">
                        <div class="p-2 rounded border bg-light h-100" style="border-left: 3px solid {{ $st->color }} !important;">
                            <div class="fw-bold fs-5 text-dark">{{ $st->leads_count }}</div>
                            <div class="text-truncate text-muted fw-medium" style="font-size: 0.7rem;" title="{{ $st->name }}">
                                {{ $st->name }}
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Middle Section: Priority Follow-up Queue & Marketing Acquisition Chart -->
<div class="row g-3 mb-3">
    <!-- Priority Follow-up Action Queue -->
    <div class="col-lg-7 col-xl-8">
        <div class="card h-100 mb-0">
            <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-telephone-outbound text-primary"></i>
                    <span class="fw-semibold text-dark small">Priority Follow-up Queue</span>
                </div>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2" style="font-size: 0.7rem;">
                    {{ count($pendingFollowups) }} Due Today
                </span>
            </div>
            <div class="table-responsive" style="overflow-x: hidden !important;">
                <table class="table table-hover align-middle mb-0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 32%;">Prospect & Contact</th>
                            <th style="width: 25%;">Stage</th>
                            <th style="width: 23%;">Due Time</th>
                            <th style="width: 20%;" class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingFollowups as $followupLead)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="{{ route('leads.show', $followupLead->id) }}" class="fw-semibold text-dark text-decoration-none text-truncate" style="max-width: 140px;">
                                            {{ $followupLead->name }}
                                        </a>
                                        @if($followupLead->is_hot)
                                            <span class="badge-hot-pro ms-1"><i class="bi bi-fire"></i> Hot</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-0.5 text-muted" style="font-size: 0.75rem;">
                                        <span>{{ $followupLead->phone }}</span>
                                        <a href="tel:{{ $followupLead->phone }}" class="text-primary"><i class="bi bi-telephone-fill"></i></a>
                                        <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $followupLead->phone) }}" target="_blank" class="text-success"><i class="bi bi-whatsapp"></i></a>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-pill text-truncate d-inline-flex" style="background-color: {{ $followupLead->status?->color }}12; color: {{ $followupLead->status?->color }}; border-color: {{ $followupLead->status?->color }}30; max-width: 130px;">
                                        <span class="status-dot" style="background-color: {{ $followupLead->status?->color }};"></span>
                                        <span class="text-truncate">{{ $followupLead->status?->name }}</span>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-danger fw-semibold small" style="font-size: 0.75rem;">
                                        {{ $followupLead->next_follow_up_date?->format('d M, h:i A') }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('leads.show', $followupLead->id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.72rem;">
                                        Log Call
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">
                                    <i class="bi bi-check2-circle text-success fs-4 d-block mb-1"></i>
                                    All follow-ups completed for today.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Leads by Marketing Source Chart -->
    <div class="col-lg-5 col-xl-4">
        <div class="card h-100 mb-0">
            <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pie-chart text-primary"></i>
                    <span class="fw-semibold text-dark small">Acquisition Channels</span>
                </div>
                <span class="badge bg-light text-muted border">Campaigns</span>
            </div>
            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                <div style="height: 220px; width: 100%; position: relative;">
                    <canvas id="sourcesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feature 4: Sales Team Leaderboard & Performance Gamification -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-white">
        <div class="d-flex align-items-center gap-2">
            <span class="fs-6">🏆</span>
            <div>
                <span class="fw-bold text-dark small">Sales Team Leaderboard & Performance Gamification</span>
                <span class="text-muted d-block" style="font-size: 0.7rem;">Scored on Won Deals (100 pts), Site Visits (25 pts), and Calls Logged (5 pts)</span>
            </div>
        </div>
        <span class="badge bg-warning-subtle text-dark border border-warning-subtle py-1 px-2" style="font-size: 0.72rem;">
            <i class="bi bi-award-fill text-warning me-1"></i> Live Competition
        </span>
    </div>

    <div class="table-responsive" style="overflow-x: hidden !important;">
        <table class="table table-hover align-middle mb-0" style="width: 100%;">
            <thead class="table-light" style="font-size: 0.75rem;">
                <tr>
                    <th style="width: 8%;" class="ps-3 text-center">Rank</th>
                    <th style="width: 25%;">Executive</th>
                    <th style="width: 18%;">Gamification Score</th>
                    <th style="width: 10%;" class="text-center">Calls</th>
                    <th style="width: 12%;" class="text-center">Site Visits</th>
                    <th style="width: 12%;" class="text-center">Won Deals</th>
                    <th style="width: 15%;" class="text-end pe-3">Booked Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaderboard as $idx => $member)
                    @php
                        $rank = $idx + 1;
                        $maxPoints = $leaderboard->first()?->points ?: 1;
                        $scorePct = min(100, round(($member->points / max(1, $maxPoints)) * 100));
                    @endphp
                    <tr>
                        <td class="ps-3 text-center">
                            @if($rank === 1)
                                <span class="badge bg-warning text-dark border border-warning fw-bold px-2 py-1" style="font-size: 0.78rem;" title="1st Place - Gold Champion">
                                    🥇 1st
                                </span>
                            @elseif($rank === 2)
                                <span class="badge bg-secondary-subtle text-dark border border-secondary fw-bold px-2 py-1" style="font-size: 0.78rem;" title="2nd Place - Silver Star">
                                    🥈 2nd
                                </span>
                            @elseif($rank === 3)
                                <span class="badge bg-danger-subtle text-danger border border-danger fw-bold px-2 py-1" style="font-size: 0.78rem;" title="3rd Place - Bronze Achiever">
                                    🥉 3rd
                                </span>
                            @else
                                <span class="text-muted fw-semibold" style="font-size: 0.8rem;">#{{ $rank }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle flex-shrink-0" style="width: 30px; height: 30px; font-size: 0.75rem; background-color: #0f172a; color: #fff;">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                                <div class="text-truncate">
                                    <div class="fw-semibold text-dark text-truncate" style="font-size: 0.8125rem;">
                                        {{ $member->name }}
                                        @if($member->id === $user->id)
                                            <span class="badge bg-primary text-white py-0 px-1" style="font-size: 0.65rem;">You</span>
                                        @endif
                                    </div>
                                    <span class="text-muted text-capitalize" style="font-size: 0.7rem;">
                                        {{ str_replace('_', ' ', $member->role) }} · {{ $member->total_leads }} active leads
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-dark" style="font-size: 0.8rem;">
                                    <i class="bi bi-stars text-warning me-0.5"></i> {{ number_format($member->points) }} pts
                                </span>
                                <span class="text-muted small" style="font-size: 0.7rem;">{{ $member->conversion_rate }}% conv.</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $scorePct }}%;" aria-valuenow="{{ $scorePct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border fw-medium" style="font-size: 0.75rem;">
                                <i class="bi bi-telephone me-0.5 text-primary"></i> {{ $member->calls_logged }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border fw-medium" style="font-size: 0.75rem;">
                                <i class="bi bi-geo-alt me-0.5 text-warning"></i> {{ $member->site_visits }}
                                <span class="text-success small">({{ $member->completed_visits }} done)</span>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success fw-bold px-2" style="font-size: 0.75rem;">
                                {{ $member->won_deals }} Won
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <span class="fw-bold text-dark" style="font-size: 0.8125rem;">
                                @if($member->revenue_booked >= 10000000)
                                    ₹ {{ number_format($member->revenue_booked / 10000000, 2) }} Cr
                                @elseif($member->revenue_booked > 0)
                                    ₹ {{ number_format($member->revenue_booked / 100000, 1) }} L
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-3 text-muted small">No sales executive data available yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Incoming Leads Stream -->
<div class="card mb-0">
    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock text-primary"></i>
            <span class="fw-semibold text-dark small">Recent Incoming Enquiries</span>
        </div>
        <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.72rem;">
            <span>View All</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="table-responsive" style="overflow-x: hidden !important;">
        <table class="table table-hover align-middle mb-0" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 25%;">Lead Contact</th>
                    <th style="width: 25%;">Requirement & Budget</th>
                    <th style="width: 16%;">Source</th>
                    <th style="width: 16%;">Status</th>
                    <th style="width: 18%;" class="text-end pe-3">Manage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLeads as $lead)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ route('leads.show', $lead->id) }}" class="fw-semibold text-dark text-decoration-none text-truncate" style="max-width: 150px;">
                                    {{ $lead->name }}
                                </a>
                                @if($lead->is_hot)
                                    <span class="badge-hot-pro ms-1"><i class="bi bi-fire"></i> Hot</span>
                                @endif
                            </div>
                            <div class="text-muted small" style="font-size: 0.72rem;">
                                {{ $lead->phone }} @if($lead->city) · {{ $lead->city }} @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <span class="fw-medium text-dark">{{ $lead->property_type ?? 'Residential' }}</span>
                                @if($lead->configuration)
                                    <span class="badge bg-light text-secondary border py-0 px-1" style="font-size: 0.68rem;">{{ $lead->configuration }}</span>
                                @endif
                            </div>
                            <div class="small" style="font-size: 0.72rem;">
                                @if($lead->budget_max)
                                    <span class="fw-bold text-dark">₹ {{ number_format($lead->budget_max / 100000, 1) }}L</span>
                                @endif
                                @if($lead->enquiry_name || $lead->project)
                                    <span class="text-muted text-truncate d-inline-block" style="max-width: 110px; vertical-align: bottom;">
                                        · {{ $lead->enquiry_name ?? $lead->project?->name }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border fw-normal text-truncate d-inline-block" style="font-size: 0.72rem; max-width: 120px;">
                                {{ $lead->source_name }}
                            </span>
                        </td>
                        <td>
                            <span class="status-pill text-truncate d-inline-flex" style="background-color: {{ $lead->status?->color }}12; color: {{ $lead->status?->color }}; border-color: {{ $lead->status?->color }}30; max-width: 130px;">
                                <span class="status-dot" style="background-color: {{ $lead->status?->color }};"></span>
                                <span class="text-truncate">{{ $lead->status?->name ?? 'New' }}</span>
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.72rem;">
                                View Profile
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted small">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('sourcesChart').getContext('2d');
    const sourceLabels = {!! json_encode($sources->pluck('name')) !!};
    const sourceData = {!! json_encode($sources->pluck('leads_count')) !!};

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: sourceLabels,
            datasets: [{
                data: sourceData,
                backgroundColor: [
                    '#1d4ed8', '#0284c7', '#059669', '#d97706',
                    '#7c3aed', '#db2777', '#475569', '#0d9488'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 8,
                        boxHeight: 8,
                        usePointStyle: true,
                        font: { size: 10.5, family: 'Inter' },
                        padding: 10
                    }
                }
            }
        }
    });
</script>
@endpush
