@extends('layouts.app')

@section('title', 'Users Management')

@php
    $breadcrumbs = [
        ['title' => 'Users', 'url' => '#']
    ];
@endphp

@push('styles')
<style>
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.9) 0%, rgba(111, 66, 193, 0.9) 100%);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
    }
    
    .loading-overlay.show {
        display: flex;
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .loading-content {
        text-align: center;
        color: white;
        background: rgba(255, 255, 255, 0.1);
        padding: 40px 60px;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .loading-content .spinner-border {
        width: 4rem;
        height: 4rem;
        border-width: 0.4em;
        border-color: rgba(255, 255, 255, 0.3);
        border-right-color: white;
    }
    
    .loading-content p {
        margin-top: 20px;
        font-size: 18px;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    
    /* Spin Animation for Icon */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .spin-animation {
        display: inline-block;
        animation: spin 2s linear infinite;
    }
    
    /* Pulse Animation for Cloud Icon */
    @keyframes pulse {
        0%, 100% { 
            transform: scale(1);
            opacity: 1;
        }
        50% { 
            transform: scale(1.1);
            opacity: 0.8;
        }
    }
    
    .pulse-animation {
        display: inline-block;
        animation: pulse 2s ease-in-out infinite;
    }
    
    /* Gradient Move Animation for Progress Bar */
    @keyframes gradientMove {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    .sync-stat-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }
    
    .sync-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .sync-stat-card .card-body {
        padding: 20px;
    }
    
    .sync-stat-card h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .sync-stat-card small {
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .stat-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .stat-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    
    .error-table-container {
        max-height: 300px;
        overflow-y: auto;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    
    .error-table-container::-webkit-scrollbar {
        width: 8px;
    }
    
    .error-table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .error-table-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .error-table-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Dark Mode Support */
    [data-theme="dark"] .modal-content {
        background-color: var(--bg-card);
        color: var(--text-primary);
    }
    
    [data-theme="dark"] .modal-body h6,
    [data-theme="dark"] .modal-body p,
    [data-theme="dark"] .modal-body small {
        color: var(--text-primary);
    }
    
    [data-theme="dark"] .alert-info {
        background-color: rgba(13, 202, 240, 0.15);
        color: var(--text-primary);
        border: 1px solid rgba(13, 202, 240, 0.3);
    }
    
    [data-theme="dark"] .alert-warning {
        background-color: rgba(255, 193, 7, 0.15);
        color: var(--text-primary);
        border: 1px solid rgba(255, 193, 7, 0.3);
    }
    
    [data-theme="dark"] .alert-success {
        background-color: rgba(25, 135, 84, 0.15);
        color: var(--text-primary);
        border: 1px solid rgba(25, 135, 84, 0.3);
    }
    
    [data-theme="dark"] .alert-danger {
        background-color: rgba(220, 53, 69, 0.15);
        color: var(--text-primary);
        border: 1px solid rgba(220, 53, 69, 0.3);
    }
    
    [data-theme="dark"] .modal-footer {
        background-color: var(--bg-secondary);
        border-top: 1px solid var(--border-color);
    }
    
    [data-theme="dark"] .error-table-container {
        border-color: var(--border-color);
    }
    
    [data-theme="dark"] .table {
        color: var(--text-primary);
    }
    
    [data-theme="dark"] .table-light {
        background-color: var(--bg-secondary);
        color: var(--text-primary);
    }
    
    [data-theme="dark"] .table-hover tbody tr:hover {
        background-color: var(--bg-hover);
    }
    
    [data-theme="dark"] .error-table-container::-webkit-scrollbar-track {
        background: var(--bg-secondary);
    }
    
    [data-theme="dark"] .error-table-container::-webkit-scrollbar-thumb {
        background: var(--border-color);
    }
    
    [data-theme="dark"] .error-table-container::-webkit-scrollbar-thumb:hover {
        background: var(--text-secondary);
    }
    
    [data-theme="dark"] .text-muted {
        color: var(--text-secondary) !important;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people"></i> Users Management</span>
                <div>
                    @if(auth()->user()->hasPermission('users.edit'))
                        <button type="button" class="btn btn-success btn-sm me-2" id="syncAzureBtn">
                            <i class="bi bi-arrow-repeat"></i> Sync Azure
                        </button>
                    @endif
                    @if(auth()->user()->hasPermission('users.create'))
                        <a href="{{ route('users.create') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-plus-circle"></i> Add User
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6 ms-auto">
                        <form action="{{ route('users.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by name, email, phone..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>Brand</th>
                                <th>Dealer</th>
                                <th>Roles</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->full_name }}</td>
                                    <td>{{ $user->phone ?? '-' }}</td>
                                    <td>
                                        @forelse($user->brands as $brand)
                                            <span class="badge bg-info">{{ $brand->brand_name }}</span>
                                        @empty
                                            -
                                        @endforelse
                                    </td>
                                    <td>{{ $user->dealer->dealer_name ?? '-' }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-primary">{{ $role->role_name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if(auth()->user()->hasPermission('users.edit'))
                                            @if(!$user->hasRole('SUPERADMIN'))
                                                <a href="{{ route('users.edit', $user->unique_id) }}" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @else
                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot edit Super Admin user">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            @endif
                                        @endif
                                        @if(auth()->user()->hasPermission('users.delete'))
                                            @if(!$user->hasRole('SUPERADMIN') && !$user->hasRole('ADMIN') && $user->user_id !== auth()->id())
                                                <form action="{{ route('users.destroy', $user->unique_id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete Super Admin, Admin user or yourself">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No users found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="text-center text-md-start">
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} entries
                    </div>
                    <div>
                        {{ $users->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Syncing users from Azure AD...</p>
    </div>
</div>

<!-- Sync Confirmation/Loading Modal -->
<div class="modal fade" id="syncConfirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="syncConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <!-- Confirmation State -->
            <div id="confirmState">
                <div class="modal-header bg-gradient border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h5 class="modal-title fw-bold" id="syncConfirmModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i> Confirm Sync
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <i class="bi bi-cloud-arrow-down-fill text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <h6 class="text-center mb-3">Are you sure you want to sync users from Azure AD?</h6>
                    <div class="alert alert-info border-0 shadow-sm">
                        <small>
                            <i class="bi bi-info-circle-fill"></i> 
                            <strong>This action will:</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li>Fetch all users from Microsoft Azure Active Directory</li>
                                <li>Create new users that don't exist in the system</li>
                                <li>Update existing users with latest information</li>
                            </ul>
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmSyncBtn">
                        <i class="bi bi-check-circle"></i> Yes, Sync Now
                    </button>
                </div>
            </div>
            
            <!-- Loading State -->
            <div id="loadingState" style="display: none;">
                <div class="modal-header bg-gradient border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="bi bi-arrow-repeat spin-animation"></i> Syncing Users
                    </h5>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="sync-animation">
                            <i class="bi bi-cloud-arrow-down-fill text-primary pulse-animation" style="font-size: 5rem;"></i>
                        </div>
                    </div>
                    <h6 class="text-center mb-3">Please wait while we sync users from Azure AD...</h6>
                    
                    <!-- Animated Progress Bar -->
                    <div class="progress" style="height: 25px; border-radius: 12px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             id="syncProgressBar"
                             role="progressbar" 
                             aria-valuenow="0" 
                             aria-valuemin="0" 
                             aria-valuemax="100" 
                             style="width: 0%; background: #002856; transition: width 3s ease-in-out;">
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-hourglass-split"></i> This may take a few moments...
                        </small>
                    </div>
                    
                    <div class="alert alert-warning border-0 shadow-sm mt-3 mb-0">
                        <small>
                            <i class="bi bi-info-circle-fill"></i> 
                            <strong>Please do not close this window or refresh the page.</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sync Result Modal -->
<div class="modal fade" id="syncResultModal" tabindex="-1" aria-labelledby="syncResultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title fw-bold" id="syncResultModalLabel">
                    <i class="bi bi-cloud-arrow-down"></i> Azure AD Sync Result
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-success border-0 shadow-sm" id="syncSuccessAlert" style="display: none;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                        <div>
                            <strong>Sync Completed Successfully!</strong>
                            <p class="mb-0 mt-1 small">All users have been synchronized from Azure AD.</p>
                        </div>
                    </div>
                </div>
                <div class="alert alert-danger border-0 shadow-sm" id="syncErrorAlert" style="display: none;">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div class="flex-grow-1">
                            <strong>Sync Failed!</strong>
                            <p class="mb-0 mt-2" id="syncErrorMessage"></p>
                        </div>
                    </div>
                </div>
                
                <div id="syncStats" style="display: none;">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card sync-stat-card stat-primary">
                                <div class="card-body text-center">
                                    <h3 class="mb-0" id="totalSynced">0</h3>
                                    <small>Total Synced</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card sync-stat-card stat-success">
                                <div class="card-body text-center">
                                    <h3 class="mb-0" id="totalCreated">0</h3>
                                    <small>Created</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card sync-stat-card stat-info">
                                <div class="card-body text-center">
                                    <h3 class="mb-0" id="totalUpdated">0</h3>
                                    <small>Updated</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="errorSection" style="display: none;">
                        <div class="alert alert-warning border-0 shadow-sm">
                            <h6 class="mb-0">
                                <i class="bi bi-exclamation-circle-fill"></i> 
                                <strong>Errors Encountered:</strong> <span id="errorCount" class="badge bg-danger">0</span>
                            </h6>
                        </div>
                        <div class="error-table-container">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="35%">Email</th>
                                        <th width="65%">Error Message</th>
                                    </tr>
                                </thead>
                                <tbody id="errorTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
                <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Page
                </button>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const syncBtn = document.getElementById('syncAzureBtn');
        const confirmSyncBtn = document.getElementById('confirmSyncBtn');
        const syncConfirmModal = new bootstrap.Modal(document.getElementById('syncConfirmModal'));
        const syncResultModal = new bootstrap.Modal(document.getElementById('syncResultModal'));
        
        const confirmState = document.getElementById('confirmState');
        const loadingState = document.getElementById('loadingState');
        
        // Show confirmation modal when sync button is clicked
        if (syncBtn) {
            syncBtn.addEventListener('click', function() {
                // Reset to confirmation state
                confirmState.style.display = 'block';
                loadingState.style.display = 'none';
                syncConfirmModal.show();
            });
        }
        
        // Perform sync when confirmed
        if (confirmSyncBtn) {
            confirmSyncBtn.addEventListener('click', function() {
                // Switch to loading state
                confirmState.style.display = 'none';
                loadingState.style.display = 'block';
                
                // Animate progress bar to 100%
                const progressBar = document.getElementById('syncProgressBar');
                setTimeout(() => {
                    progressBar.style.width = '100%';
                    progressBar.setAttribute('aria-valuenow', '100');
                }, 100);
                
                // Disable backdrop click and keyboard
                const modalElement = document.getElementById('syncConfirmModal');
                modalElement.setAttribute('data-bs-backdrop', 'static');
                modalElement.setAttribute('data-bs-keyboard', 'false');
                
                // Disable sync button
                syncBtn.disabled = true;
                
                // Make AJAX request
                fetch('{{ route("users.sync.azure") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Hide sync modal
                    syncConfirmModal.hide();
                    
                    // Reset progress bar
                    progressBar.style.width = '0%';
                    progressBar.setAttribute('aria-valuenow', '0');
                    
                    // Enable sync button
                    syncBtn.disabled = false;
                    
                    // Reset modal backdrop settings
                    modalElement.removeAttribute('data-bs-backdrop');
                    modalElement.removeAttribute('data-bs-keyboard');
                    
                    if (data.success) {
                        // Show success alert
                        document.getElementById('syncSuccessAlert').style.display = 'block';
                        document.getElementById('syncErrorAlert').style.display = 'none';
                        
                        // Show stats
                        document.getElementById('syncStats').style.display = 'block';
                        document.getElementById('totalSynced').textContent = data.data.total_synced;
                        document.getElementById('totalCreated').textContent = data.data.created;
                        document.getElementById('totalUpdated').textContent = data.data.updated;
                        
                        // Show errors if any
                        if (data.data.errors && data.data.errors.length > 0) {
                            document.getElementById('errorSection').style.display = 'block';
                            document.getElementById('errorCount').textContent = data.data.errors.length;
                            
                            const errorTableBody = document.getElementById('errorTableBody');
                            errorTableBody.innerHTML = '';
                            
                            data.data.errors.forEach(error => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${error.email}</td>
                                    <td>${error.error}</td>
                                `;
                                errorTableBody.appendChild(row);
                            });
                        } else {
                            document.getElementById('errorSection').style.display = 'none';
                        }
                    } else {
                        // Show error alert
                        document.getElementById('syncSuccessAlert').style.display = 'none';
                        document.getElementById('syncErrorAlert').style.display = 'block';
                        document.getElementById('syncErrorMessage').textContent = data.message;
                        document.getElementById('syncStats').style.display = 'none';
                    }
                    
                    // Show result modal after a short delay
                    setTimeout(() => {
                        syncResultModal.show();
                    }, 300);
                })
                .catch(error => {
                    // Hide sync modal
                    syncConfirmModal.hide();
                    
                    // Reset progress bar
                    progressBar.style.width = '0%';
                    progressBar.setAttribute('aria-valuenow', '0');
                    
                    // Enable sync button
                    syncBtn.disabled = false;
                    
                    // Reset modal backdrop settings
                    modalElement.removeAttribute('data-bs-backdrop');
                    modalElement.removeAttribute('data-bs-keyboard');
                    
                    // Show error alert
                    document.getElementById('syncSuccessAlert').style.display = 'none';
                    document.getElementById('syncErrorAlert').style.display = 'block';
                    document.getElementById('syncErrorMessage').textContent = 'An unexpected error occurred: ' + error.message;
                    document.getElementById('syncStats').style.display = 'none';
                    
                    // Show result modal after a short delay
                    setTimeout(() => {
                        syncResultModal.show();
                    }, 300);
                    
                    console.error('Sync error:', error);
                });
            });
        }
    });
</script>
@endpush
