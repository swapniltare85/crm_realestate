@extends('layouts.app')

@section('title', 'Team & User Management')
@section('page-title', 'User & Team Management')
@section('page-subtitle', 'Manage sales agents, telecallers, and administrative team members')

@section('content')

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="card stat-card border-0 shadow-sm p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.8px;">Total Team</span>
                    <h2 class="fw-bold mb-0 mt-1 text-dark" style="letter-spacing: -0.5px;">{{ $totalUsers }}</h2>
                    <span class="text-muted small">Registered staff members</span>
                </div>
                <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1d4ed8;">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="card stat-card border-0 shadow-sm p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.8px;">Sales Executives</span>
                    <h2 class="fw-bold mb-0 mt-1 text-primary" style="letter-spacing: -0.5px;">{{ $totalAgents }}</h2>
                    <span class="text-info small fw-semibold"><i class="bi bi-lightning-charge me-1"></i>Receiving auto leads</span>
                </div>
                <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%); color: #0891b2;">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="card stat-card border-0 shadow-sm p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.8px;">Active Accounts</span>
                    <h2 class="fw-bold mb-0 mt-1 text-success" style="letter-spacing: -0.5px;">{{ $activeUsers }}</h2>
                    <span class="text-success small fw-semibold"><i class="bi bi-check2-circle me-1"></i>Available for calls</span>
                </div>
                <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #15803d;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header & Add User Trigger -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold mb-0 text-dark">Staff & Sales Directory</h5>
        <span class="text-muted small">Manage roles, round-robin assignments, and lead quotas</span>
    </div>
    <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus-fill"></i>
        <span>Add Team Member</span>
    </button>
</div>

<!-- Users Table Card -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.6px;">
                <tr>
                    <th class="ps-4" style="width: 50px;">#</th>
                    <th>Member Name</th>
                    <th>Contact Info</th>
                    <th>Role / Designation</th>
                    <th>Leads Assigned</th>
                    <th>Calls Logged</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td class="ps-4 text-muted small fw-bold">{{ $u->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle {{ $u->role === 'admin' ? 'bg-danger-subtle text-danger border border-danger-subtle' : ($u->role === 'sales_agent' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-secondary-subtle text-secondary border') }} d-flex align-items-center justify-content-center fw-bold small flex-shrink-0" style="width: 38px; height: 38px;">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="fw-bold text-dark d-block">{{ $u->name }}</span>
                                    @if($u->id === Auth::id())
                                        <span class="badge bg-light text-primary border" style="font-size: 0.68rem;">You (Active Session)</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark">{{ $u->email }}</div>
                            <div class="small text-muted" style="font-size: 0.78rem;">
                                <i class="bi bi-telephone text-muted me-1"></i> {{ $u->phone ?? 'Not set' }}
                            </div>
                        </td>
                        <td>
                            @if($u->role === 'admin')
                                <span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1" style="font-size: 0.75rem;">Administrator</span>
                            @elseif($u->role === 'manager')
                                <span class="badge bg-warning-subtle text-dark fw-bold px-2 py-1" style="font-size: 0.75rem;">Sales Manager</span>
                            @elseif($u->role === 'sales_agent')
                                <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" style="font-size: 0.75rem;">Sales Executive</span>
                            @else
                                <span class="badge bg-info-subtle text-info fw-bold px-2 py-1" style="font-size: 0.75rem;">Telecaller</span>
                            @endif
                        </td>
                        <td>
                            @if($u->assigned_leads_count > 0)
                                <a href="{{ route('leads.index', ['assigned_to' => $u->id]) }}" class="badge bg-primary-subtle text-primary text-decoration-none px-3 py-1 rounded-pill fw-bold" title="View Leads">
                                    <i class="bi bi-people me-1"></i> {{ $u->assigned_leads_count }} Leads
                                </a>
                            @else
                                <span class="text-muted small">0 Leads</span>
                            @endif
                        </td>
                        <td>
                            <span class="small fw-semibold text-dark">
                                <i class="bi bi-telephone-outbound text-primary me-1"></i> {{ $u->followups_count }} Calls
                            </span>
                        </td>
                        <td>
                            @if($u->status === 'active')
                                <span class="badge bg-success-subtle text-success px-2 py-1 fw-bold" style="font-size: 0.74rem;">
                                    ● Active
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fw-bold" style="font-size: 0.74rem;">
                                    ● Inactive
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border p-2" type="button" data-bs-toggle="dropdown" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                    <li>
                                        <button class="dropdown-item py-2" type="button" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $u->id }}">
                                            <i class="bi bi-pencil-square text-primary me-2"></i> Edit Details
                                        </button>
                                    </li>
                                    @if($u->id !== Auth::id())
                                        <li>
                                            <form action="{{ route('users.toggle-status', $u->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item py-2">
                                                    @if($u->status === 'active')
                                                        <i class="bi bi-pause-circle text-warning me-2"></i> Deactivate Account
                                                    @else
                                                        <i class="bi bi-play-circle text-success me-2"></i> Activate Account
                                                    @endif
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove {{ $u->name }} from the team?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger py-2">
                                                    <i class="bi bi-trash me-2"></i> Delete Member
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit User Modal -->
                    <div class="modal fade" id="editUserModal{{ $u->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-start">
                                <div class="modal-header bg-light border-0 p-4 pb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-3 bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                            <i class="bi bi-person-gear"></i>
                                        </div>
                                        <div>
                                            <h5 class="modal-title fw-bold text-dark mb-0">Edit Member - {{ $u->name }}</h5>
                                            <span class="text-muted small">Update profile and role settings</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('users.update', $u->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-secondary mb-1">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" value="{{ $u->name }}" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-secondary mb-1">Email Address <span class="text-danger">*</span></label>
                                                <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-secondary mb-1">Mobile Number</label>
                                                <input type="tel" name="phone" class="form-control" value="{{ $u->phone }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-secondary mb-1">Role / Designation</label>
                                                <select name="role" class="form-select" {{ $u->id === Auth::id() ? 'disabled' : '' }}>
                                                    <option value="sales_agent" {{ $u->role === 'sales_agent' ? 'selected' : '' }}>Sales Executive</option>
                                                    <option value="manager" {{ $u->role === 'manager' ? 'selected' : '' }}>Sales Manager</option>
                                                    <option value="telecaller" {{ $u->role === 'telecaller' ? 'selected' : '' }}>Telecaller</option>
                                                    <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Administrator</option>
                                                </select>
                                                @if($u->id === Auth::id())
                                                    <input type="hidden" name="role" value="admin">
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-secondary mb-1">Account Status</label>
                                                <select name="status" class="form-select" {{ $u->id === Auth::id() ? 'disabled' : '' }}>
                                                    <option value="active" {{ $u->status === 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ $u->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                                @if($u->id === Auth::id())
                                                    <input type="hidden" name="status" value="active">
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-secondary mb-1">Reset Password (Optional)</label>
                                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep same">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0 p-3 px-4">
                                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary px-4 fw-semibold">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add New Team Member -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0 p-4 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="addUserModalLabel">Add New Team Member</h5>
                        <span class="text-muted small">Create account and assign permissions</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1">Staff Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Vikas Gupta" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1">Email (Login ID) <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="vikas@realestate.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Mobile Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="9811223344">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="sales_agent" selected>Sales Executive</option>
                                <option value="manager">Sales Manager</option>
                                <option value="telecaller">Telecaller</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Initial Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active" selected>Active (Can take leads)</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-3 px-4">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-check-lg me-1"></i> Create Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
