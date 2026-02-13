@extends('layouts.app')

@section('title', 'Search History')

@php
    $breadcrumbs = [
        ['title' => 'Search History', 'url' => '#']
    ];
@endphp

@push('styles')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-input {
        background-color: white !important;
    }
    [data-theme="dark"] .flatpickr-input {
        background-color: var(--bg-card) !important;
    }
    .table-nowrap th,
    .table-nowrap td {
        white-space: nowrap;
    }

    .table-nowrap th {
        vertical-align: middle;
        text-align: center;
    }

    .table-nowrap td {
        font-size: 0.90em;
        vertical-align: middle;
        text-align: center;
    }
    
    /* Table border radius */
    .table-responsive {
        border-radius: 8px;
        overflow-x: auto;
        overflow-y: visible;
        box-shadow: 1rem 1rem 1rem 1rem rgba(0, 0, 0, 0.075);
    }
    
    .table-nowrap {
        margin-bottom: 0;
    }

    .table thead th {
        font-size: 12px;
    }

    .form-label, label {
        font-size: 12px;
    }
    
    /* Prevent horizontal scroll on mobile */
    @media (max-width: 767.98px) {
        body {
            overflow-x: hidden;
        }
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }
        .card {
            margin-left: 0;
            margin-right: 0;
        }
        .table-responsive {
            margin-left: -10px;
            margin-right: -10px;
            width: calc(100% + 20px);
        }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <span><i class="bi bi-clock-history"></i> Search History</span>
            </div>
            <div class="card-body">
                <form action="{{ route('search-history.index') }}" method="GET" id="searchForm">
                    <div class="row mb-3">
                        <div class="col-md-1">
                            <label class="form-label">Per Page</label>
                            <select class="form-select form-select-sm" name="per_page" onchange="this.form.submit()">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Transaction Type</label>
                            <select class="form-select form-select-sm" name="transaction_type">
                                <option value="">All</option>
                                <option value="H" {{ request('transaction_type') == 'H' ? 'selected' : '' }}>Header</option>
                                <option value="B" {{ request('transaction_type') == 'B' ? 'selected' : '' }}>Body</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">User</label>
                            <select class="form-select form-select-sm" name="user_id">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" {{ request('user_id') == $user->user_id ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="text" class="form-control form-control-sm" id="date_from_display" 
                                   placeholder="Select date from" readonly>
                            <input type="hidden" name="date_from" id="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="text" class="form-control form-control-sm" id="date_to_display" 
                                   placeholder="Select date to" readonly>
                            <input type="hidden" name="date_to" id="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary btn-sm me-2" type="submit">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            @if(request('transaction_type') || request('user_id') || request('date_from') || request('date_to'))
                                <a href="{{ route('search-history.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-sm table-nowrap">
                        <thead>
                            <tr>
                                <th style="min-width: 80px;">ID</th>
                                <th style="min-width: 150px;">User</th>
                                <th style="min-width: 100px;">Type</th>
                                <th style="min-width: 200px;">Search Query</th>
                                <th style="min-width: 120px;">Date From</th>
                                <th style="min-width: 120px;">Date To</th>
                                <th style="min-width: 150px;">Executed Date</th>
                                <th style="min-width: 120px;">Execution Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($histories as $history)
                                <tr>
                                    <td>{{ ($histories->currentPage() - 1) * $histories->perPage() + $loop->iteration }}</td>
                                    <td>{{ $history->user->full_name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $history->transaction_type === 'H' ? 'primary' : 'success' }}">
                                            {{ $history->getTransactionTypeLabel() }}
                                        </span>
                                    </td>
                                    <td>{{ $history->search ?? '-' }}</td>
                                    <td>{{ $history->date_from ? $history->date_from->format('d M Y') : '-' }}</td>
                                    <td>{{ $history->date_to ? $history->date_to->format('d M Y') : '-' }}</td>
                                    <td>{{ $history->executed_date->format('d M Y H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $history->execution_time < 500 ? 'success' : ($history->execution_time < 1500 ? 'warning' : 'danger') }}">
                                            {{ number_format($history->execution_time, 2) }} ms
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No search history found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="text-center text-md-start">
                        Showing {{ $histories->firstItem() ?? 0 }} to {{ $histories->lastItem() ?? 0 }} of {{ $histories->total() }} entries
                    </div>
                    <div>
                        {{ $histories->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to convert Y-m-d to d-m-Y
        function formatDateForDisplay(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                return parts[2] + '-' + parts[1] + '-' + parts[0];
            }
            return dateStr;
        }

        // Set initial display values if dates exist
        const initialDateFrom = document.getElementById('date_from').value;
        const initialDateTo = document.getElementById('date_to').value;
        if (initialDateFrom) {
            document.getElementById('date_from_display').value = formatDateForDisplay(initialDateFrom);
        }
        if (initialDateTo) {
            document.getElementById('date_to_display').value = formatDateForDisplay(initialDateTo);
        }

        // Initialize date_from picker
        const dateFromPicker = flatpickr("#date_from_display", {
            dateFormat: "d-m-Y",
            allowInput: false,
            onChange: function(selectedDates, dateStr, instance) {
                // Convert d-m-Y to Y-m-d for hidden input
                if (dateStr) {
                    const parts = dateStr.split('-');
                    const ymdFormat = parts[2] + '-' + parts[1] + '-' + parts[0];
                    document.getElementById('date_from').value = ymdFormat;
                    
                    // Update date_to minDate
                    dateToPicker.set('minDate', dateStr);
                    
                    // Clear date_to if it's before the new date_from
                    const dateToValue = document.getElementById('date_to_display').value;
                    if (dateToValue && new Date(dateToValue.split('-').reverse().join('-')) < new Date(ymdFormat)) {
                        dateToPicker.clear();
                        document.getElementById('date_to').value = '';
                    }
                } else {
                    document.getElementById('date_from').value = '';
                    dateToPicker.set('minDate', null);
                }
            }
        });

        // Initialize date_to picker
        const dateToPicker = flatpickr("#date_to_display", {
            dateFormat: "d-m-Y",
            allowInput: false,
            minDate: initialDateFrom ? formatDateForDisplay(initialDateFrom) : null,
            onChange: function(selectedDates, dateStr, instance) {
                // Convert d-m-Y to Y-m-d for hidden input
                if (dateStr) {
                    const parts = dateStr.split('-');
                    const ymdFormat = parts[2] + '-' + parts[1] + '-' + parts[0];
                    document.getElementById('date_to').value = ymdFormat;
                } else {
                    document.getElementById('date_to').value = '';
                }
            }
        });
    });
</script>
@endpush
