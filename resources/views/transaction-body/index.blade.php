@extends('layouts.app')

@section('title', 'Transaction Body')

@php
    $breadcrumbs = [
        ['title' => 'Transaction Body', 'url' => '#']
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
        font-size: 14px;
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
        font-size: 13px;
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
    
    /* Hide clear button by default */
    #clearBtn {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul"></i> Transaction Body</span>
                <div id="headerButtons">
                    <a href="#" id="exportBtn" class="btn btn-success btn-sm me-2" style="display: none;">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                    @if(auth()->user()->hasPermission('transaction-body.import'))
                    <a href="{{ route('transaction-body.import') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-upload"></i> Import Excel
                    </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form method="GET" id="searchForm">
                    <div class="row mb-3">
                        <div class="col-md-1">
                            <label class="form-label">Per Page</label>
                            <select class="form-select form-select-sm" name="per_page" id="per_page">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">POS Code</label>
                            <select class="form-select form-select-sm" name="brand_code" id="brand_code">
                                <option value="">All POS Code</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->brand_code }}" {{ request('brand_code') == $brand->brand_code ? 'selected' : '' }}>
                                        {{ $brand->brand_code }} - {{ $brand->brand_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control form-control-sm" name="search" id="search"
                                   placeholder="Part No, Invoice No, WIP No..." 
                                   value="{{ request('search') }}">
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
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary btn-sm me-2" type="submit" id="searchBtn">
                                <i class="bi bi-search"></i> Search
                            </button>
                            <button class="btn btn-secondary btn-sm" type="button" id="clearBtn">
                                <i class="bi bi-x-circle"></i> Clear
                            </button>
                        </div>
                    </div>
                </form>

                <div id="loadingIndicator" style="display: none;" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Searching transactions...</p>
                </div>

                <div id="tableContainer">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div id="paginationContainer">
                    <!-- Pagination will be loaded via AJAX -->
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

        // AJAX Search Function
        function performSearch(page = 1, updateUrl = true, showClearButton = false) {
            const formData = {
                search: $('#search').val(),
                date_from: $('#date_from').val(),
                date_to: $('#date_to').val(),
                brand_code: $('#brand_code').val(),
                per_page: $('#per_page').val(),
                page: page
            };

            // Show loading indicator
            $('#loadingIndicator').show();
            $('#tableContainer').hide();
            $('#paginationContainer').hide();

            $.ajax({
                url: '{{ route("transaction-body.search") }}',
                method: 'GET',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Update table content
                        $('#tableContainer').html(response.html);
                        
                        // Update pagination
                        $('#paginationContainer').html(response.pagination);
                        
                        // Show content
                        $('#tableContainer').show();
                        $('#paginationContainer').show();
                        $('#loadingIndicator').hide();
                        
                        // Update URL without page reload only if updateUrl is true
                        if (updateUrl) {
                            const url = new URL(window.location);
                            Object.keys(formData).forEach(key => {
                                if (formData[key] && formData[key] !== '10' && key !== 'per_page') {
                                    url.searchParams.set(key, formData[key]);
                                } else if (key === 'per_page' && formData[key] !== '10') {
                                    url.searchParams.set(key, formData[key]);
                                } else {
                                    url.searchParams.delete(key);
                                }
                            });
                            
                            // Only update URL if there are actual filters
                            if (hasActiveFilters() || formData.per_page !== '10' || formData.page > 1) {
                                window.history.pushState({}, '', url);
                            } else {
                                // Clear URL if no filters
                                window.history.pushState({}, '', '{{ route("transaction-body.index") }}');
                            }
                        }
                        
                        // Show clear button if requested
                        if (showClearButton) {
                            $('#clearBtn').show();
                        }
                        
                        // Update export button
                        updateExportButton();
                    }
                },
                error: function(xhr) {
                    $('#loadingIndicator').hide();
                    $('#tableContainer').show();
                    $('#paginationContainer').show();
                    console.error('Search error:', xhr);
                    alert('Failed to search transactions. Please try again.');
                }
            });
        }

        // Function to check if there are active filters
        function hasActiveFilters() {
            return $('#search').val() !== '' || 
                   $('#date_from').val() !== '' || 
                   $('#date_to').val() !== '';
        }

        // Function to update export button
        function updateExportButton() {
            @if(auth()->user()->hasPermission('transaction-body.view'))
            if (hasActiveFilters()) {
                const params = new URLSearchParams({
                    search: $('#search').val(),
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val(),
                    brand_code: $('#brand_code').val(),
                    per_page: $('#per_page').val()
                });
                
                // Remove empty params
                for (let [key, value] of [...params.entries()]) {
                    if (!value) {
                        params.delete(key);
                    }
                }
                
                const exportUrl = '{{ route("transaction-body.export") }}?' + params.toString();
                $('#exportBtn').attr('href', exportUrl).show();
            } else {
                $('#exportBtn').hide();
            }
            @endif
        }

        // Load initial data on page load
        $(document).ready(function() {
            // Check if there are URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const hasUrlParams = urlParams.has('search') || urlParams.has('date_from') || 
                                 urlParams.has('date_to') || urlParams.has('brand_code') || 
                                 urlParams.has('page');
            
            // Show clear button if there are URL parameters (coming from previous search)
            if (hasUrlParams && hasActiveFilters()) {
                $('#clearBtn').show();
            }
            
            // Update export button on initial load
            updateExportButton();
            
            // Load data without updating URL if no params, otherwise with URL update
            performSearch({{ request('page', 1) }}, hasUrlParams, false);
        });

        // Handle search form submit
        $('#searchForm').on('submit', function(e) {
            e.preventDefault();
            performSearch(1, true, true); // Show clear button after search
        });

        // Handle clear button
        $('#clearBtn').on('click', function() {
            $('#search').val('');
            $('#date_from').val('');
            $('#date_to').val('');
            $('#date_from_display').val('');
            $('#date_to_display').val('');
            $('#brand_code').val('');
            $('#per_page').val('10');
            dateFromPicker.clear();
            dateToPicker.clear();
            
            // Hide clear button
            $('#clearBtn').hide();
            
            // Hide export button
            $('#exportBtn').hide();
            
            // Perform search with cleared filters to show default 10 data
            performSearch(1, false, false);
        });

        // Handle per_page change
        $('#per_page').on('change', function() {
            // Keep clear button state when changing per_page
            const isClearButtonVisible = $('#clearBtn').is(':visible');
            performSearch(1, true, isClearButtonVisible);
        });

        // Handle pagination clicks
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = new URL($(this).attr('href'));
            const page = url.searchParams.get('page') || 1;
            // Keep clear button state when paginating
            const isClearButtonVisible = $('#clearBtn').is(':visible');
            performSearch(page, true, isClearButtonVisible);
        });
    });
</script>
@endpush
