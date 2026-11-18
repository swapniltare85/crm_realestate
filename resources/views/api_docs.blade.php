@extends('layouts.app')

@section('title', 'REST API & Webhooks')
@section('page-title', 'REST API & Webhook Documentation')
@section('page-subtitle', 'Connect Meta Lead Ads, Website Forms, Google Ads, or Custom Mobile Apps')

@section('content')
<div class="row g-4">
    <!-- Webhook Ingestion Info Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm p-4 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 position-relative" style="z-index: 2;">
                <div>
                    <span class="badge bg-white text-primary mb-2 fw-bold px-3 py-1 rounded-pill" style="font-size: 0.75rem;">
                        <i class="bi bi-broadcast me-1"></i> PUBLIC INGESTION WEBHOOK
                    </span>
                    <h3 class="fw-bold mb-1 font-monospace" style="letter-spacing: -0.5px;">POST /api/v1/leads/webhook</h3>
                    <p class="mb-0 text-white-50 small" style="max-width: 700px;">
                        Use this endpoint to instantly insert incoming buyer leads from Landing Pages, Facebook & Instagram Lead Ads, Google Search Forms, or Zapier/Pabbly.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success text-white fs-6 px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> Auto Round-Robin Active
                    </span>
                </div>
            </div>
            <div style="position: absolute; right: -40px; top: -40px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
        </div>
    </div>

    <!-- cURL / JSON Request Example -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-terminal-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Example 1: Send Lead via cURL / JSON</h6>
                        <span class="text-muted small">Compatible with Python, Node.js, PHP, or Zapier</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 pt-2">
                <pre class="bg-dark text-light p-3 rounded-4 small mb-3" style="max-height: 380px; overflow-y: auto; font-size: 0.8rem; line-height: 1.5;"><code>curl -X POST http://localhost:8000/api/v1/leads/webhook \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "enquiry_name": "Emerald Heights Sea View Enquiry",
    "source": "Website Landing Page",
    "sub_project_name": "Tower A",
    "name": "Manish Sharma",
    "phone": "9812345678",
    "email": "manish@example.com",
    "description": "Interested in 3 BHK higher floor with sea view",
    "phone_country_code": "+91",
    "ip_country_code": "IN",
    "ip_address": "103.21.124.5",
    "physical_location": "Bandra West, Mumbai",
    "gclick_id": "CjwKCAjw7_xlBhBSEiwAEBO35_example_gclick_id",
    "utm_source": "google",
    "utm_medium": "cpc",
    "utm_campaign": "mumbai_luxury_flats_search",
    "utm_term": "buy 3 bhk in bandra",
    "utm_content": "headline_sea_view",
    "ads_keyword": "flats in bandra west",
    "keyword_type": "phrase",
    "device": "mobile",
    "platform": "google_ads",
    "source_key": "web_landing_page",
    "sub_source": "sem",
    "ads_placement": "search_top",
    "ads_position": "1.1",
    "url": "https://yoursite.com/emerald-heights?utm_source=google",
    "campaign_id": "987654321",
    "campaign_name": "Mumbai High-Intent Search 2026",
    "ads_id": "11223344",
    "ads_name": "Sea Facing 3BHK Ad",
    "ads_group_id": "55667788",
    "ads_set_name": "Luxury Buyers 35-55",
    "ads_extension_id": "990011",
    "ad_strategy": "Maximize Conversions",
    "sourcing_price": 285.50,
    "is_organic": false
  }'</code></pre>

                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Response (201 Created):</h6>
                <pre class="bg-light p-3 rounded-3 small text-dark border mb-0" style="font-size: 0.8rem;"><code>{
  "success": true,
  "message": "Lead created successfully",
  "data": {
    "id": 5,
    "name": "Manish Sharma",
    "phone": "9812345678",
    "status": { "name": "New Lead", "slug": "new" },
    "assigned_agent": { "name": "Rohan Sharma" }
  }
}</code></pre>
            </div>
        </div>
    </div>

    <!-- Website HTML Form Example -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-code-slash"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Example 2: Website Contact Form Code</h6>
                        <span class="text-muted small">Drop this form directly into landing pages</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 pt-2">
                <pre class="bg-dark text-light p-3 rounded-4 small mb-3" style="font-size: 0.82rem; line-height: 1.6;"><code>&lt;!-- Direct Ingestion Contact Form --&gt;
&lt;form action="http://localhost:8000/api/v1/leads/webhook" method="POST"&gt;
  &lt;input type="text" name="name" placeholder="Your Name" required /&gt;
  &lt;input type="tel" name="phone" placeholder="Mobile Number" required /&gt;
  &lt;input type="email" name="email" placeholder="Email Address" /&gt;
  
  &lt;select name="configuration"&gt;
    &lt;option value="2 BHK"&gt;2 BHK&lt;/option&gt;
    &lt;option value="3 BHK"&gt;3 BHK&lt;/option&gt;
  &lt;/select&gt;

  &lt;!-- Pass Source ID for Website (e.g. 6 = Website Enquiry) --&gt;
  &lt;input type="hidden" name="source_id" value="6" /&gt;

  &lt;button type="submit"&gt;Book Site Visit&lt;/button&gt;
&lt;/form&gt;</code></pre>

                <div class="alert alert-info small mt-3 mb-0 rounded-3 border-0 shadow-sm" style="background-color: #f0f9ff; border-left: 4px solid #0284c7 !important;">
                    <i class="bi bi-info-circle-fill me-1 text-info"></i>
                    <strong>Auto Round-Robin:</strong> Any lead coming through this API endpoint is automatically assigned to active sales executives in balanced rotation.
                </div>
            </div>
        </div>
    </div>

    <!-- Full Endpoints Reference Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                <h6 class="fw-bold mb-0 text-dark">Complete REST API Endpoints Reference</h6>
                <span class="text-muted small">Standard RESTful routes for mobile applications or external portals</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.6px;">
                        <tr>
                            <th class="ps-4">Method</th>
                            <th>Endpoint Route</th>
                            <th>Description</th>
                            <th class="pe-4">Authentication</th>
                        </tr>
                    </thead>
                    <tbody class="small font-monospace">
                        <tr>
                            <td class="ps-4"><span class="badge bg-success px-2 py-1">POST</span></td>
                            <td class="fw-bold text-dark">/api/v1/auth/login</td>
                            <td class="font-sans">Email & Password login; returns Sanctum Bearer token</td>
                            <td class="pe-4"><span class="badge bg-light text-dark border">Public</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><span class="badge bg-success px-2 py-1">POST</span></td>
                            <td class="fw-bold text-dark">/api/v1/leads/webhook</td>
                            <td class="font-sans">Public ingestion endpoint for external landing forms / Meta Ads</td>
                            <td class="pe-4"><span class="badge bg-light text-dark border">Public</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><span class="badge bg-primary px-2 py-1">GET</span></td>
                            <td class="fw-bold text-dark">/api/v1/leads</td>
                            <td class="font-sans">List leads with filters (?status_id=1&search=Bandra)</td>
                            <td class="pe-4"><span class="badge bg-primary-subtle text-primary border">Bearer Token</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><span class="badge bg-success px-2 py-1">POST</span></td>
                            <td class="fw-bold text-dark">/api/v1/leads</td>
                            <td class="font-sans">Create a new customer lead</td>
                            <td class="pe-4"><span class="badge bg-primary-subtle text-primary border">Bearer Token</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><span class="badge bg-primary px-2 py-1">GET</span></td>
                            <td class="fw-bold text-dark">/api/v1/leads/{id}</td>
                            <td class="font-sans">Get single lead details with followups & site visits</td>
                            <td class="pe-4"><span class="badge bg-primary-subtle text-primary border">Bearer Token</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><span class="badge bg-warning text-dark px-2 py-1">PUT</span></td>
                            <td class="fw-bold text-dark">/api/v1/leads/{id}</td>
                            <td class="font-sans">Update lead details, pipeline status, or reassign agent</td>
                            <td class="pe-4"><span class="badge bg-primary-subtle text-primary border">Bearer Token</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><span class="badge bg-success px-2 py-1">POST</span></td>
                            <td class="fw-bold text-dark">/api/v1/leads/{id}/followups</td>
                            <td class="font-sans">Log call / meeting with client and set next reminder</td>
                            <td class="pe-4"><span class="badge bg-primary-subtle text-primary border">Bearer Token</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><span class="badge bg-success px-2 py-1">POST</span></td>
                            <td class="fw-bold text-dark">/api/v1/leads/{id}/site-visits</td>
                            <td class="font-sans">Schedule property site visit and cab assistance</td>
                            <td class="pe-4"><span class="badge bg-primary-subtle text-primary border">Bearer Token</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><span class="badge bg-primary px-2 py-1">GET</span></td>
                            <td class="fw-bold text-dark">/api/v1/projects</td>
                            <td class="font-sans">List all real estate developments and starting prices</td>
                            <td class="pe-4"><span class="badge bg-light text-dark border">Public</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><span class="badge bg-primary px-2 py-1">GET</span></td>
                            <td class="fw-bold text-dark">/api/v1/lead-sources</td>
                            <td class="font-sans">List lead sources (Meta Ads, Google, 99acres, etc.)</td>
                            <td class="pe-4"><span class="badge bg-light text-dark border">Public</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><span class="badge bg-primary px-2 py-1">GET</span></td>
                            <td class="fw-bold text-dark">/api/v1/lead-statuses</td>
                            <td class="font-sans">List lead statuses and color codes</td>
                            <td class="pe-4"><span class="badge bg-light text-dark border">Public</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
