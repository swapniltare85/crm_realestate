@extends('layouts.app')

@section('title', 'Agent Commission & Incentive Tracker')
@section('page-title', 'Commissions & Incentives')

@section('content')
<div class="container-fluid px-0">

    <!-- Header Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #0f172a; letter-spacing: -0.02em;">Agent Commission & Broker Incentive Tracker</h4>
            <p class="text-muted small mb-0">Automated deal-value commission calculation, 5% standard TDS deduction, and payout disbursement workflows.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('bookings.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-receipt-cutoff me-1"></i> View All Bookings
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show p-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Financial KPI Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-primary border-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Total Commission Accrued</div>
                        <div class="kpi-value text-primary">₹{{ number_format($stats['total_accrued']) }}</div>
                        <div class="text-muted small mt-1" style="font-size: 0.72rem;">Gross commission across booked deals</div>
                    </div>
                    <div class="rounded-3 p-2 bg-primary-subtle text-primary">
                        <i class="bi bi-award fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-warning border-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Pending Approval</div>
                        <div class="kpi-value text-warning">{{ number_format($stats['pending_count']) }}</div>
                        <div class="text-muted small mt-1" style="font-size: 0.72rem;">Deals waiting for manager sign-off</div>
                    </div>
                    <div class="rounded-3 p-2 bg-warning-subtle text-warning">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-info border-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Approved (Ready to Pay)</div>
                        <div class="kpi-value text-dark">₹{{ number_format($stats['total_approved']) }}</div>
                        <div class="text-muted small mt-1" style="font-size: 0.72rem;">Net payable cleared for disbursement</div>
                    </div>
                    <div class="rounded-3 p-2 bg-info-subtle text-info">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-success border-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Total Disbursed</div>
                        <div class="kpi-value text-success">₹{{ number_format($stats['total_paid']) }}</div>
                        <div class="text-muted small mt-1" style="font-size: 0.72rem;">Paid out with bank UTR reference</div>
                    </div>
                    <div class="rounded-3 p-2 bg-success-subtle text-success">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card border mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('commissions.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Payout Statuses</option>
                        <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved (Ready)</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Disbursed / Paid</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                @if(Auth::user()->isAdmin() || Auth::user()->isManager())
                    <div class="col-6 col-md-3">
                        <select name="agent_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Sales Agents</option>
                            @foreach($agents as $a)
                                <option value="{{ $a->id }}" {{ request('agent_id') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-6 col-md-3">
                    <select name="commission_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Types (Agent / CP)</option>
                        <option value="internal_agent" {{ request('commission_type') === 'internal_agent' ? 'selected' : '' }}>Internal Company Agents</option>
                        <option value="channel_partner_broker" {{ request('commission_type') === 'channel_partner_broker' ? 'selected' : '' }}>Channel Partners (Brokers)</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Filter</button>
                    @if(request()->hasAny(['status', 'agent_id', 'commission_type']))
                        <a href="{{ route('commissions.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Commissions Table -->
    <div class="card border">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Booking Ref</th>
                        <th>Agent / Broker</th>
                        <th>Property & Customer</th>
                        <th>Deal Value</th>
                        <th>Rate %</th>
                        <th>Gross Commission</th>
                        <th>TDS (5%)</th>
                        <th>Net Payable</th>
                        <th>Payout Status</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $c)
                        @php $badge = $c->status_badge; @endphp
                        <tr>
                            <td class="ps-3">
                                @if($c->booking)
                                    <a href="{{ route('bookings.show', $c->booking_id) }}" class="fw-bold text-decoration-none text-primary">
                                        {{ $c->booking->booking_reference }}
                                    </a>
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        {{ $c->booking->booking_date->format('d M Y') }}
                                    </div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ $c->commission_type === 'internal_agent' ? ($c->agent?->name ?? 'Agent') : ($c->partner_name ?? 'External Broker') }}
                                </div>
                                <div>
                                    <span class="badge {{ $c->commission_type === 'internal_agent' ? 'bg-primary-subtle text-primary border border-primary' : 'bg-warning-subtle text-dark border border-warning' }}" style="font-size: 0.65rem;">
                                        {{ $c->commission_type === 'internal_agent' ? 'Internal Agent' : 'Channel Partner' }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="fw-medium text-dark">{{ $c->booking?->customer_name ?? 'Customer' }}</div>
                                <div class="text-muted small" style="font-size: 0.72rem;">
                                    {{ $c->booking?->project?->name ?? 'Project' }}
                                    @if($c->booking?->unit)
                                        • {{ $c->booking->unit->tower }} Unit {{ $c->booking->unit->unit_number }}
                                    @endif
                                </div>
                            </td>

                            <td class="fw-medium text-dark">
                                ₹{{ number_format($c->deal_value) }}
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">{{ $c->commission_percentage }}%</span>
                            </td>

                            <td class="fw-bold text-dark">
                                ₹{{ number_format($c->commission_amount) }}
                            </td>

                            <td class="text-danger small">
                                -₹{{ number_format($c->tds_amount) }}
                            </td>

                            <td>
                                <div class="fw-bold text-success fs-6">
                                    ₹{{ number_format($c->net_payable) }}
                                </div>
                            </td>

                            <td>
                                <span class="badge {{ $badge['badge'] }} px-2 py-1" style="font-size: 0.72rem;">
                                    <i class="bi {{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}
                                </span>
                                @if($c->status === 'paid' && $c->payment_reference)
                                    <div class="text-muted" style="font-size: 0.65rem;">Ref: {{ $c->payment_reference }}</div>
                                @elseif($c->status === 'approved' && $c->approver)
                                    <div class="text-muted" style="font-size: 0.65rem;">By {{ $c->approver->name }}</div>
                                @endif
                            </td>

                            <td class="text-end pe-3">
                                @if((Auth::user()->isAdmin() || Auth::user()->isManager()) && $c->status === 'pending_approval')
                                    <form method="POST" action="{{ route('commissions.approve', $c->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary py-1 px-2" title="Approve Commission for Payout">
                                            <i class="bi bi-check-lg me-1"></i> Approve
                                        </button>
                                    </form>
                                @elseif((Auth::user()->isAdmin() || Auth::user()->isManager()) && $c->status === 'approved')
                                    <button type="button" class="btn btn-sm btn-success py-1 px-2" data-bs-toggle="modal" data-bs-target="#disburseModal{{ $c->id }}" title="Disburse Payment">
                                        <i class="bi bi-cash-stack me-1"></i> Disburse
                                    </button>

                                    <!-- Disburse Modal -->
                                    <div class="modal fade" id="disburseModal{{ $c->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-success text-white py-2 px-3">
                                                    <h6 class="modal-title fw-bold mb-0">Disburse Commission - ₹{{ number_format($c->net_payable) }}</h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('commissions.disburse', $c->id) }}">
                                                    @csrf
                                                    <div class="modal-body p-3">
                                                        <div class="p-2 mb-3 bg-light rounded border">
                                                            <div class="d-flex justify-content-between small mb-1">
                                                                <span class="text-muted">Agent / Recipient:</span>
                                                                <span class="fw-bold">{{ $c->commission_type === 'internal_agent' ? ($c->agent?->name ?? 'Agent') : $c->partner_name }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between small mb-1">
                                                                <span class="text-muted">Gross Commission:</span>
                                                                <span>₹{{ number_format($c->commission_amount) }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between small mb-1 text-danger">
                                                                <span>TDS Deducted (5%):</span>
                                                                <span>-₹{{ number_format($c->tds_amount) }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between small fw-bold text-success border-top pt-1 mt-1">
                                                                <span>Net Disbursable Amount:</span>
                                                                <span>₹{{ number_format($c->net_payable) }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary mb-1">Bank UTR / Cheque / Transaction Ref <span class="text-danger">*</span></label>
                                                            <input type="text" name="payment_reference" class="form-control form-control-sm" required placeholder="e.g. UTR #HDFC09283719 or Cheque #839102">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary mb-1">Disbursement Date <span class="text-danger">*</span></label>
                                                            <input type="date" name="paid_at" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                                                        </div>

                                                        <div class="mb-0">
                                                            <label class="form-label small fw-semibold text-secondary mb-1">Notes / Accounts Remarks</label>
                                                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Transferred to HDFC Salary Account"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer py-2 px-3 bg-light">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-success fw-semibold">
                                                            <i class="bi bi-check2-all me-1"></i> Confirm Disbursement
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($c->status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success" style="font-size: 0.72rem;">
                                        <i class="bi bi-check2-all me-1"></i> Paid
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-award fs-1"></i></div>
                                <h6 class="fw-bold text-dark">No Commission Records Found</h6>
                                <p class="text-muted small mb-0">Commissions are automatically created when new property bookings are recorded.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commissions->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-end">
                {{ $commissions->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection
