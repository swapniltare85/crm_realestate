<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'booking_id',
        'uploaded_by',
        'document_type',
        'document_title',
        'file_path',
        'file_name',
        'file_size',
        'file_extension',
        'verification_status',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return match($this->document_type) {
            'aadhaar_card' => 'Aadhaar Card (Identity Proof)',
            'pan_card' => 'PAN Card (Tax / Financial)',
            'token_cheque' => 'Token Cheque / Payment Slip',
            'allotment_letter' => 'Signed Allotment Letter',
            'sale_agreement' => 'Agreement for Sale',
            'passport' => 'Passport / NRI Proof',
            'salary_slip' => 'Income / Salary Slips',
            default => 'General Document',
        };
    }

    public function getIconAttribute(): string
    {
        $ext = strtolower($this->file_extension);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            return 'bi-file-earmark-image text-primary';
        } elseif ($ext === 'pdf') {
            return 'bi-file-earmark-pdf text-danger';
        } elseif (in_array($ext, ['doc', 'docx'])) {
            return 'bi-file-earmark-word text-info';
        }
        return 'bi-file-earmark text-secondary';
    }

    public function getTitleAttribute(): string
    {
        return $this->document_title ?? '';
    }

    public function getRejectionReasonAttribute(): ?string
    {
        return $this->notes;
    }

    public function getVerificationBadgeHtmlAttribute(): string
    {
        $info = $this->verification_badge;
        return '<span class="badge ' . $info['badge'] . '"><i class="bi ' . $info['icon'] . ' me-1"></i>' . $info['label'] . '</span>';
    }

    public function getVerificationBadgeAttribute(): array
    {
        return match($this->verification_status) {
            'verified' => ['label' => 'Verified', 'badge' => 'bg-success text-white', 'icon' => 'bi-check-circle-fill'],
            'rejected' => ['label' => 'Rejected', 'badge' => 'bg-danger text-white', 'icon' => 'bi-x-circle-fill'],
            default => ['label' => 'Pending Verification', 'badge' => 'bg-warning text-dark', 'icon' => 'bi-hourglass-split'],
        };
    }
}
