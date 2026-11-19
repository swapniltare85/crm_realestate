@extends('layouts.app')

@section('title', 'Record New Property Booking')
@section('page-title', 'New Booking')

@section('content')
<div class="container-fluid px-0" style="max-width: 960px;">

    <!-- Back button & Header -->
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-0 text-dark">Record New Property Booking</h5>
            <p class="text-muted small mb-0">Create deal agreement, lock inventory unit, and auto-generate construction milestones.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show p-3" role="alert">
            <div class="fw-semibold small mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please fix the following errors:</div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('bookings.store') }}">
        @csrf

        @if($prefilledLead)
            <input type="hidden" name="lead_id" value="{{ $prefilledLead->id }}">
            <div class="alert alert-primary d-flex align-items-center justify-content-between p-3 mb-4 rounded-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-check-fill fs-4 text-primary"></i>
                    <div>
                        <div class="fw-bold small text-dark">Converting CRM Lead: {{ $prefilledLead->name }}</div>
                        <div class="text-muted" style="font-size: 0.72rem;">
                            Phone: {{ $prefilledLead->phone }} | Enquired Project: {{ $prefilledLead->project?->name ?? 'General' }}
                        </div>
                    </div>
                </div>
                <span class="badge bg-primary text-white">Lead #{{ $prefilledLead->id }}</span>
            </div>
        @endif

        <!-- Card 1: Customer Snapshot -->
        <div class="card border mb-3">
            <div class="card-header bg-light py-2 px-3 fw-bold small text-dark d-flex align-items-center gap-2">
                <i class="bi bi-person-vcard text-primary"></i> 1. Buyer / Customer Information
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Customer Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control form-control-sm" required placeholder="e.g. Rahul Sharma" value="{{ old('customer_name', $prefilledLead?->name) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Mobile / WhatsApp No. <span class="text-danger">*</span></label>
                        <input type="text" name="customer_phone" class="form-control form-control-sm" required placeholder="e.g. 9820198201" value="{{ old('customer_phone', $prefilledLead?->phone) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Email Address</label>
                        <input type="email" name="customer_email" class="form-control form-control-sm" placeholder="customer@example.com" value="{{ old('customer_email', $prefilledLead?->email) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Property & Unit Selection -->
        <div class="card border mb-3">
            <div class="card-header bg-light py-2 px-3 fw-bold small text-dark d-flex align-items-center gap-2">
                <i class="bi bi-building text-primary"></i> 2. Property & Unit Details
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-secondary mb-1">Project <span class="text-danger">*</span></label>
                        <select name="project_id" id="projectSelect" class="form-select form-select-sm" required onchange="filterUnitsByProject()">
                            <option value="">-- Select Project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ (old('project_id', $prefilledLead?->project_id) == $p->id) ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ $p->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-secondary mb-1">Select Unit (Flat / Villa / Shop)</label>
                        <select name="unit_id" id="unitSelect" class="form-select form-select-sm" onchange="autoFillPriceFromUnit()">
                            <option value="">-- Select Available Unit (Optional) --</option>
                            @foreach($projects as $p)
                                @foreach($p->units as $u)
                                    <option value="{{ $u->id }}" data-project="{{ $p->id }}" data-price="{{ $u->base_price }}" {{ old('unit_id') == $u->id ? 'selected' : '' }}>
                                        {{ $p->name }} | {{ $u->tower }} - Unit {{ $u->unit_number }} ({{ $u->configuration }}, {{ $u->carpet_area }} sq.ft) - ₹{{ number_format($u->base_price) }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        <div class="form-text text-muted" style="font-size: 0.7rem;">Selecting an inventory unit will automatically mark its status as Sold.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Financials & Booking Token -->
        <div class="card border mb-3">
            <div class="card-header bg-light py-2 px-3 fw-bold small text-dark d-flex align-items-center gap-2">
                <i class="bi bi-currency-rupee text-primary"></i> 3. Commercials & Booking Date
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Total Agreement Value (INR) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">₹</span>
                            <input type="number" step="1000" name="agreement_value" id="agreementValueInput" class="form-control" required placeholder="e.g. 7500000" value="{{ old('agreement_value') }}">
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.7rem;">Final negotiated sale agreement price.</div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Token / Booking Advance (INR) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">₹</span>
                            <input type="number" step="500" name="token_amount" class="form-control" required placeholder="e.g. 200000" value="{{ old('token_amount', 100000) }}">
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.7rem;">Initial booking deposit received.</div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Booking Date <span class="text-danger">*</span></label>
                        <input type="date" name="booking_date" class="form-control form-control-sm" required value="{{ old('booking_date', date('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Sales Agent & Commission Structure -->
        <div class="card border mb-3">
            <div class="card-header bg-light py-2 px-3 fw-bold small text-dark d-flex align-items-center gap-2">
                <i class="bi bi-award text-primary"></i> 4. Closing Agent & Commission Structure
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Closing Sales Agent <span class="text-danger">*</span></label>
                        <select name="agent_id" class="form-select form-select-sm" required>
                            @foreach($agents as $a)
                                <option value="{{ $a->id }}" {{ (old('agent_id', $prefilledLead?->assigned_to ?? Auth::id()) == $a->id) ? 'selected' : '' }}>
                                    {{ $a->name }} ({{ ucfirst(str_replace('_', ' ', $a->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Commission Type</label>
                        <select name="commission_type" id="commissionTypeSelect" class="form-select form-select-sm" onchange="togglePartnerName()">
                            <option value="internal_agent" {{ old('commission_type') === 'internal_agent' ? 'selected' : '' }}>Internal Company Agent</option>
                            <option value="channel_partner_broker" {{ old('commission_type') === 'channel_partner_broker' ? 'selected' : '' }}>Channel Partner / External Broker</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4" id="partnerNameCol" style="display: none;">
                        <label class="form-label small fw-semibold text-secondary mb-1">Channel Partner / Broker Name</label>
                        <input type="text" name="partner_name" class="form-control form-control-sm" placeholder="e.g. Apex Realty Partners" value="{{ old('partner_name') }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Commission Percentage (%)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.05" min="0" max="25" name="commission_percentage" class="form-control" value="{{ old('commission_percentage', 1.50) }}">
                            <span class="input-group-text bg-white">%</span>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.7rem;">5% standard TDS will be deducted automatically.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card border mb-4">
            <div class="card-body p-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Booking Notes & Special Terms</label>
                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Token received via Cheque #492810 HDFC Bank. Balance agreement due in 30 days.">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                <i class="bi bi-check-circle-fill me-1"></i> Confirm & Generate Booking
            </button>
        </div>
    </form>

</div>

<script>
function filterUnitsByProject() {
    const projId = document.getElementById('projectSelect').value;
    const unitSelect = document.getElementById('unitSelect');
    const options = unitSelect.querySelectorAll('option');

    options.forEach(opt => {
        if (!opt.value) {
            opt.style.display = 'block';
            return;
        }
        const optProj = opt.getAttribute('data-project');
        if (!projId || optProj === projId) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    });

    // Reset selection if current selected is hidden
    const selected = unitSelect.selectedOptions[0];
    if (selected && selected.value && selected.getAttribute('data-project') !== projId) {
        unitSelect.value = '';
    }
}

function autoFillPriceFromUnit() {
    const unitSelect = document.getElementById('unitSelect');
    const selected = unitSelect.selectedOptions[0];
    if (selected && selected.hasAttribute('data-price')) {
        const price = parseFloat(selected.getAttribute('data-price'));
        if (price > 0) {
            const agreementInput = document.getElementById('agreementValueInput');
            if (!agreementInput.value || parseFloat(agreementInput.value) === 0) {
                agreementInput.value = price;
            }
        }
    }
}

function togglePartnerName() {
    const type = document.getElementById('commissionTypeSelect').value;
    const col = document.getElementById('partnerNameCol');
    col.style.display = (type === 'channel_partner_broker') ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    filterUnitsByProject();
    togglePartnerName();
});
</script>
@endsection
