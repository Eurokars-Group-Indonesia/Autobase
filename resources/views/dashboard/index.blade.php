@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@push('styles')
<style>
    [data-theme="dark"] .text-primary {
        color: #FA891A !important;
    }
    
    /* Limit Select2 dropdown height */
    .select2-results__options {
        max-height: 200px !important;
        overflow-y: auto !important;
    }
    
    /* Match Select2 with Bootstrap form-select-sm */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 31px !important;
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Dashboard</h2>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Welcome to AutoBase (Autoline Database)
            </div>
            <div class="card-body">
                <h5>Hello, {{ auth()->user()->full_name }}!</h5>
                <p class="mb-0"><strong>Last Login:</strong> {{ auth()->user()->last_login ? auth()->user()->last_login->format('d M Y H:i:s') : 'First time login' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @if(auth()->user()->hasRole('ADMIN') || auth()->user()->hasRole('SUPERADMIN'))
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalUsers) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Transaction Master (Header)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalTransactionHeaders) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-receipt fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Transaction Detail (Body)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalTransactionBodies) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-list-ul fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Charts -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Transaction Statistics</h5>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center justify-content-md-end gap-3">
                            <div class="d-flex align-items-center">
                                <label class="me-2 mb-0">Brand:</label>
                                <select id="brandSelect" class="form-select form-select-sm" style="width: 200px;">
                                    <option value="">All Brands</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->brand_id }}" {{ $selectedBrandId == $brand->brand_id ? 'selected' : '' }}>
                                            {{ $brand->brand_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex align-items-center">
                                <label class="me-2 mb-0">Year:</label>
                                <select id="yearSelect" class="form-select form-select-sm" style="width: 120px;">
                                    @for($year = date('Y'); $year >= 2007; $year--)
                                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Transaction Header Chart -->
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header text-white" style="background-color: #002856;">
                <i class="bi bi-receipt"></i> Transaction Header by Invoice Date - <span id="headerChartYear">{{ $selectedYear }}</span>
            </div>
            <div class="card-body">
                <canvas id="transactionHeaderChart" style="max-height: 400px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Transaction Body Chart -->
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header text-white" style="background-color: #002856;">
                <i class="bi bi-list-ul"></i> Transaction Body by Decard Date - <span id="bodyChartYear">{{ $selectedYear }}</span>
            </div>
            <div class="card-body">
                <canvas id="transactionBodyChart" style="max-height: 400px;"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    let transactionHeaderChart;
    let transactionBodyChart;

    // Initialize Select2 for year dropdown
    $(document).ready(function() {
        $('#yearSelect').select2({
            theme: 'bootstrap-5',
            dropdownAutoWidth: true,
            width: '120px',
            placeholder: 'Select Year'
        });
    });

    // Initialize charts
    function initCharts(labels, headerData, bodyData, year) {
        // Transaction Header Chart
        const ctxHeader = document.getElementById('transactionHeaderChart').getContext('2d');
        
        const headerChartData = {
            labels: labels,
            datasets: [
                {
                    label: 'Transaction Count',
                    data: headerData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }
            ]
        };

        const headerConfig = {
            type: 'line',
            data: headerChartData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            title: function(context) {
                                return context[0].label + ' ' + year;
                            },
                            label: function(context) {
                                return 'Total: ' + context.parsed.y.toLocaleString() + ' transactions';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                return Number.isInteger(value) ? value.toLocaleString() : '';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        };

        if (transactionHeaderChart) {
            transactionHeaderChart.destroy();
        }
        transactionHeaderChart = new Chart(ctxHeader, headerConfig);

        // Transaction Body Chart
        const ctxBody = document.getElementById('transactionBodyChart').getContext('2d');
        
        const bodyChartData = {
            labels: labels,
            datasets: [
                {
                    label: 'Transaction Count',
                    data: bodyData,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.2)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#17a2b8',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }
            ]
        };

        const bodyConfig = {
            type: 'line',
            data: bodyChartData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            title: function(context) {
                                return context[0].label + ' ' + year;
                            },
                            label: function(context) {
                                return 'Total: ' + context.parsed.y.toLocaleString() + ' transactions';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                return Number.isInteger(value) ? value.toLocaleString() : '';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        };

        if (transactionBodyChart) {
            transactionBodyChart.destroy();
        }
        transactionBodyChart = new Chart(ctxBody, bodyConfig);
    }

    // Load chart data via AJAX
    function loadChartData(year, brandId) {
        let url = '{{ route('dashboard.chart-data') }}?year=' + year;
        if (brandId) {
            url += '&brand_id=' + brandId;
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Update chart year labels
                document.getElementById('headerChartYear').textContent = data.year;
                document.getElementById('bodyChartYear').textContent = data.year;
                
                // Update charts
                initCharts(data.labels, data.headerData, data.bodyData, data.year);
            })
            .catch(error => {
                console.error('Error loading chart data:', error);
            });
    }

    // Initialize charts on page load
    document.addEventListener('DOMContentLoaded', function() {
        const initialLabels = @json($chartLabels);
        const initialHeaderData = @json($chartHeaderData);
        const initialBodyData = @json($chartBodyData);
        const initialYear = {{ $selectedYear }};
        
        initCharts(initialLabels, initialHeaderData, initialBodyData, initialYear);
    });
    
    // Year select change event (after Select2 initialized)
    $(document).ready(function() {
        $('#yearSelect').on('select2:select', function() {
            const brandId = document.getElementById('brandSelect').value;
            loadChartData(this.value, brandId);
        });
        
        // Brand select change event
        document.getElementById('brandSelect').addEventListener('change', function() {
            const year = $('#yearSelect').val();
            loadChartData(year, this.value);
        });
    });
</script>
@endpush
@endsection
