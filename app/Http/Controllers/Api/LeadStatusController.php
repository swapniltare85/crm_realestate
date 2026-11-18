<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeadStatus;
use Illuminate\Http\Request;

class LeadStatusController extends Controller
{
    public function index()
    {
        $statuses = LeadStatus::where('is_active', true)->orderBy('order', 'asc')->get();
        return response()->json(['success' => true, 'data' => $statuses]);
    }
}
