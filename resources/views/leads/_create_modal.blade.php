@php
    $modalSources = \App\Models\LeadSource::where('is_active', true)->get();
    $modalStatuses = \App\Models\LeadStatus::where('is_active', true)->orderBy('order')->get();
    $modalProjects = \App\Models\Project::all();
    $modalAgents = \App\Models\User::where('status', 'active')->get();
@endphp

<div class="modal fade" id="createLeadModal" tabindex="-1" aria-labelledby="createLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded bg-primary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.85rem;">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <h6 class="modal-title fw-semibold text-dark mb-0" id="createLeadModalLabel">Add New Real Estate Lead</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('leads.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Client Contact Details -->
                        <div class="col-12">
                            <span class="text-muted fw-bold small text-uppercase d-block border-bottom pb-1" style="font-size: 0.6875rem; letter-spacing: 0.06em;">
                                1. Contact Information
                            </span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Customer Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Rahul Sharma" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Mobile / WhatsApp No. <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control form-control-sm" placeholder="e.g. 9876543210" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Alternate Contact No.</label>
                            <input type="tel" name="alt_phone" class="form-control form-control-sm" placeholder="e.g. 9123456789">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="e.g. rahul@example.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">City / Location</label>
                            <input type="text" name="city" class="form-control form-control-sm" placeholder="e.g. Mumbai, Pune, Delhi">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Lead Source</label>
                            <select name="source_id" class="form-select form-select-sm">
                                <option value="">Select Category</option>
                                @foreach($modalSources as $source)
                                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-semibold text-secondary mb-1">Campaign / Platform Name</label>
                            <input type="text" name="source" class="form-control form-control-sm" placeholder="e.g. Website Form, FB Lead Ad, MagicBricks Campaign">
                        </div>

                        <!-- Property Requirement Details -->
                        <div class="col-12 mt-3">
                            <span class="text-muted fw-bold small text-uppercase d-block border-bottom pb-1" style="font-size: 0.6875rem; letter-spacing: 0.06em;">
                                2. Property Requirement
                            </span>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary mb-1">Enquiry Title / Property Reference</label>
                            <input type="text" name="enquiry_name" class="form-control form-control-sm" placeholder="e.g. Skyline Towers Luxury 3BHK Enquiry">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Property Type</label>
                            <select name="property_type" class="form-select form-select-sm">
                                <option value="Apartment">Apartment / Flat</option>
                                <option value="Villa">Villa / Row House</option>
                                <option value="Plot">Residential Plot</option>
                                <option value="Commercial">Commercial Office / Shop</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Configuration</label>
                            <select name="configuration" class="form-select form-select-sm">
                                <option value="1 BHK">1 BHK</option>
                                <option value="2 BHK" selected>2 BHK</option>
                                <option value="3 BHK">3 BHK</option>
                                <option value="4+ BHK">4+ BHK</option>
                                <option value="Penthouse">Penthouse</option>
                                <option value="Commercial">Office Space</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Mapped Project</label>
                            <select name="project_id" class="form-select form-select-sm">
                                <option value="">No Project Tagged</option>
                                @foreach($modalProjects as $proj)
                                    <option value="{{ $proj->id }}">{{ $proj->name }} ({{ $proj->city }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Min Budget (INR ₹)</label>
                            <input type="number" name="budget_min" class="form-control form-control-sm" placeholder="e.g. 5000000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Max Budget (INR ₹)</label>
                            <input type="number" name="budget_max" class="form-control form-control-sm" placeholder="e.g. 9000000">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary mb-1">Preferred Localities</label>
                            <input type="text" name="preferred_location" class="form-control form-control-sm" placeholder="e.g. Bandra West, Whitefield">
                        </div>

                        <!-- Feature 5: Smart Tags -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                <i class="bi bi-tags text-primary me-1"></i> Smart Tags (Comma-separated)
                            </label>
                            <input type="text" name="tags" class="form-control form-control-sm" placeholder="e.g. NRI, Investor, Immediate-Possession, High Networth">
                        </div>

                        <!-- Assignment & Pipeline -->
                        <div class="col-12 mt-3">
                            <span class="text-muted fw-bold small text-uppercase d-block border-bottom pb-1" style="font-size: 0.6875rem; letter-spacing: 0.06em;">
                                3. Assignment & Follow-up
                            </span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Assigned Sales Executive</label>
                            <select name="assigned_to" class="form-select form-select-sm">
                                <option value="">Auto-Assign (Round-Robin to Active Executives)</option>
                                @foreach($modalAgents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }} ({{ ucfirst(str_replace('_', ' ', $agent->role)) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Initial Status</label>
                            <select name="status_id" class="form-select form-select-sm">
                                @foreach($modalStatuses as $status)
                                    <option value="{{ $status->id }}" {{ $status->slug === 'new' ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">First Follow-up Date</label>
                            <input type="datetime-local" name="next_follow_up_date" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6 d-flex align-items-center mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_hot" id="modalIsHot" value="1">
                                <label class="form-check-label small fw-semibold text-danger" for="modalIsHot">
                                    <i class="bi bi-fire"></i> Mark as High-Priority (Hot Lead)
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary mb-1">Requirement Notes</label>
                            <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Timeline to buy, financing requirements, preferred amenities..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Lead Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
