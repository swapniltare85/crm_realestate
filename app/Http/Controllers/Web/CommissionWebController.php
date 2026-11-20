<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AgentCommission;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionWebController extends Controller
{
    /**
     * Display agent commissions ledger with KPI metrics and approval actions.
     */
    public function index(Request $request)
    {
        // Auto-sync any leads marked Won that don't have bookings/commissions yet
        Booking::syncAllWonLeads();

        $user = Auth::user();
        $query = AgentCommission::with(['booking.project', 'booking.unit', 'agent', 'approver']);

        // RBAC: Sales Agent sees only their commissions; Admin/Manager sees all
        if ($user->role === 'sales_agent') {
            $query->where('agent_id', $user->id);
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: Agent (for Admin/Manager)
        if ($request->filled('agent_id') && ($user->isAdmin() || $user->isManager())) {
            $query->where('agent_id', $request->agent_id);
        }

        // Filter: Commission Type
        if ($request->filled('commission_type')) {
            $query->where('commission_type', $request->commission_type);
        }

        // KPI Stats
        $kpiQuery = AgentCommission::query();
        if ($user->role === 'sales_agent') {
            $kpiQuery->where('agent_id', $user->id);
        }

        $totalAccrued = (clone $kpiQuery)->where('status', '!=', 'cancelled')->sum('commission_amount');
        $totalApproved = (clone $kpiQuery)->where('status', 'approved')->sum('net_payable');
        $totalPaid = (clone $kpiQuery)->where('status', 'paid')->sum('net_payable');
        $pendingCount = (clone $kpiQuery)->where('status', 'pending_approval')->count();

        $stats = [
            'total_accrued' => $totalAccrued,
            'total_approved' => $totalApproved,
            'total_paid' => $totalPaid,
            'pending_count' => $pendingCount,
        ];

        $commissions = $query->latest('id')->paginate(15)->withQueryString();
        $agents = User::where('status', 'active')
            ->whereIn('role', ['admin', 'manager', 'sales_agent'])
            ->orderBy('name')
            ->get();

        return view('commissions.index', compact('commissions', 'agents', 'stats'));
    }

    /**
     * Approve commission payout (Admin / Manager only).
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Only Admins and Managers can approve commissions.');
        }

        $commission = AgentCommission::findOrFail($id);
        $commission->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'notes' => $request->notes ? ($commission->notes . "\nApproved: " . $request->notes) : $commission->notes,
        ]);

        return back()->with('success', "Commission of ₹" . number_format($commission->net_payable) . " approved successfully for " . ($commission->agent?->name ?? 'Agent') . "!");
    }

    /**
     * Mark commission as disbursed / paid with bank payment reference.
     */
    public function disburse(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Only Admins and Managers can disburse commissions.');
        }

        $validated = $request->validate([
            'payment_reference' => 'required|string|max:100',
            'paid_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $commission = AgentCommission::findOrFail($id);
        $commission->update([
            'status' => 'paid',
            'payment_reference' => $validated['payment_reference'],
            'paid_at' => $validated['paid_at'],
            'notes' => $validated['notes'] ? ($commission->notes . "\nDisbursed: " . $validated['notes']) : $commission->notes,
        ]);

        return back()->with('success', "Commission marked as Disbursed/Paid! Payment Ref: {$validated['payment_reference']}");
    }
}
