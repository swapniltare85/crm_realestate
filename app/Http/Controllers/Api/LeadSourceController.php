<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeadSource;
use Illuminate\Http\Request;

class LeadSourceController extends Controller
{
    public function index()
    {
        $sources = LeadSource::where('is_active', true)->get();
        return response()->json(['success' => true, 'data' => $sources]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:100|unique:lead_sources,code',
        ]);

        $source = LeadSource::create($data);
        return response()->json(['success' => true, 'data' => $source], 201);
    }
}
