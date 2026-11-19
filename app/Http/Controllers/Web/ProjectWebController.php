<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectUnit;
use App\Models\Lead;
use Illuminate\Http\Request;

class ProjectWebController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::withCount(['leads', 'siteVisits', 'units'])->latest('id')->get();
        
        $selectedProjectId = $request->input('project_id', $projects->first()?->id);
        $selectedProject = Project::with(['units.bookedLead'])->find($selectedProjectId);
        
        $leads = Lead::select('id', 'name', 'phone')->latest('id')->get();

        return view('projects.index', compact('projects', 'selectedProject', 'leads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'builder_name' => 'nullable|string|max:255',
            'property_type' => 'required|string',
            'location' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'price_starting' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'status' => 'required|string',
            'description' => 'nullable|string',
        ]);

        Project::create($validated);

        return redirect()->back()->with('success', 'Project added successfully!');
    }

    public function updateUnitStatus(Request $request, $id)
    {
        $unit = ProjectUnit::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:available,blocked,sold',
            'booked_lead_id' => 'nullable|exists:leads,id',
            'token_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validated['status'] === 'available') {
            $validated['booked_lead_id'] = null;
            $validated['token_amount'] = null;
            $validated['booked_at'] = null;
        } else {
            $validated['booked_at'] = $unit->booked_at ?? now();
        }

        $unit->update($validated);

        return redirect()->back()->with('success', "Unit {$unit->unit_number} status updated to " . ucfirst($validated['status']) . "!");
    }
}
