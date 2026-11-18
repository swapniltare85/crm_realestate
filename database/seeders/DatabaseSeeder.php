<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Project;
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
                'description' => "Lead added from source: {$lead->source->name}",
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
    }
}
