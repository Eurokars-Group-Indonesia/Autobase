@extends('layouts.app')

@section('title', 'Transaction Headers')

@php
    $breadcrumbs = [
        ['title' => 'Transactions', 'url' => '#']
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
        font-size: 12px;
        vertical-align: middle;
        text-align: center;
    }
    
    /* Table container with fixed height and scroll */
    #tableContainer .table-responsive {
        max-height: 65vh !important;
        overflow-y: auto !important;
        overflow-x: auto !important;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .table-nowrap {
        margin-bottom: 0;
    }
    
    /* Sticky table header */
    #tableContainer .table-responsive > table > thead {
        position: sticky !important;
        top: 0 !important;
        z-index: 10 !important;
        background-color: #f8f9fa !important;
    }
    
    [data-theme="dark"] #tableContainer .table-responsive > table > thead {
        background-color: var(--bg-card) !important;
    }
    
    /* Border box for each header-body group */
    .transaction-group {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 1rem;
        overflow: visible;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .transaction-group:hover {
        border-color: #002856;
        box-shadow: 0 4px 8px rgba(0, 40, 86, 0.15);
    }
    
    .transaction-group .header-row td {
        background-color: #f8f9fa;
        font-weight: 500;
    }
    
    .transaction-group .body-details-row td {
        background-color: #ffffff;
    }
    
    /* Sticky header for transaction groups */
    .transaction-group table thead {
        position: sticky !important;
        top: 0 !important;
        z-index: 10 !important;
        background-color: #f8f9fa !important;
    }
    
    [data-theme="dark"] .transaction-group table thead {
        background-color: var(--bg-card) !important;
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
        #tableContainer .table-responsive {
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
                <span><i class="bi bi-receipt"></i> Transaction Headers</span>
                <div id="headerButtons">
                    <a href="#" id="exportBtn" class="btn btn-success btn-sm me-2" style="display: none;">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                    @if(auth()->user()->hasPermission('transactions.header.import'))
                    <a href="{{ route('transactions.header.import') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-upload"></i> Import Excel
                    </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form id="searchForm" method="GET">
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
                                   placeholder="Customer, Chassis, Invoice No, WIP No, Reg No, Date..." 
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

<!-- Modal for Transaction Body Details (when not filtering) -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="detailsModalLabel">Transaction Body Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading transaction details...</p>
                </div>
                <div id="modalContent" style="display: none;">
                    <div class="mb-3">
                        <strong>WIP No:</strong> <span id="modalWipNo"></span> | 
                        <strong>Invoice No:</strong> <span id="modalInvNo"></span> | 
                        <strong>Magic ID:</strong> <span id="modalMagicId"></span>
                    </div>
                    <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th class="text-center">Part No</th>
                                    <th class="text-center">Description</th>
                                    <th class="text-center">Date Decard</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Cost Price</th>
                                    <th class="text-center">Selling Price</th>
                                    <th class="text-center">Discount %</th>
                                    <th class="text-center">Extended Price</th>
                                    <th class="text-center">VAT</th>
                                    <th class="text-center">Analysis Code</th>
                                    <th class="text-center">Parts/Labour</th>
                                </tr>
                            </thead>
                            <tbody id="detailsTableBody">
                                <!-- Data will be loaded here via AJAX -->
                            </tbody>
                            <tfoot class="table-light" style="position: sticky; bottom: 0; z-index: 1;">
                                <tr>
                                    <th colspan="8" class="text-end">Total Extended Price :</th>
                                    <th class="text-end" id="totalExtPrice">0.00</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div id="modalError" style="display: none;" class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <span id="errorMessage"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Transaction page loaded');
        
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
                url: '{{ route("transactions.search") }}',
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
                                window.history.pushState({}, '', '{{ route("transactions.index") }}');
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
            @if(auth()->user()->hasPermission('transactions.view'))
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
                
                const exportUrl = '{{ route("transactions.export") }}?' + params.toString();
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

        // Remove brand filter auto-search - wait for submit button
        // $('#brand_code').on('change', function() {
        //     const isClearButtonVisible = $('#clearBtn').is(':visible');
        //     performSearch(1, true, isClearButtonVisible);
        // });

        // Handle pagination clicks
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = new URL($(this).attr('href'));
            const page = url.searchParams.get('page') || 1;
            // Keep clear button state when paginating
            const isClearButtonVisible = $('#clearBtn').is(':visible');
            performSearch(page, true, isClearButtonVisible);
        });

        // Handle view details button click (when not filtering - use modal) - Using jQuery
        $(document).on('click', '.view-details', function(e) {
            e.preventDefault();
            const wipNo = $(this).data('wipno');
            const invNo = $(this).data('invno');
            const posCode = $(this).data('poscode');
            const magicId = $(this).data('magicid');
            
            console.log('View details clicked:', wipNo, invNo, magicId);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
            
            // Reset modal state
            $('#modalLoading').show();
            $('#modalContent').hide();
            $('#modalError').hide();
            $('#detailsTableBody').empty();
            
            // Set header info
            $('#modalWipNo').text(wipNo);
            $('#modalInvNo').text(invNo);
            $('#modalMagicId').text(magicId);
            
            // Fetch data via AJAX
            $.ajax({
                url: '{{ route("transactions.body.details") }}',
                method: 'GET',
                data: {
                    wip_no: wipNo,
                    invoice_no: invNo,
                    pos_code: posCode,
                    magic_id: magicId
                },
                success: function(response) {
                    $('#modalLoading').hide();
                    
                    if (response.success && response.data.length > 0) {
                        let totalExtPrice = 0;
                        let html = '';
                        
                        response.data.forEach(function(item, index) {
                            totalExtPrice += parseFloat(item.extended_price || 0);
                            
                            const dateDecard = item.date_decard ? new Date(item.date_decard).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}) : '-';
                            
                            html += `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
                                    <td>${item.part_no || '-'}</td>
                                    <td>${item.description || '-'}</td>
                                    <td class="text-center">${dateDecard}</td>
                                    <td class="text-end">${parseFloat(item.qty || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td class="text-end">${parseFloat(item.cost_price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td class="text-end">${parseFloat(item.selling_price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td class="text-end">${parseFloat(item.discount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}%</td>
                                    <td class="text-end">${parseFloat(item.extended_price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td>${item.vat || '-'}</td>
                                    <td>${item.analysis_code || '-'}</td>
                                    <td class="text-center">
                                        <span class="badge bg-${item.part_or_labour === 'P' ? 'primary' : 'success'}">
                                            ${item.part_or_labour === 'P' ? 'Part' : 'Labour'}
                                        </span>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        $('#detailsTableBody').html(html);
                        $('#totalExtPrice').text(totalExtPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#modalContent').show();
                    } else {
                        $('#modalError').show();
                        $('#errorMessage').text('No transaction body details found for this transaction.');
                    }
                },
                error: function(xhr) {
                    $('#modalLoading').hide();
                    $('#modalError').show();
                    $('#errorMessage').text('Failed to load transaction details. Please try again.');
                    console.error('AJAX Error:', xhr);
                }
            });
        });
    });
</script>
@endpush
