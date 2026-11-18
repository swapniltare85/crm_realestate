<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::latest('id')->get();
        return response()->json(['success' => true, 'data' => $projects]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'builder_name' => 'nullable|string|max:255',
            'property_type' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'price_starting' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'status' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $project = Project::create($data);
        return response()->json(['success' => true, 'data' => $project], 201);
    }
}
