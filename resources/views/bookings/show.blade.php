@extends('layouts.app')

@section('title', 'Booking ' . $booking->booking_reference)
@section('page-title', 'Booking Details')

@section('content')
<div class="container-fluid px-0">

    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">{{ $booking->booking_reference }}</h4>
                    @php $badge = $booking->status_badge; @endphp
                    <span class="badge {{ $badge['badge'] }} px-2 py-1" style="font-size: 0.75rem;">
                        <i class="bi {{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}
                    </span>
                    @php $payBadge = $booking->payment_status_badge; @endphp
                    <span class="badge {{ $payBadge['badge'] }} px-2 py-1" style="font-size: 0.75rem;">
                        {{ $payBadge['label'] }}
                    </span>
                </div>
                <div class="text-muted small mt-1">
                    Booked on {{ $booking->booking_date->format('d M Y') }} | Closing Agent: <strong>{{ $booking->agent?->name ?? 'Unassigned' }}</strong>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Update Status Modal Button -->
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                <i class="bi bi-pencil-square me-1"></i> Update Status
            </button>

            <!-- Add Milestone Modal Button -->
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMilestoneModal">
                <i class="bi bi-plus-lg me-1"></i> Add Custom Stage
            </button>

            <!-- Print / Receipt Button -->
            <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Booking Slip
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show p-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border mb-0 h-100">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Agreement Value</div>
                    <div class="fs-4 fw-bold text-dark mt-1">₹{{ number_format($booking->agreement_value) }}</div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Initial Token: ₹{{ number_format($booking->token_amount) }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border mb-0 h-100">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Total Collected</div>
                    <div class="fs-4 fw-bold text-success mt-1">₹{{ number_format($booking->total_paid) }}</div>
                    <div class="text-success small fw-medium" style="font-size: 0.72rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>{{ $booking->progress_percentage }}% of deal paid
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border mb-0 h-100">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Pending Balance</div>
                    <div class="fs-4 fw-bold text-warning mt-1">₹{{ number_format($booking->total_pending) }}</div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Remaining construction dues</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border mb-0 h-100">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Payment Schedule Progress</div>
                    <div class="d-flex justify-content-between align-items-center mt-2 mb-1">
                        <span class="small fw-bold text-dark">{{ $booking->milestones->where('status', 'paid')->count() }} of {{ $booking->milestones->count() }} Milestones</span>
                        <span class="badge bg-success">{{ $booking->progress_percentage }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $booking->progress_percentage }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Row: Customer & Property Snapshot -->
    <div class="row g-3 mb-4">
        <!-- Customer Info -->
        <div class="col-12 col-md-6">
            <div class="card border h-100 mb-0">
                <div class="card-header bg-light py-2 px-3 fw-bold small text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-person-fill text-primary"></i> Buyer / Customer Information
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Customer Name</span>
                        <span class="fw-semibold text-dark">{{ $booking->customer_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Contact Mobile</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold text-dark">{{ $booking->customer_phone }}</span>
                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $booking->customer_phone) }}" target="_blank" class="text-success small" title="WhatsApp Customer">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Email Address</span>
                        <span class="text-dark">{{ $booking->customer_email ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted small">CRM Lead Link</span>
                        @if($booking->lead)
                            <a href="{{ route('leads.show', $booking->lead_id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.72rem;">
                                View Lead #{{ $booking->lead_id }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                        @else
                            <span class="text-muted small fst-italic">Direct Booking (No Lead Linked)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Property & Unit Info -->
        <div class="col-12 col-md-6">
            <div class="card border h-100 mb-0">
                <div class="card-header bg-light py-2 px-3 fw-bold small text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-building text-primary"></i> Property & Inventory Details
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Project Name</span>
                        <span class="fw-semibold text-dark">{{ $booking->project?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Location & City</span>
                        <span class="text-dark">{{ $booking->project?->location ?? 'N/A' }}, {{ $booking->project?->city ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Unit / Flat No.</span>
                        @if($booking->unit)
                            <span class="badge bg-primary text-white">
                                {{ $booking->unit->tower }} - Unit {{ $booking->unit->unit_number }} (Floor {{ $booking->unit->floor }})
                            </span>
                        @else
                            <span class="badge bg-secondary">Unassigned Unit</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted small">Configuration & Area</span>
                        <span class="text-dark fw-medium">
                            {{ $booking->unit?->configuration ?? 'Standard' }} ({{ $booking->unit?->carpet_area ?? 0 }} sq.ft)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Construction-Linked Payment Milestones Table -->
    <div class="card border mb-4">
        <div class="card-header bg-white py-3 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar2-check text-primary me-2"></i>Construction-Linked Payment Schedule</h6>
                <div class="text-muted small" style="font-size: 0.72rem;">Track each stage from Token deposit to Final Key Handover / Possession.</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border"><i class="bi bi-info-circle text-primary me-1"></i> Click "Record Payment" to clear milestone</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Milestone / Construction Stage</th>
                        <th>% Share</th>
                        <th>Stage Amount</th>
                        <th>Due Date</th>
                        <th>Payment Status</th>
                        <th>Payment Details</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($booking->milestones as $m)
                        @php $mBadge = $m->status_badge; @endphp
                        <tr class="{{ $m->status === 'paid' ? 'bg-light bg-opacity-50' : '' }}">
                            <td class="ps-3 fw-bold text-muted">{{ $m->stage_order }}</td>

                            <td>
                                <div class="fw-semibold text-dark">{{ $m->milestone_name }}</div>
                                @if($m->notes)
                                    <div class="text-muted small" style="font-size: 0.7rem;">{{ $m->notes }}</div>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">{{ $m->percentage }}%</span>
                            </td>

                            <td>
                                <div class="fw-bold {{ $m->status === 'paid' ? 'text-success' : 'text-dark' }}">
                                    ₹{{ number_format($m->amount) }}
                                </div>
                            </td>

                            <td>
                                <div class="small {{ ($m->status !== 'paid' && $m->due_date && $m->due_date < now()) ? 'text-danger fw-bold' : 'text-dark' }}">
                                    {{ $m->due_date ? $m->due_date->format('d M Y') : 'On Call' }}
                                </div>
                            </td>

                            <td>
                                <span class="badge {{ $mBadge['badge'] }} px-2 py-1" style="font-size: 0.72rem;">
                                    <i class="bi {{ $mBadge['icon'] }} me-1"></i>{{ $mBadge['label'] }}
                                </span>
                            </td>

                            <td>
                                @if($m->status === 'paid')
                                    <div class="text-success small fw-medium" style="font-size: 0.75rem;">
                                        <i class="bi bi-check2-circle me-1"></i>Paid on {{ $m->paid_date ? $m->paid_date->format('d M Y') : 'Date N/A' }}
                                    </div>
                                    @if($m->transaction_reference || $m->payment_method)
                                        <div class="text-muted" style="font-size: 0.68rem;">
                                            {{ $m->payment_method }} {{ $m->transaction_reference ? '• Ref: ' . $m->transaction_reference : '' }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted small fst-italic" style="font-size: 0.72rem;">Awaiting stage completion</span>
                                @endif
                            </td>

                            <td class="text-end pe-3">
                                @if($m->status !== 'paid')
                                    <button type="button" class="btn btn-sm btn-success py-1 px-2" data-bs-toggle="modal" data-bs-target="#payModal{{ $m->id }}" title="Record Customer Payment">
                                        <i class="bi bi-cash-coin me-1"></i> Record Payment
                                    </button>

                                    <!-- Record Payment Modal -->
                                    <div class="modal fade" id="payModal{{ $m->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start border-0 shadow">
                                                <div class="modal-header bg-success text-white py-2 px-3">
                                                    <h6 class="modal-title fw-bold mb-0">Record Payment - {{ $m->milestone_name }}</h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('bookings.milestones.pay', [$booking->id, $m->id]) }}">
                                                    @csrf
                                                    <div class="modal-body p-3">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary mb-1">Payment Amount (INR) <span class="text-danger">*</span></label>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text bg-white">₹</span>
                                                                <input type="number" step="100" name="amount" class="form-control" required value="{{ $m->amount }}">
                                                            </div>
                                                        </div>

                                                        <div class="row g-2 mb-3">
                                                            <div class="col-6">
                                                                <label class="form-label small fw-semibold text-secondary mb-1">Payment Date <span class="text-danger">*</span></label>
                                                                <input type="date" name="paid_date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small fw-semibold text-secondary mb-1">Payment Method <span class="text-danger">*</span></label>
                                                                <select name="payment_method" class="form-select form-select-sm" required>
                                                                    <option value="Cheque">Bank Cheque</option>
                                                                    <option value="NEFT / RTGS">NEFT / RTGS Transfer</option>
                                                                    <option value="UPI">UPI / Digital</option>
                                                                    <option value="Demand Draft">Demand Draft (DD)</option>
                                                                    <option value="Cash Deposit">Bank Cash Deposit</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary mb-1">Cheque / UTR / Reference No.</label>
                                                            <input type="text" name="transaction_reference" class="form-control form-control-sm" placeholder="e.g. UTR #HDFC0029412 or Cheque #492810">
                                                        </div>

                                                        <div class="mb-0">
                                                            <label class="form-label small fw-semibold text-secondary mb-1">Payment Notes / Receipt Remarks</label>
                                                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Cleared via HDFC Bank Bandra Branch."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer py-2 px-3 bg-light">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-success fw-semibold">
                                                            <i class="bi bi-check2-all me-1"></i> Confirm & Mark Paid
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-success small"><i class="bi bi-check-circle-fill"></i> Cleared</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No milestones found. Click "Add Custom Stage" to configure schedule.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Agent Commission & Incentive Section -->
    <div class="card border mb-4">
        <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-award text-primary me-2"></i>Linked Agent Commission & Broker Incentive</h6>
                <div class="text-muted small" style="font-size: 0.72rem;">Automated commission calculation, TDS deduction, and payout approvals.</div>
            </div>
            <a href="{{ route('commissions.index') }}" class="btn btn-sm btn-outline-primary">
                View All Commissions <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Recipient Type</th>
                        <th>Agent / Broker Name</th>
                        <th>Deal Value</th>
                        <th>Commission %</th>
                        <th>Gross Commission</th>
                        <th>TDS (5%)</th>
                        <th>Net Payable</th>
                        <th>Payout Status</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($booking->commissions as $c)
                        @php $cBadge = $c->status_badge; @endphp
                        <tr>
                            <td class="ps-3">
                                <span class="badge {{ $c->commission_type === 'internal_agent' ? 'bg-primary-subtle text-primary border border-primary' : 'bg-warning-subtle text-dark border border-warning' }}" style="font-size: 0.72rem;">
                                    {{ $c->commission_type === 'internal_agent' ? 'Internal Agent' : 'Channel Partner (CP)' }}
                                </span>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">{{ $c->commission_type === 'internal_agent' ? ($c->agent?->name ?? 'Agent') : ($c->partner_name ?? 'Broker') }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ $c->agent?->email }}</div>
                            </td>

                            <td class="fw-medium">₹{{ number_format($c->deal_value) }}</td>

                            <td><span class="badge bg-light text-dark border">{{ $c->commission_percentage }}%</span></td>

                            <td class="fw-bold text-dark">₹{{ number_format($c->commission_amount) }}</td>

                            <td class="text-danger small">-₹{{ number_format($c->tds_amount) }}</td>

                            <td class="fw-bold text-success fs-6">₹{{ number_format($c->net_payable) }}</td>

                            <td>
                                <span class="badge {{ $cBadge['badge'] }} px-2 py-1" style="font-size: 0.72rem;">
                                    <i class="bi {{ $cBadge['icon'] }} me-1"></i>{{ $cBadge['label'] }}
                                </span>
                            </td>

                            <td class="text-end pe-3">
                                @if((Auth::user()->isAdmin() || Auth::user()->isManager()) && $c->status === 'pending_approval')
                                    <form method="POST" action="{{ route('commissions.approve', $c->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary py-1 px-2" title="Approve Commission">
                                            <i class="bi bi-check-lg me-1"></i> Approve
                                        </button>
                                    </form>
                                @elseif($c->status === 'approved' && (Auth::user()->isAdmin() || Auth::user()->isManager()))
                                    <button type="button" class="btn btn-sm btn-success py-1 px-2" data-bs-toggle="modal" data-bs-target="#disburseModal{{ $c->id }}">
                                        <i class="bi bi-cash-stack me-1"></i> Disburse
                                    </button>
                                @else
                                    <span class="text-muted small">{{ $c->status === 'paid' ? 'Paid: ' . ($c->paid_at ? $c->paid_at->format('d M Y') : '') : 'In Process' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-3 text-muted">No commission records linked to this booking.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Update Lifecycle Status -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 px-3 bg-light">
                <h6 class="modal-title fw-bold mb-0">Update Booking Status</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('bookings.update-status', $booking->id) }}">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Booking Lifecycle Status</label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="under_registration" {{ $booking->status === 'under_registration' ? 'selected' : '' }}>Under Registration</option>
                            <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Possession Completed</option>
                            <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled / Refunded</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-secondary mb-1">Status Update Note</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Sale agreement registered at Sub-Registrar office Panvel."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Custom Milestone -->
<div class="modal fade" id="addMilestoneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 px-3 bg-light">
                <h6 class="modal-title fw-bold mb-0">Add Custom Payment Milestone</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('bookings.milestones.store', $booking->id) }}">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Milestone Stage Name <span class="text-danger">*</span></label>
                        <input type="text" name="milestone_name" class="form-control form-control-sm" required placeholder="e.g. Lift & Electrical Works (10%)">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Amount (INR) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">₹</span>
                                <input type="number" step="100" name="amount" class="form-control" required placeholder="500000">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Due Date</label>
                            <input type="date" name="due_date" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-secondary mb-1">Remarks / Description</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Optional notes for this milestone"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Add Stage</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
