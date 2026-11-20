<?php

namespace Database\Seeders;

use App\Models\AgentCommission;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\PaymentMilestone;
use App\Models\Project;
use App\Models\ProjectUnit;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@realestate.com'],
            [
                'name' => 'Admin Manager',
                'phone' => '9876543210',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('admin123'),
            ]
        );

        $agent1 = User::firstOrCreate(
            ['email' => 'rohan@realestate.com'],
            [
                'name' => 'Rohan Sharma',
                'phone' => '9822011223',
                'role' => 'sales_agent',
                'status' => 'active',
                'password' => Hash::make('agent123'),
            ]
        );

        $agent2 = User::firstOrCreate(
            ['email' => 'priya@realestate.com'],
            [
                'name' => 'Priya Patel',
                'phone' => '9833044556',
                'role' => 'sales_agent',
                'status' => 'active',
                'password' => Hash::make('agent123'),
            ]
        );

        // 2. Create Lead Sources
        $sources = [
            ['name' => 'Meta Ads (FB/Insta)', 'code' => 'meta_ads'],
            ['name' => 'Google Search Ads', 'code' => 'google_ads'],
            ['name' => 'MagicBricks', 'code' => 'magicbricks'],
            ['name' => '99acres', 'code' => '99acres'],
            ['name' => 'Housing.com', 'code' => 'housing_com'],
            ['name' => 'Website Enquiry', 'code' => 'website_form'],
            ['name' => 'Walk-in Office', 'code' => 'walk_in'],
            ['name' => 'Client Referral', 'code' => 'referral'],
        ];

        foreach ($sources as $s) {
            LeadSource::firstOrCreate(['code' => $s['code']], ['name' => $s['name'], 'is_active' => true]);
        }

        // 3. Create Lead Statuses
        $statuses = [
            ['name' => 'New Lead', 'slug' => 'new', 'color' => '#0d6efd', 'order' => 1],
            ['name' => 'Contacted / Attempted', 'slug' => 'contacted', 'color' => '#6c757d', 'order' => 2],
            ['name' => 'Follow Up Required', 'slug' => 'follow_up', 'color' => '#fd7e14', 'order' => 3],
            ['name' => 'Site Visit Scheduled', 'slug' => 'visit_scheduled', 'color' => '#0dcaf0', 'order' => 4],
            ['name' => 'Site Visit Completed', 'slug' => 'visit_completed', 'color' => '#6610f2', 'order' => 5],
            ['name' => 'Negotiation', 'slug' => 'negotiation', 'color' => '#ffc107', 'order' => 6],
            ['name' => 'Booked / Won', 'slug' => 'won', 'color' => '#198754', 'order' => 7],
            ['name' => 'Lost / Junk', 'slug' => 'lost', 'color' => '#dc3545', 'order' => 8],
        ];

        foreach ($statuses as $st) {
            LeadStatus::firstOrCreate(['slug' => $st['slug']], $st);
        }

        // 4. Create Sample Real Estate Projects
        $projects = [
            [
                'name' => 'Emerald Heights',
                'builder_name' => 'Sun Realty Group',
                'property_type' => 'Apartment',
                'location' => 'Bandra West',
                'city' => 'Mumbai',
                'price_starting' => 12500000, // 1.25 Cr
                'price_max' => 28000000,
                'status' => 'Ongoing',
                'description' => 'Luxury 2 & 3 BHK sea-facing apartments with modern amenities and clubhouse.'
            ],
            [
                'name' => 'Silver Palm Villas',
                'builder_name' => 'Greenfield Developers',
                'property_type' => 'Villa',
                'location' => 'Whitefield',
                'city' => 'Bengaluru',
                'price_starting' => 21000000, // 2.1 Cr
                'price_max' => 45000000,
                'status' => 'Ready to Move',
                'description' => 'Premium 4 BHK gated community villas with private pool and garden.'
            ],
            [
                'name' => 'Orchid Business Hub',
                'builder_name' => 'Skyline Builders',
                'property_type' => 'Commercial',
                'location' => 'Cyber City',
                'city' => 'Gurugram',
                'price_starting' => 8500000,
                'price_max' => 35000000,
                'status' => 'Upcoming',
                'description' => 'Grade-A IT/commercial office spaces with multi-level parking.'
            ]
        ];

        $createdProjects = [];
        foreach ($projects as $proj) {
            $createdProjects[] = Project::firstOrCreate(['name' => $proj['name']], $proj);
        }

        // 5. Create Sample Leads
        $srcMeta = LeadSource::where('code', 'meta_ads')->first();
        $srcGoogle = LeadSource::where('code', 'google_ads')->first();
        $srcMb = LeadSource::where('code', 'magicbricks')->first();

        $stNew = LeadStatus::where('slug', 'new')->first();
        $stFollowup = LeadStatus::where('slug', 'follow_up')->first();
        $stVisit = LeadStatus::where('slug', 'visit_scheduled')->first();
        $stWon = LeadStatus::where('slug', 'won')->first();

        $sampleLeads = [
            [
                'name' => 'Amitabh Saxena',
                'phone' => '9820198201',
                'email' => 'amitabh.saxena@example.com',
                'city' => 'Mumbai',
                'property_type' => 'Apartment',
                'configuration' => '3 BHK',
                'budget_min' => 12000000,
                'budget_max' => 18000000,
                'preferred_location' => 'Bandra / Khar',
                'source_id' => $srcMeta->id,
                'status_id' => $stVisit->id,
                'project_id' => $createdProjects[0]->id,
                'assigned_to' => $agent1->id,
                'created_by' => $admin->id,
                'next_follow_up_date' => now()->addDays(1)->setTime(11, 0),
                'is_hot' => true,
                'notes' => 'Customer is very interested in sea-facing 3 BHK. Site visit scheduled for tomorrow.',
            ],
            [
                'name' => 'Rajesh Verma',
                'phone' => '9870023456',
                'email' => 'rajesh.verma@example.com',
                'city' => 'Bengaluru',
                'property_type' => 'Villa',
                'configuration' => '4 BHK',
                'budget_min' => 20000000,
                'budget_max' => 30000000,
                'preferred_location' => 'Whitefield',
                'source_id' => $srcGoogle->id,
                'status_id' => $stFollowup->id,
                'project_id' => $createdProjects[1]->id,
                'assigned_to' => $agent2->id,
                'created_by' => $admin->id,
                'next_follow_up_date' => now()->addDays(2)->setTime(16, 30),
                'is_hot' => false,
                'notes' => 'Looking for immediate possession villa. Budget flexible.',
            ],
            [
                'name' => 'Neha Kapoor',
                'phone' => '9911223344',
                'email' => 'neha.k@example.com',
                'city' => 'Gurugram',
                'property_type' => 'Commercial',
                'configuration' => 'Office Space',
                'budget_min' => 8000000,
                'budget_max' => 12000000,
                'preferred_location' => 'Cyber City',
                'source_id' => $srcMb->id,
                'status_id' => $stNew->id,
                'project_id' => $createdProjects[2]->id,
                'assigned_to' => $agent1->id,
                'created_by' => $admin->id,
                'next_follow_up_date' => now()->addHours(3),
                'is_hot' => true,
                'notes' => 'Just received from MagicBricks. Needs to be called today.',
            ],
            [
                'name' => 'Vikram Singhania',
                'phone' => '9819988776',
                'email' => 'vikram.s@example.com',
                'city' => 'Mumbai',
                'property_type' => 'Apartment',
                'configuration' => '2 BHK',
                'budget_min' => 12000000,
                'budget_max' => 15000000,
                'preferred_location' => 'Bandra West',
                'source_id' => $srcMeta->id,
                'status_id' => $stWon->id,
                'project_id' => $createdProjects[0]->id,
                'assigned_to' => $agent1->id,
                'created_by' => $admin->id,
                'next_follow_up_date' => null,
                'is_hot' => false,
                'notes' => 'Token amount received! Flat Unit 702 Booked.',
            ]
        ];

        foreach ($sampleLeads as $leadData) {
            $lead = Lead::create($leadData);

            // Add an initial activity log
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $admin->id,
                'activity_type' => 'created',
                'description' => "Lead added from source: " . ($lead->leadSource?->name ?? 'Direct'),
            ]);

            // Add a follow-up for contacted leads
            if ($lead->status_id != $stNew->id) {
                LeadFollowup::create([
                    'lead_id' => $lead->id,
                    'user_id' => $lead->assigned_to,
                    'follow_up_type' => 'call',
                    'call_response' => 'Interested',
                    'notes' => 'Discussed requirements, sent project brochure on WhatsApp.',
                    'follow_up_date' => now()->subDay(),
                    'next_follow_up_date' => $lead->next_follow_up_date,
                    'status' => 'completed',
                ]);
            }

            // Add a site visit if scheduled
            if ($lead->status_id == $stVisit->id) {
                SiteVisit::create([
                    'lead_id' => $lead->id,
                    'project_id' => $lead->project_id,
                    'assigned_to' => $lead->assigned_to,
                    'visit_date' => now()->addDays(1)->setTime(11, 0),
                    'status' => 'scheduled',
                    'cab_required' => true,
                    'client_feedback' => null,
                ]);
            }
        }

        // 6. Create Sample Units for Emerald Heights
        $emerald = $createdProjects[0];
        $unit101 = ProjectUnit::firstOrCreate(
            ['project_id' => $emerald->id, 'unit_number' => '101'],
            ['tower' => 'Wing A', 'floor' => 1, 'configuration' => '2 BHK', 'carpet_area' => 850, 'base_price' => 13500000, 'status' => 'available']
        );
        $unit102 = ProjectUnit::firstOrCreate(
            ['project_id' => $emerald->id, 'unit_number' => '102'],
            ['tower' => 'Wing A', 'floor' => 1, 'configuration' => '2 BHK', 'carpet_area' => 880, 'base_price' => 14000000, 'status' => 'available']
        );
        $unit702 = ProjectUnit::firstOrCreate(
            ['project_id' => $emerald->id, 'unit_number' => '702'],
            ['tower' => 'Wing A', 'floor' => 7, 'configuration' => '3 BHK', 'carpet_area' => 1350, 'base_price' => 18500000, 'status' => 'sold']
        );
        $unit1204 = ProjectUnit::firstOrCreate(
            ['project_id' => $emerald->id, 'unit_number' => '1204'],
            ['tower' => 'Wing B', 'floor' => 12, 'configuration' => '4 BHK Penthouse', 'carpet_area' => 2200, 'base_price' => 28000000, 'status' => 'available']
        );

        // 7. Seed Sample Confirmed Booking for Won Lead (Vikram Singhania)
        $wonLead = Lead::where('phone', '9819988776')->first();
        if ($wonLead) {
            $booking = Booking::firstOrCreate(
                ['booking_reference' => 'BK-2026-0001'],
                [
                    'lead_id' => $wonLead->id,
                    'project_id' => $emerald->id,
                    'unit_id' => $unit702->id,
                    'agent_id' => $agent1->id,
                    'customer_name' => $wonLead->name,
                    'customer_phone' => $wonLead->phone,
                    'customer_email' => $wonLead->email,
                    'agreement_value' => 18500000,
                    'token_amount' => 500000,
                    'booking_date' => now()->subDays(10)->toDateString(),
                    'status' => 'confirmed',
                    'payment_status' => 'partially_paid',
                    'notes' => 'Token received via HDFC Cheque #492810. Stamp duty and registration in progress.',
                ]
            );

            // Link unit to booking
            $unit702->update([
                'status' => 'sold',
                'booked_lead_id' => $wonLead->id,
                'token_amount' => 500000,
                'booked_at' => now()->subDays(10),
            ]);

            // Seed construction milestones
            $milestones = [
                ['name' => '1. Booking & Token Amount (10%)', 'pct' => 10, 'amount' => 1850000, 'status' => 'paid', 'due' => now()->subDays(10), 'paid' => now()->subDays(10), 'ref' => 'HDFC Cheque #492810', 'method' => 'Cheque'],
                ['name' => '2. Agreement & Registration (10%)', 'pct' => 10, 'amount' => 1850000, 'status' => 'due', 'due' => now()->addDays(15), 'paid' => null, 'ref' => null, 'method' => null],
                ['name' => '3. Plinth Level Completion (20%)', 'pct' => 20, 'amount' => 3700000, 'status' => 'pending', 'due' => now()->addDays(75), 'paid' => null, 'ref' => null, 'method' => null],
                ['name' => '4. RCC Slabs & Structure (25%)', 'pct' => 25, 'amount' => 4625000, 'status' => 'pending', 'due' => now()->addDays(165), 'paid' => null, 'ref' => null, 'method' => null],
                ['name' => '5. Brickwork, Flooring & Finishing (20%)', 'pct' => 20, 'amount' => 3700000, 'status' => 'pending', 'due' => now()->addDays(255), 'paid' => null, 'ref' => null, 'method' => null],
                ['name' => '6. Final Possession & Key Handover (15%)', 'pct' => 15, 'amount' => 2775000, 'status' => 'pending', 'due' => now()->addDays(350), 'paid' => null, 'ref' => null, 'method' => null],
            ];

            foreach ($milestones as $i => $m) {
                PaymentMilestone::firstOrCreate(
                    ['booking_id' => $booking->id, 'stage_order' => $i + 1],
                    [
                        'milestone_name' => $m['name'],
                        'percentage' => $m['pct'],
                        'amount' => $m['amount'],
                        'due_date' => $m['due'],
                        'paid_date' => $m['paid'],
                        'status' => $m['status'],
                        'payment_method' => $m['method'],
                        'transaction_reference' => $m['ref'],
                    ]
                );
            }

            // Seed Agent Commission
            AgentCommission::firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'agent_id' => $agent1->id,
                    'commission_type' => 'internal_agent',
                    'deal_value' => 18500000,
                    'commission_percentage' => 1.50,
                    'commission_amount' => 277500,
                    'tds_percentage' => 5.00,
                    'tds_amount' => 13875,
                    'net_payable' => 263625,
                    'status' => 'approved',
                    'approved_by' => $admin->id,
                    'approved_at' => now()->subDays(5),
                    'notes' => '1.5% deal incentive approved for Wing A Unit 702 closure.',
                ]
            );
        }
    }
}
