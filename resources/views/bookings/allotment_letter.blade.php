<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allotment Letter - {{ $booking->booking_reference }} - {{ $booking->customer_name }}</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
            font-size: 0.88rem;
            line-height: 1.6;
            margin: 0;
            padding: 20px 0;
        }

        .action-toolbar {
            max-width: 860px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .letter-page {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            padding: 50px 60px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 4px;
            position: relative;
            box-sizing: border-box;
        }

        .builder-title {
            font-family: 'Cinzel', serif;
            letter-spacing: 0.08em;
            color: #0f172a;
            font-weight: 700;
            font-size: 1.35rem;
        }

        .table-custom th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 8px 12px;
            border-bottom: 2px solid #cbd5e1;
        }

        .table-custom td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.82rem;
        }

        .stamp-box {
            width: 170px;
            height: 90px;
            border: 2px dashed #94a3b8;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 0.72rem;
            text-transform: uppercase;
            margin-top: 10px;
        }

        /* Print styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .action-toolbar {
                display: none !important;
            }
            .letter-page {
                box-shadow: none !important;
                padding: 30px 40px !important;
                max-width: 100% !important;
                width: 100% !important;
                border: none !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Print / Action Toolbar -->
    <div class="action-toolbar">
        <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Booking
        </a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary" onclick="downloadDirectPDF()" id="downloadPdfBtn">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Download PDF
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print / Browser PDF
            </button>
            <a href="{{ route('bookings.receipt', $booking->id) }}" class="btn btn-sm btn-success">
                <i class="bi bi-receipt me-1"></i> View Token Receipt
            </a>
        </div>
    </div>

    <!-- Letter Paper Container -->
    <div class="letter-page" id="letterPageContent">

        <!-- 1. Header & Builder Letterhead -->
        <div class="border-bottom pb-3 mb-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="builder-title">ESTATEPULSE REALTY DEVELOPERS</div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                        Infrastructure & Realty Sales Private Limited
                    </div>
                    <div class="text-secondary small mt-1" style="font-size: 0.75rem;">
                        CIN: U45200MH2021PTC369123 | GSTIN: 27AABCE9412M1ZM<br>
                        Corporate Office: Level 14, Tower 2, BKC Commercial Complex, Bandra (E), Mumbai - 400051
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary px-3 py-2 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.08em;">
                        Official Allotment
                    </span>
                    <div class="text-muted small mt-2" style="font-size: 0.72rem;">
                        MahaRERA: <strong>P52000028941</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Reference & Date -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2">
            <div>
                <span class="text-muted small">Reference No:</span>
                <span class="fw-bold text-dark font-monospace ms-1">EPR/ALLOT/2026/{{ $booking->booking_reference }}</span>
            </div>
            <div>
                <span class="text-muted small">Date of Issue:</span>
                <span class="fw-semibold text-dark ms-1">{{ $booking->booking_date->format('F d, Y') }}</span>
            </div>
        </div>

        <!-- 3. Addressee / Customer Details -->
        <div class="bg-light p-3 rounded-2 border mb-4">
            <div class="row g-2">
                <div class="col-sm-6">
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Allottee / Purchaser:</div>
                    <div class="fs-6 fw-bold text-dark mt-1">{{ $booking->customer_name }}</div>
                    <div class="text-secondary small">
                        <i class="bi bi-telephone text-primary me-1"></i> {{ $booking->customer_phone }}
                        @if($booking->customer_email)
                            | <i class="bi bi-envelope text-primary me-1"></i> {{ $booking->customer_email }}
                        @endif
                    </div>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Authorized Sourcing Partner:</div>
                    <div class="fw-semibold text-dark mt-1">{{ $booking->agent?->name ?? 'Company Direct' }}</div>
                    <div class="text-muted small">EstatePulse Sales Division</div>
                </div>
            </div>
        </div>

        <!-- 4. Subject Line -->
        <div class="mb-4">
            <div class="p-2 border-start border-primary border-4 bg-light">
                <span class="fw-bold text-dark">SUB: PROVISIONAL ALLOTMENT LETTER FOR UNIT NO. 
                    {{ $booking->unit ? $booking->unit->unit_number : '[TBD]' }} IN PROJECT "{{ strtoupper($booking->project->name) }}", {{ strtoupper($booking->project->location) }}, {{ strtoupper($booking->project->city) }}.
                </span>
            </div>
        </div>

        <!-- 5. Formal Legal Body -->
        <div class="mb-4 text-secondary" style="text-align: justify;">
            <p class="mb-2">Dear <strong>{{ $booking->customer_name }}</strong>,</p>
            <p class="mb-2">
                We take immense pleasure in confirming the provisional allotment of the residential unit detailed in <strong>Schedule 'A'</strong> below in our prestigious development project <strong>"{{ $booking->project->name }}"</strong> situated at {{ $booking->project->location }}, {{ $booking->project->city }}.
            </p>
            <p class="mb-2">
                This allotment is issued pursuant to your booking application and receipt of the initial token deposit of <strong>₹{{ number_format($booking->token_amount) }}</strong>, subject to the terms and construction payment milestones set forth herein.
            </p>
        </div>

        <!-- 6. Schedule 'A': Property Particulars -->
        <h6 class="fw-bold text-dark text-uppercase small mb-2" style="letter-spacing: 0.05em;">Schedule 'A' - Property & Commercial Specifications</h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-custom mb-0">
                <tbody>
                    <tr>
                        <td class="fw-semibold text-muted" style="width: 30%;">Project Name:</td>
                        <td class="fw-bold text-dark">{{ $booking->project->name }} (RERA Registered)</td>
                        <td class="fw-semibold text-muted" style="width: 25%;">Property Type:</td>
                        <td class="fw-bold text-dark">{{ $booking->project->property_type }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Allotted Unit / Flat No.:</td>
                        <td class="fw-bold text-primary fs-6">
                            {{ $booking->unit ? $booking->unit->tower . ' - Unit ' . $booking->unit->unit_number : 'Unit Pending Allocation' }}
                        </td>
                        <td class="fw-semibold text-muted">Floor & Wing:</td>
                        <td class="text-dark">{{ $booking->unit ? 'Floor ' . $booking->unit->floor . ' (' . $booking->unit->tower . ')' : 'As per plan' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Configuration:</td>
                        <td class="text-dark">{{ $booking->unit ? $booking->unit->configuration : 'Standard Residential' }}</td>
                        <td class="fw-semibold text-muted">RERA Carpet Area:</td>
                        <td class="fw-bold text-dark">{{ $booking->unit ? $booking->unit->carpet_area . ' Sq. Ft. (approx.)' : 'As per sanction' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Parking Space:</td>
                        <td class="text-dark">1 Covered Stilt Car Parking Space</td>
                        <td class="fw-semibold text-muted">Initial Token Received:</td>
                        <td class="fw-bold text-success">₹{{ number_format($booking->token_amount) }}</td>
                    </tr>
                    <tr class="table-light">
                        <td class="fw-bold text-dark fs-6" colspan="2">Total Agreed Consideration (Agreement Value):</td>
                        <td class="fw-bold text-dark fs-5 text-end pe-3" colspan="2">
                            ₹{{ number_format($booking->agreement_value) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 7. Schedule 'B': Construction-Linked Payment Milestones -->
        <h6 class="fw-bold text-dark text-uppercase small mb-2" style="letter-spacing: 0.05em;">Schedule 'B' - Construction-Linked Payment Milestones</h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Construction Milestone Stage</th>
                        <th>% Share</th>
                        <th>Amount Payable (INR)</th>
                        <th>Payment Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->milestones as $m)
                        <tr>
                            <td class="text-muted fw-bold">{{ $m->stage_order }}</td>
                            <td class="fw-medium text-dark">{{ $m->milestone_name }}</td>
                            <td>{{ $m->percentage }}%</td>
                            <td class="fw-bold text-dark">₹{{ number_format($m->amount) }}</td>
                            <td>
                                @if($m->status === 'paid')
                                    <span class="text-success fw-bold"><i class="bi bi-check2-circle me-1"></i> Paid (Token Cleared)</span>
                                @else
                                    <span class="text-secondary small">Payable on Demand (Due: {{ $m->due_date ? $m->due_date->format('d M Y') : 'Stage Completion' }})</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 8. Key Terms & Regulatory Covenants -->
        <div class="small text-muted mb-5 border-top pt-3">
            <div class="fw-bold text-dark mb-1">Standard Allotment Terms:</div>
            <ol class="ps-3 mb-0" style="font-size: 0.75rem; line-height: 1.5;">
                <li>The Agreement for Sale shall be executed and registered within thirty (30) days from the date of this letter, upon clearance of statutory stamp duty and registration fees.</li>
                <li>All payments shall be drawn in favor of <strong>"EstatePulse Realty Developers Escrow A/c"</strong> through banking channels (Cheque/NEFT/RTGS).</li>
                <li>This allotment is subject to the provisions of the Real Estate (Regulation and Development) Act (RERA) and rules made thereunder.</li>
            </ol>
        </div>

        <!-- 9. Signatures & Official Seals -->
        <div class="row pt-3 border-top mt-4">
            <div class="col-6">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">For EstatePulse Realty Developers Pvt. Ltd.</div>
                <div class="stamp-box">
                    <span>Authorized Signatory<br>& Corporate Seal</span>
                </div>
                <div class="fw-bold text-dark small mt-2">Director / Authorized Signatory</div>
            </div>

            <div class="col-6 text-end">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Purchaser / Allottee Acceptance</div>
                <div class="stamp-box ms-auto">
                    <span>Signature of Allottee</span>
                </div>
                <div class="fw-bold text-dark small mt-2">{{ $booking->customer_name }}</div>
                <div class="text-muted small" style="font-size: 0.7rem;">Date: ________________________</div>
            </div>
        </div>

    </div>

    <!-- html2pdf Bundle for 1-Click PDF Download without Printer Connection -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadDirectPDF() {
            const element = document.getElementById('letterPageContent');
            const btn = document.getElementById('downloadPdfBtn');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Generating PDF...';
            btn.disabled = true;

            const opt = {
                margin: [8, 8, 8, 8],
                filename: 'Allotment_Letter_{{ Str::slug($booking->customer_name) }}_{{ $booking->booking_reference }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, logging: false },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }).catch(err => {
                console.error('PDF error:', err);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                window.print();
            });
        }
    </script>
</body>
</html>
