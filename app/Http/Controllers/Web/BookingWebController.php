<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AgentCommission;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadStatus;
use App\Models\PaymentMilestone;
use App\Models\Project;
use App\Models\ProjectUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingWebController extends Controller
{
    /**
     * Display a listing of bookings with financial KPIs and filters.
     */
    public function index(Request $request)
    {
        // Auto-sync any leads marked Won that don't have bookings yet
        Booking::syncAllWonLeads();

        $user = Auth::user();
        $query = Booking::with(['project', 'unit', 'agent', 'lead', 'milestones']);

        // RBAC: Sales Agent sees only their own deals; Admin/Manager sees all
        if ($user->role === 'sales_agent') {
            $query->where('agent_id', $user->id);
        }

        // Filter: Project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: Payment Status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter: Search query
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhereHas('unit', function ($uq) use ($search) {
                      $uq->where('unit_number', 'like', "%{$search}%")
                         ->orWhere('tower', 'like', "%{$search}%");
                  });
            });
        }

        // Global KPI Stats (scoped to user role)
        $kpiQuery = Booking::query();
        if ($user->role === 'sales_agent') {
            $kpiQuery->where('agent_id', $user->id);
        }
        $totalBookings = (clone $kpiQuery)->where('status', '!=', 'cancelled')->count();
        $totalDealValue = (clone $kpiQuery)->where('status', '!=', 'cancelled')->sum('agreement_value');
        
        // Total collected from paid milestones
        $bookingIds = (clone $kpiQuery)->where('status', '!=', 'cancelled')->pluck('id');
        $totalCollected = PaymentMilestone::whereIn('booking_id', $bookingIds)->where('status', 'paid')->sum('amount');
        $totalPending = max(0, $totalDealValue - $totalCollected);

        $stats = [
            'total_bookings' => $totalBookings,
            'total_deal_value' => $totalDealValue,
            'total_collected' => $totalCollected,
            'total_pending' => $totalPending,
        ];

        $bookings = $query->latest('booking_date')->latest('id')->paginate(12)->withQueryString();
        $projects = Project::where('status', '!=', 'Inactive')->orderBy('name')->get();

        return view('bookings.index', compact('bookings', 'projects', 'stats'));
    }

    /**
     * Show booking creation form (pre-fills lead & project data if provided).
     */
    public function create(Request $request)
    {
        $prefilledLead = null;
        if ($request->filled('lead_id')) {
            $prefilledLead = Lead::with(['project', 'assignedAgent'])->find($request->lead_id);
        }

        $projects = Project::with(['units' => function($q) {
            $q->where('status', 'available')->orderBy('unit_number');
        }])->where('status', '!=', 'Inactive')->orderBy('name')->get();

        $agents = User::where('status', 'active')
            ->whereIn('role', ['admin', 'manager', 'sales_agent'])
            ->orderBy('name')
            ->get();

        return view('bookings.create', compact('projects', 'agents', 'prefilledLead'));
    }

    /**
     * Store new confirmed property booking, generate milestones & agent commission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'project_id' => 'required|exists:projects,id',
            'unit_id' => 'nullable|exists:project_units,id',
            'agent_id' => 'required|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:255',
            'agreement_value' => 'required|numeric|min:1',
            'token_amount' => 'required|numeric|min:0',
            'booking_date' => 'required|date',
            'commission_percentage' => 'nullable|numeric|min:0|max:50',
            'commission_type' => 'nullable|in:internal_agent,channel_partner_broker',
            'partner_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // 1. Generate unique human-readable booking reference (e.g. BK-2026-0001)
            $nextId = (Booking::max('id') ?? 0) + 1;
            $ref = 'BK-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // 2. Create Booking
            $booking = Booking::create([
                'booking_reference' => $ref,
                'lead_id' => $validated['lead_id'] ?? null,
                'project_id' => $validated['project_id'],
                'unit_id' => $validated['unit_id'] ?? null,
                'agent_id' => $validated['agent_id'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'agreement_value' => $validated['agreement_value'],
                'token_amount' => $validated['token_amount'],
                'booking_date' => $validated['booking_date'],
                'status' => 'confirmed',
                'payment_status' => ($validated['token_amount'] >= $validated['agreement_value']) ? 'fully_paid' : 'token_received',
                'notes' => $validated['notes'] ?? null,
            ]);

            // 3. Mark selected unit as Sold / Booked
            if (!empty($validated['unit_id'])) {
                ProjectUnit::where('id', $validated['unit_id'])->update([
                    'status' => 'sold',
                    'booked_lead_id' => $validated['lead_id'] ?? null,
                    'token_amount' => $validated['token_amount'],
                    'booked_at' => now(),
                    'notes' => "Booked under Ref: {$ref} for {$validated['customer_name']}",
                ]);
            }

            // 4. Update Lead to Won status and log activity
            if (!empty($validated['lead_id'])) {
                $lead = Lead::find($validated['lead_id']);
                if ($lead) {
                    $wonStatus = LeadStatus::where('slug', 'won')->first();
                    if ($wonStatus) {
                        $lead->update(['status_id' => $wonStatus->id]);
                    }

                    LeadActivity::create([
                        'lead_id' => $lead->id,
                        'user_id' => Auth::id() ?? $validated['agent_id'],
                        'activity_type' => 'status_changed',
                        'description' => "Deal Closed & Booked! Ref: {$ref}, Value: ₹" . number_format($validated['agreement_value']),
                    ]);
                }
            }

            // 5. Generate Standard 6-Stage Construction Payment Milestones
            $agreement = (float) $validated['agreement_value'];
            $token = (float) $validated['token_amount'];
            $bookingDate = $validated['booking_date'];

            $stages = [
                ['name' => '1. Booking & Token Amount (10%)', 'pct' => 10, 'days' => 0],
                ['name' => '2. Agreement & Registration (10%)', 'pct' => 10, 'days' => 30],
                ['name' => '3. Plinth Level Completion (20%)', 'pct' => 20, 'days' => 90],
                ['name' => '4. RCC Slabs & Structure (25%)', 'pct' => 25, 'days' => 180],
                ['name' => '5. Brickwork, Flooring & Finishing (20%)', 'pct' => 20, 'days' => 270],
                ['name' => '6. Final Possession & Key Handover (15%)', 'pct' => 15, 'days' => 365],
            ];

            foreach ($stages as $index => $stage) {
                $amount = round($agreement * ($stage['pct'] / 100), 2);
                $dueDate = date('Y-m-d', strtotime($bookingDate . " + {$stage['days']} days"));
                
                // First stage is considered paid if token is provided
                $isFirstStagePaid = ($index === 0 && $token > 0);
                $stageAmount = ($index === 0 && $token > 0) ? $token : $amount;

                PaymentMilestone::create([
                    'booking_id' => $booking->id,
                    'milestone_name' => $stage['name'],
                    'stage_order' => $index + 1,
                    'percentage' => $stage['pct'],
                    'amount' => $stageAmount,
                    'due_date' => $dueDate,
                    'paid_date' => $isFirstStagePaid ? $bookingDate : null,
                    'status' => $isFirstStagePaid ? 'paid' : ($index === 0 ? 'due' : 'pending'),
                    'payment_method' => $isFirstStagePaid ? 'Bank Transfer / Cheque' : null,
                    'transaction_reference' => $isFirstStagePaid ? 'Token Received' : null,
                    'notes' => $isFirstStagePaid ? 'Initial token advance recorded at booking.' : null,
                ]);
            }

            // 6. Generate Agent Commission & Incentive Record
            $commPct = !empty($validated['commission_percentage']) ? (float) $validated['commission_percentage'] : 1.50;
            $grossComm = round($agreement * ($commPct / 100), 2);
            $tdsPct = 5.00; // standard 5% TDS
            $tdsAmount = round($grossComm * ($tdsPct / 100), 2);
            $netPayable = max(0, $grossComm - $tdsAmount);

            AgentCommission::create([
                'booking_id' => $booking->id,
                'agent_id' => $validated['agent_id'],
                'commission_type' => $validated['commission_type'] ?? 'internal_agent',
                'partner_name' => $validated['partner_name'] ?? null,
                'deal_value' => $agreement,
                'commission_percentage' => $commPct,
                'commission_amount' => $grossComm,
                'tds_percentage' => $tdsPct,
                'tds_amount' => $tdsAmount,
                'net_payable' => $netPayable,
                'status' => 'pending_approval',
                'notes' => "Auto-calculated on {$commPct}% deal value for {$ref}.",
            ]);

            return redirect()->route('bookings.show', $booking->id)
                ->with('success', "Booking {$ref} created successfully! Milestones and Commission have been generated.");
        });
    }

    /**
     * Display detailed booking overview with milestone timeline and payment modal.
     */
    public function show($id)
    {
        $user = Auth::user();
        $booking = Booking::with(['project', 'unit', 'agent', 'lead', 'milestones', 'commissions.approver'])->findOrFail($id);

        // RBAC check
        if ($user->role === 'sales_agent' && (int) $booking->agent_id !== (int) $user->id) {
            abort(403, 'Unauthorized access to this booking.');
        }

        return view('bookings.show', compact('booking'));
    }

    /**
     * Update Booking Lifecycle Status.
     */
    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $request->validate([
            'status' => 'required|in:confirmed,under_registration,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $booking->update([
            'status' => $request->status,
            'notes' => $request->notes ? ($booking->notes . "\n" . now()->format('d M Y') . ": " . $request->notes) : $booking->notes,
        ]);

        return back()->with('success', "Booking status updated to " . ucfirst(str_replace('_', ' ', $request->status)));
    }

    /**
     * Record payment for a specific milestone.
     */
    public function recordMilestonePayment(Request $request, $id, $milestoneId)
    {
        $booking = Booking::findOrFail($id);
        $milestone = PaymentMilestone::where('booking_id', $booking->id)->findOrFail($milestoneId);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'paid_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $milestone->update([
            'amount' => $validated['amount'],
            'paid_date' => $validated['paid_date'],
            'payment_method' => $validated['payment_method'],
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'paid',
        ]);

        // Re-evaluate overall booking payment status
        $totalPaid = $booking->milestones()->where('status', 'paid')->sum('amount');
        if ($totalPaid >= $booking->agreement_value) {
            $booking->update(['payment_status' => 'fully_paid']);
        } elseif ($totalPaid > 0) {
            $booking->update(['payment_status' => 'partially_paid']);
        }

        return back()->with('success', "Payment recorded successfully for '{$milestone->milestone_name}'! Amount: ₹" . number_format($validated['amount']));
    }

    /**
     * Add custom milestone stage to booking schedule.
     */
    public function addCustomMilestone(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'milestone_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $nextOrder = ($booking->milestones()->max('stage_order') ?? 0) + 1;
        $pct = round(($validated['amount'] / $booking->agreement_value) * 100, 2);

        PaymentMilestone::create([
            'booking_id' => $booking->id,
            'milestone_name' => $validated['milestone_name'],
            'stage_order' => $nextOrder,
            'percentage' => $pct,
            'amount' => $validated['amount'],
            'due_date' => $validated['due_date'] ?? null,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "New custom payment milestone added successfully!");
    }

    /**
     * Display formal printable Real Estate Allotment Letter.
     */
    public function allotmentLetter($id)
    {
        $booking = Booking::with(['project', 'unit', 'agent', 'lead', 'milestones'])->findOrFail($id);
        return view('bookings.allotment_letter', compact('booking'));
    }

    /**
     * Display formal printable Payment Receipt.
     */
    public function receipt($id, $milestoneId = null)
    {
        $booking = Booking::with(['project', 'unit', 'agent'])->findOrFail($id);
        $milestone = $milestoneId ? PaymentMilestone::where('booking_id', $booking->id)->findOrFail($milestoneId) : null;
        return view('bookings.receipt', compact('booking', 'milestone'));
    }

    public function paymentReceipt($id, $milestoneId = null)
    {
        return $this->receipt($id, $milestoneId);
    }
}
