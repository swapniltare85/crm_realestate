<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerDocument;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerDocumentWebController extends Controller
{
    /**
     * Store an uploaded KYC or Booking compliance document.
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:10240',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:10240',
            'document_type' => 'required|string|max:50',
            'document_title' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'lead_id' => 'nullable|exists:leads,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'notes' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $file = $request->file('document_file') ?? $request->file('file');
        if (!$file) {
            return redirect()->back()->with('error', 'Please select a document file to upload (max 10MB).');
        }

        $documentTitle = $request->input('document_title') ?: ($request->input('title') ?: 'KYC Document');
        $notes = $request->input('notes') ?: $request->input('remarks');

        $origName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $size = $file->getSize();

        // Store file in public/documents folder
        $filePath = $file->store('documents', 'public');

        $doc = CustomerDocument::create([
            'lead_id' => $request->input('lead_id') ?: null,
            'booking_id' => $request->input('booking_id') ?: null,
            'uploaded_by' => Auth::id() ?? 1,
            'document_type' => $request->input('document_type'),
            'document_title' => $documentTitle,
            'file_path' => $filePath,
            'file_name' => $origName,
            'file_size' => $size,
            'file_extension' => $ext,
            'verification_status' => 'pending',
            'notes' => $notes,
        ]);

        // Log to lead activity if attached to lead
        if (!empty($doc->lead_id)) {
            LeadActivity::create([
                'lead_id' => $doc->lead_id,
                'user_id' => Auth::id() ?? 1,
                'activity_type' => 'document_uploaded',
                'description' => "KYC / Booking Document uploaded: '{$documentTitle}' ({$doc->document_type_label})",
            ]);
        }

        return redirect()->back()->with('success', "Document '{$documentTitle}' uploaded successfully!");
    }

    /**
     * Download or view the uploaded document.
     */
    public function download($id)
    {
        $doc = CustomerDocument::findOrFail($id);
        
        if (!Storage::disk('public')->exists($doc->file_path)) {
            abort(404, 'File not found on server.');
        }

        return Storage::disk('public')->download($doc->file_path, $doc->file_name);
    }

    /**
     * Verify or Reject a KYC document (Admin / Manager only).
     */
    public function verify(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Only Admins and Managers can verify compliance documents.');
        }

        $validated = $request->validate([
            'verification_status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
        ]);

        $verifyNotes = $request->input('rejection_reason') ?: $request->input('notes');

        $doc = CustomerDocument::findOrFail($id);
        $doc->update([
            'verification_status' => $validated['verification_status'],
            'verified_by' => $user->id,
            'verified_at' => now(),
            'notes' => !empty($verifyNotes) ? trim(($doc->notes ? $doc->notes . "\n" : '') . "Status: " . ucfirst($validated['verification_status']) . " - Note: " . $verifyNotes) : $doc->notes,
        ]);

        if ($doc->lead_id) {
            LeadActivity::create([
                'lead_id' => $doc->lead_id,
                'user_id' => $user->id,
                'activity_type' => 'document_verified',
                'description' => "Document '{$doc->document_title}' marked as " . strtoupper($validated['verification_status']) . " by {$user->name}.",
            ]);
        }

        return redirect()->back()->with('success', "Document verification updated to " . ucfirst($validated['verification_status']) . "!");
    }

    /**
     * Delete an uploaded document.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $doc = CustomerDocument::findOrFail($id);

        // RBAC: only admin or the uploader can delete
        if (!$user->isAdmin() && (int)$doc->uploaded_by !== (int)$user->id) {
            abort(403, 'Unauthorized to delete this document.');
        }

        if (Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $doc->delete();

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}
