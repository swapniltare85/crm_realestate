<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $booking->booking_reference }} - {{ $booking->customer_name }}</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
            padding: 30px 0;
            margin: 0;
        }

        .receipt-card {
            max-width: 760px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px 50px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .builder-brand {
            font-family: 'Cinzel', serif;
            letter-spacing: 0.05em;
            color: #0f172a;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .receipt-seal {
            width: 140px;
            height: 70px;
            border: 2px dashed #94a3b8;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 0.68rem;
            text-transform: uppercase;
            margin-top: 8px;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .action-bar {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: none !important;
                padding: 20px !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>

    <!-- Action Toolbar -->
    <div class="action-bar d-flex justify-content-between align-items-center mb-3" style="max-width: 760px; margin: 0 auto 16px;">
        <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Booking
        </a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary" onclick="downloadDirectReceiptPDF()" id="downloadReceiptPdfBtn">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Download PDF
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> System Print
            </button>
            <a href="{{ route('bookings.allotment-letter', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-file-earmark-text me-1"></i> View Allotment Letter
            </a>
        </div>
    </div>

    <!-- Receipt Container -->
    <div class="receipt-card" id="receiptCardContent">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
            <div>
                <div class="builder-brand">ESTATEPULSE REALTY DEVELOPERS</div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Official Payment Acknowledgment</div>
                <div class="text-secondary small" style="font-size: 0.72rem;">BKC Commercial Complex, Bandra (E), Mumbai - 400051</div>
            </div>
            <div class="text-end">
                <div class="badge bg-success-subtle text-success border border-success px-2 py-1 fs-6 fw-bold">
                    OFFICIAL RECEIPT
                </div>
                <div class="text-muted small mt-1" style="font-size: 0.7rem;">
                    Receipt No: <strong>RCT-{{ date('Y') }}-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</strong>
                </div>
                <div class="text-muted small" style="font-size: 0.7rem;">
                    Date: <strong>{{ $booking->booking_date->format('d M Y') }}</strong>
                </div>
            </div>
        </div>

        <!-- Receipt Body Table -->
        <table class="table table-bordered mb-4" style="font-size: 0.84rem;">
            <tbody>
                <tr>
                    <td class="bg-light fw-semibold text-muted" style="width: 32%;">Received with thanks from:</td>
                    <td class="fw-bold text-dark fs-6" colspan="3">{{ $booking->customer_name }}</td>
                </tr>
                <tr>
                    <td class="bg-light fw-semibold text-muted">Contact Mobile / Email:</td>
                    <td class="text-dark">{{ $booking->customer_phone }}</td>
                    <td class="bg-light fw-semibold text-muted">Booking Reference:</td>
                    <td class="fw-bold text-primary">{{ $booking->booking_reference }}</td>
                </tr>
                <tr>
                    <td class="bg-light fw-semibold text-muted">Project & Property:</td>
                    <td class="text-dark fw-semibold" colspan="3">
                        {{ $booking->project->name }} ({{ $booking->project->location }}, {{ $booking->project->city }})
                        @if($booking->unit)
                            • {{ $booking->unit->tower }} - Unit {{ $booking->unit->unit_number }} (Floor {{ $booking->unit->floor }})
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="bg-light fw-semibold text-muted">Payment Purpose:</td>
                    <td class="text-dark" colspan="3">Initial Token Advance / Booking Consideration</td>
                </tr>
                <tr>
                    <td class="bg-light fw-semibold text-muted">Payment Mode / Instrument:</td>
                    <td class="text-dark">Bank Cheque / NEFT Transfer</td>
                    <td class="bg-light fw-semibold text-muted">Instrument Reference:</td>
                    <td class="text-dark font-monospace">HDFC-REF-TOKEN-{{ $booking->id }}</td>
                </tr>
                <tr class="table-light">
                    <td class="fw-bold text-dark fs-6">Amount Received:</td>
                    <td class="fw-bold text-success fs-4" colspan="3">
                        ₹{{ number_format($booking->token_amount) }}
                    </td>
                </tr>
                <tr>
                    <td class="bg-light fw-semibold text-muted">Total Agreement Value:</td>
                    <td class="fw-bold text-dark">₹{{ number_format($booking->agreement_value) }}</td>
                    <td class="bg-light fw-semibold text-muted">Balance Payable:</td>
                    <td class="fw-bold text-danger">₹{{ number_format($booking->total_pending) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer Signatures -->
        <div class="row pt-3 border-top mt-4 align-items-end">
            <div class="col-8">
                <div class="text-muted small" style="font-size: 0.72rem;">
                    * Payments subject to realization of cheque / electronic clearance.<br>
                    * This is a computer-generated acknowledgment and serves as official token receipt.
                </div>
            </div>
            <div class="col-4 text-end">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.68rem;">For EstatePulse Realty Developers</div>
                <div class="receipt-seal ms-auto">
                    <span>Authorized Accounts Signatory</span>
                </div>
            </div>
        </div>
    </div>

    <!-- html2pdf Bundle for 1-Click PDF Download without Printer Connection -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadDirectReceiptPDF() {
            const element = document.getElementById('receiptCardContent');
            const btn = document.getElementById('downloadReceiptPdfBtn');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Generating PDF...';
            btn.disabled = true;

            const opt = {
                margin: [10, 10, 10, 10],
                filename: 'Payment_Receipt_{{ Str::slug($booking->customer_name) }}_{{ $booking->booking_reference }}.pdf',
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
