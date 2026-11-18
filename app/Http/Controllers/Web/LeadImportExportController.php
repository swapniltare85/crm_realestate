<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadImportExportController extends Controller
{
    /**
     * Export Leads to CSV with current active filters.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Lead::with(['leadSource', 'status', 'project', 'assignedAgent']);

        // Role-based security: Sales Agent can only export their own assigned leads
        if (Auth::check() && Auth::user()->role === 'sales_agent') {
            $query->where('assigned_to', Auth::id());
        } elseif ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Apply filters
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('enquiry_name', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->filled('source_id')) {
            $query->where('source_id', $request->source_id);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->has('is_hot')) {
            $query->where('is_hot', true);
        }

        $filename = 'leads_export_' . now()->format('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header Row
            fputcsv($handle, [
                'Lead ID',
                'Customer Name',
                'Phone',
                'Country Code',
                'Email',
                'City',
                'Property Type',
                'Configuration',
                'Min Budget (INR)',
                'Max Budget (INR)',
                'Preferred Location',
                'Enquiry Name',
                'Sub Project',
                'Status',
                'Lead Source',
                'Assigned Agent',
                'Next Follow-up',
                'Is Hot',
                'Platform',
                'UTM Source',
                'UTM Medium',
                'UTM Campaign',
                'Ad Keyword',
                'GClick ID',
                'Cost / Sourcing Price',
                'Customer Notes',
                'Created Date'
            ]);

            $query->chunk(200, function ($leads) use ($handle) {
                foreach ($leads as $lead) {
                    fputcsv($handle, [
                        $lead->id,
                        $lead->name,
                        $lead->phone,
                        $lead->phone_country_code ?? '+91',
                        $lead->email ?? '',
                        $lead->city ?? '',
                        $lead->property_type ?? '',
                        $lead->configuration ?? '',
                        $lead->budget_min ? number_format($lead->budget_min, 2, '.', '') : '',
                        $lead->budget_max ? number_format($lead->budget_max, 2, '.', '') : '',
                        $lead->preferred_location ?? '',
                        $lead->enquiry_name ?? $lead->project?->name ?? '',
                        $lead->sub_project_name ?? '',
                        $lead->status?->name ?? 'New',
                        $lead->source_name,
                        $lead->assignedAgent?->name ?? 'Unassigned',
                        $lead->next_follow_up_date ? $lead->next_follow_up_date->format('Y-m-d H:i') : '',
                        $lead->is_hot ? 'YES' : 'NO',
                        $lead->platform ?? '',
                        $lead->utm_source ?? '',
                        $lead->utm_medium ?? '',
                        $lead->utm_campaign ?? '',
                        $lead->ads_keyword ?? '',
                        $lead->gclick_id ?? '',
                        $lead->sourcing_price ? number_format($lead->sourcing_price, 2, '.', '') : '',
                        $lead->notes ?? '',
                        $lead->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Download Sample CSV template for lead import.
     */
    public function sampleCsv(): StreamedResponse
    {
        $filename = 'leads_import_sample_template.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'name',
                'phone',
                'email',
                'city',
                'enquiry_name',
                'source',
                'property_type',
                'configuration',
                'budget_min',
                'budget_max',
                'preferred_location',
                'notes'
            ]);

            // Sample rows
            fputcsv($handle, [
                'Rohit Sen',
                '9876543210',
                'rohit.sen@example.com',
                'Mumbai',
                'Emerald Heights 2BHK Enquiry',
                'Facebook Ads',
                'Apartment',
                '2 BHK',
                '7500000',
                '12000000',
                'Bandra West',
                'Interested in immediate possession ready flats'
            ]);

            fputcsv($handle, [
                'Pooja Nair',
                '9811224455',
                'pooja.n@example.com',
                'Bengaluru',
                'Silver Palm Villas Luxury Enquiry',
                'Google Search',
                'Villa',
                '4 BHK',
                '20000000',
                '35000000',
                'Whitefield',
                'Looking for luxury villa with private garden'
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Bulk Import Leads from CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'source_id' => 'nullable|exists:lead_sources,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->withErrors(['csv_file' => 'Unable to read the uploaded CSV file.']);
        }

        // Read header
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'The uploaded file is empty.']);
        }

        // Normalize header names (lowercase, trim, strip BOM)
        $cleanHeader = [];
        foreach ($header as $col) {
            $colClean = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strtolower($col)));
            $colClean = str_replace(' ', '_', $colClean);
            $cleanHeader[] = $colClean;
        }

        $defaultStatus = LeadStatus::where('slug', 'new')->first();
        $defaultSource = LeadSource::where('code', 'walk_in')->first();
        $defaultSourceId = $request->input('source_id') ?? $defaultSource?->id ?? 1;

        $importedCount = 0;
        $skippedCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) {
                continue; // Skip empty lines
            }

            $rowData = [];
            foreach ($cleanHeader as $index => $colName) {
                $rowData[$colName] = isset($row[$index]) ? trim($row[$index]) : null;
            }

            $name = $rowData['name'] ?? $rowData['customer_name'] ?? 'Unknown Customer';
            $phone = preg_replace('/[^0-9]/', '', $rowData['phone'] ?? $rowData['mobile'] ?? '');

            if (empty($phone) || strlen($phone) < 8) {
                $skippedCount++;
                continue;
            }

            // Enquiry name / project name
            $enquiryName = $rowData['enquiry_name'] ?? $rowData['project_name'] ?? $rowData['project'] ?? null;
            $sourceVal = $rowData['source'] ?? $rowData['lead_source'] ?? null;

            // Find project if enquiry_name matches existing project
            $projectId = null;
            if (!empty($enquiryName)) {
                $proj = Project::where('name', 'like', "%{$enquiryName}%")->first();
                $projectId = $proj?->id;
            }

            // Assignment
            $assignedTo = $request->input('assigned_to');
            if (empty($assignedTo)) {
                // Round robin
                $agent = User::where('role', 'sales_agent')
                    ->where('status', 'active')
                    ->withCount('assignedLeads')
                    ->orderBy('assigned_leads_count', 'asc')
                    ->first();
                $assignedTo = $agent?->id;
            }

            $lead = Lead::create([
                'name' => $name,
                'phone' => $phone,
                'email' => $rowData['email'] ?? null,
                'city' => $rowData['city'] ?? null,
                'enquiry_name' => $enquiryName,
                'source' => $sourceVal ?? (LeadSource::find($defaultSourceId)?->name ?? 'Walk-in'),
                'property_type' => $rowData['property_type'] ?? 'Apartment',
                'configuration' => $rowData['configuration'] ?? null,
                'budget_min' => is_numeric($rowData['budget_min'] ?? null) ? $rowData['budget_min'] : null,
                'budget_max' => is_numeric($rowData['budget_max'] ?? null) ? $rowData['budget_max'] : null,
                'preferred_location' => $rowData['preferred_location'] ?? null,
                'project_id' => $projectId,
                'source_id' => $defaultSourceId,
                'status_id' => $defaultStatus?->id,
                'assigned_to' => $assignedTo,
                'created_by' => Auth::id(),
                'notes' => $rowData['notes'] ?? $rowData['description'] ?? 'Imported via CSV batch',
            ]);

            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'activity_type' => 'created',
                'description' => "Imported via CSV file. Assigned to " . ($lead->assignedAgent ? $lead->assignedAgent->name : 'Unassigned'),
            ]);

            $importedCount++;
        }

        fclose($handle);

        $msg = "Successfully imported {$importedCount} leads!";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} rows skipped due to missing/invalid phone number).";
        }

        return redirect()->route('leads.index')->with('success', $msg);
    }
}
