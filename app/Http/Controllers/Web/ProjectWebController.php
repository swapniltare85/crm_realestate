<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectWebController extends Controller
{
    public function index()
    {
        $projects = Project::withCount(['leads', 'siteVisits'])->latest('id')->get();
        return view('projects.index', compact('projects'));
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
}
