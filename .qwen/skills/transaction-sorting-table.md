# Transaction Sorting with Arrow Icons (Bootstrap Table)

Provides sortable table implementation with up/down arrow icons for transactions at `/transactions` and `/transaction-body` routes.

## Route Configuration

```php
// routes/web.php
use App\Http\Controllers\TransactionController;

Route::get('/transactions', [TransactionController::class, 'index'])
    ->name('transactions.index');

Route::get('/transaction-body/{transaction}', [TransactionController::class, 'show'])
    ->name('transactions.show');

Route::get('/transaction-body', [TransactionController::class, 'bodyIndex'])
    ->name('transaction-body.index');
```

## Controller Implementation

### TransactionController

```php
<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Handle /transactions route - Transaction Header List
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'transaction_date');
        $order = $request->get('order', 'desc');
        
        // Validate sort parameters
        $allowedSorts = [
            'transaction_number',
            'transaction_date',
            'status',
            'total_amount',
            'item_count',
            'details_count',
        ];
        
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'transaction_date';
        }
        
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
        
        // Build query
        $query = Transaction::query();
        
        // Apply sorting
        $this->applyHeaderSort($query, $sortBy, $order);
        
        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }
        
        $transactions = $query->with('details')->paginate(20);
        
        // Preserve query params for pagination
        $transactions->appends($request->except('page'));
        
        return view('transactions.index', compact('transactions', 'sortBy', 'order'));
    }
    
    /**
     * Handle /transaction-body route - Transaction Details List
     */
    public function bodyIndex(Request $request)
    {
        $sortBy = $request->get('sort_by', 'transaction_date');
        $order = $request->get('order', 'desc');
        
        $allowedSorts = [
            'transaction_number',
            'transaction_date',
            'customer_name',
            'total_amount',
            'status',
        ];
        
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'transaction_date';
        }
        
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
        
        $query = Transaction::query();
        
        $this->applyBodySort($query, $sortBy, $order);
        
        // Filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('transaction_number', 'LIKE', "%{$request->search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$request->search}%");
            });
        }
        
        $transactions = $query->paginate(20);
        $transactions->appends($request->except('page'));
        
        return view('transaction-body.index', compact('transactions', 'sortBy', 'order'));
    }
    
    /**
     * Handle /transaction-body/{id} route - Single Transaction Details
     */
    public function show(Request $request, Transaction $transaction)
    {
        $sortBy = $request->get('sort_by', 'product_name');
        $order = $request->get('order', 'asc');
        
        $allowedSorts = [
            'product_name',
            'quantity',
            'unit_price',
            'subtotal',
            'created_at',
        ];
        
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'product_name';
        }
        
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
        
        $details = $transaction->details()
            ->orderBy($sortBy, $order)
            ->paginate(20);
        
        $details->appends($request->except('page'));
        
        return view('transaction-body.show', compact('transaction', 'details', 'sortBy', 'order'));
    }
    
    /**
     * Apply sorting for header list
     */
    private function applyHeaderSort($query, string $sortBy, string $order): void
    {
        match($sortBy) {
            'transaction_number' => $query->orderBy('transactions.transaction_number', $order),
            'transaction_date' => $query->orderBy('transactions.transaction_date', $order),
            'status' => $query->orderBy('transactions.status', $order),
            'total_amount' => $query->orderBy('transactions.total_amount', $order),
            'item_count' => $query->orderBy('transactions.item_count', $order),
            'details_count' => $query->withCount('details')->orderBy('details_count', $order),
            default => $query->orderBy('transactions.transaction_date', $order),
        };
    }
    
    /**
     * Apply sorting for body list
     */
    private function applyBodySort($query, string $sortBy, string $order): void
    {
        match($sortBy) {
            'transaction_number' => $query->orderBy('transactions.transaction_number', $order),
            'transaction_date' => $query->orderBy('transactions.transaction_date', $order),
            'customer_name' => $query->orderBy('transactions.customer_name', $order),
            'total_amount' => $query->orderBy('transactions.total_amount', $order),
            'status' => $query->orderBy('transactions.status', $order),
            default => $query->orderBy('transactions.transaction_date', $order),
        };
    }
}
```

## Blade View: /transactions Index

```blade
{{-- resources/views/transactions/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Transaction List')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-primary">
                            <i class="bi bi-receipt"></i> Transactions
                        </h4>
                        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> New Transaction
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    {{-- Filters --}}
                    @include('transactions.partials.filters')
                    
                    {{-- Transaction Header Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    {{-- Transaction Number --}}
                                    <th style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transactions.index') }}', 'transaction_number')">
                                        <div class="d-flex align-items-center">
                                            Transaction #
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'transaction_number',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Transaction Date --}}
                                    <th style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transactions.index') }}', 'transaction_date')">
                                        <div class="d-flex align-items-center">
                                            Date
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'transaction_date',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Customer Name --}}
                                    <th style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transactions.index') }}', 'customer_name')">
                                        <div class="d-flex align-items-center">
                                            Customer
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'customer_name',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Status --}}
                                    <th style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transactions.index') }}', 'status')">
                                        <div class="d-flex align-items-center">
                                            Status
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'status',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Total Amount --}}
                                    <th class="text-end" style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transactions.index') }}', 'total_amount')">
                                        <div class="d-flex align-items-center justify-content-end">
                                            Amount
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'total_amount',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Item Count --}}
                                    <th class="text-center" style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transactions.index') }}', 'item_count')">
                                        <div class="d-flex align-items-center justify-content-center">
                                            Items
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'item_count',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Details Count --}}
                                    <th class="text-center" style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transactions.index') }}', 'details_count')">
                                        <div class="d-flex align-items-center justify-content-center">
                                            Details
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'details_count',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Actions --}}
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>
                                            <strong>{{ $transaction->transaction_number }}</strong>
                                        </td>
                                        <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                        <td>{{ $transaction->customer_name ?? 'N/A' }}</td>
                                        <td>
                                            @if($transaction->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @else
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ number_format($transaction->total_amount, 2) }}</strong>
                                        </td>
                                        <td class="text-center">{{ $transaction->item_count ?? 0 }}</td>
                                        <td class="text-center">{{ $transaction->details_count ?? $transaction->details->count() }}</td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('transactions.show', $transaction) }}" 
                                                   class="btn btn-info" 
                                                   title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('transactions.edit', $transaction) }}" 
                                                   class="btn btn-warning" 
                                                   title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                            <p class="mt-2">No transactions found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="text-muted">
                            Showing {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} results
                        </span>
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function sortTable(baseUrl, field) {
    const url = new URL(baseUrl);
    const params = new URLSearchParams(window.location.search);
    
    // Get current sort params
    const currentSort = params.get('sort_by');
    const currentOrder = params.get('order') || 'desc';
    
    // If clicking same column, toggle order
    if (currentSort === field) {
        const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
        params.set('order', newOrder);
    } else {
        // New column, default to desc
        params.set('sort_by', field);
        params.set('order', 'desc');
    }
    
    url.search = params.toString();
    window.location.href = url.toString();
}
</script>
@endpush
@endsection
```

## Sort Arrows Partial

```blade
{{-- resources/views/transactions/partials/sort-arrows.blade.php --}}
@php
    $isActive = $currentSort === $field;
@endphp

<div class="sort-arrows ms-1">
    @if($isActive)
        @if($currentOrder === 'asc')
            {{-- Active ASC - Up arrow filled --}}
            <i class="bi bi-caret-up-fill text-primary" style="font-size: 0.9rem;"></i>
            <i class="bi bi-caret-down" style="font-size: 0.9rem; opacity: 0.3;"></i>
        @else
            {{-- Active DESC - Down arrow filled --}}
            <i class="bi bi-caret-up" style="font-size: 0.9rem; opacity: 0.3;"></i>
            <i class="bi bi-caret-down-fill text-primary" style="font-size: 0.9rem;"></i>
        @endif
    @else
        {{-- Inactive - Both arrows outline --}}
        <i class="bi bi-caret-up" style="font-size: 0.9rem; opacity: 0.3;"></i>
        <i class="bi bi-caret-down" style="font-size: 0.9rem; opacity: 0.3;"></i>
    @endif
</div>

<style>
.sort-arrows {
    transition: opacity 0.2s ease;
}

th:hover .sort-arrows {
    opacity: 1;
}

.sort-arrows i {
    display: inline-block;
    vertical-align: middle;
}
</style>
```

## Blade View: /transaction-body Index

```blade
{{-- resources/views/transaction-body/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Transaction Body List')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-primary">
                            <i class="bi bi-file-earmark-text"></i> Transaction Body
                        </h4>
                        <div class="d-flex gap-2">
                            <div class="input-group input-group-sm" style="max-width: 300px;">
                                <input type="text" class="form-control" 
                                       id="searchInput" 
                                       placeholder="Search transaction or customer..."
                                       value="{{ request('search') }}">
                                <button class="btn btn-outline-primary" type="button" onclick="searchTransactions()">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    {{-- Transaction Body Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    {{-- Transaction Number --}}
                                    <th style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transaction-body.index') }}', 'transaction_number')">
                                        <div class="d-flex align-items-center">
                                            Transaction #
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'transaction_number',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Transaction Date --}}
                                    <th style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transaction-body.index') }}', 'transaction_date')">
                                        <div class="d-flex align-items-center">
                                            Date
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'transaction_date',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Customer Name --}}
                                    <th style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transaction-body.index') }}', 'customer_name')">
                                        <div class="d-flex align-items-center">
                                            Customer Name
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'customer_name',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Status --}}
                                    <th style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transaction-body.index') }}', 'status')">
                                        <div class="d-flex align-items-center">
                                            Status
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'status',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Total Amount --}}
                                    <th class="text-end" style="cursor: pointer;" 
                                        onclick="sortTable('{{ route('transaction-body.index') }}', 'total_amount')">
                                        <div class="d-flex align-items-center justify-content-end">
                                            Total Amount
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'total_amount',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    
                                    {{-- Actions --}}
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>
                                            <strong>{{ $transaction->transaction_number }}</strong>
                                        </td>
                                        <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                        <td>{{ $transaction->customer_name ?? 'N/A' }}</td>
                                        <td>
                                            @if($transaction->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @else
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ number_format($transaction->total_amount, 2) }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('transactions.show', $transaction) }}" 
                                                   class="btn btn-primary" 
                                                   title="View Details">
                                                    <i class="bi bi-box-arrow-in-right"></i> View Details
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                            <p class="mt-2">No transactions found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="text-muted">
                            Showing {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} results
                        </span>
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function sortTable(baseUrl, field) {
    const url = new URL(baseUrl);
    const params = new URLSearchParams(window.location.search);
    
    const currentSort = params.get('sort_by');
    const currentOrder = params.get('order') || 'desc';
    
    if (currentSort === field) {
        const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
        params.set('order', newOrder);
    } else {
        params.set('sort_by', field);
        params.set('order', 'desc');
    }
    
    url.search = params.toString();
    window.location.href = url.toString();
}

function searchTransactions() {
    const search = document.getElementById('searchInput').value;
    const url = new URL('{{ route('transaction-body.index') }}');
    url.searchParams.set('search', search);
    window.location.href = url.toString();
}

// Enter key to search
document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchTransactions();
    }
});
</script>
@endpush
@endsection
```

## Blade View: Single Transaction Details

```blade
{{-- resources/views/transaction-body/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Transaction Header Info --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-receipt"></i> {{ $transaction->transaction_number }}
                        </h5>
                        <a href="{{ route('transaction-body.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="text-muted small">Transaction Date</label>
                            <p class="mb-0"><strong>{{ $transaction->transaction_date->format('Y-m-d') }}</strong></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Customer</label>
                            <p class="mb-0"><strong>{{ $transaction->customer_name ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                @if($transaction->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Total Amount</label>
                            <p class="mb-0 text-end"><strong>{{ number_format($transaction->total_amount, 2) }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Transaction Details Table --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i> Transaction Details
                        </h5>
                        
                        {{-- Sort Dropdown --}}
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                    type="button" 
                                    data-bs-toggle="dropdown">
                                <i class="bi bi-sort-down"></i> Sort by
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item {{ $sortBy === 'product_name' && $order === 'asc' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'product_name', 'order' => 'asc']) }}">
                                        <i class="bi bi-sort-alpha-down"></i> Product (A-Z)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ $sortBy === 'product_name' && $order === 'desc' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'product_name', 'order' => 'desc']) }}">
                                        <i class="bi bi-sort-alpha-down-alt"></i> Product (Z-A)
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item {{ $sortBy === 'quantity' && $order === 'desc' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'quantity', 'order' => 'desc']) }}">
                                        <i class="bi bi-caret-down-fill"></i> Quantity (High-Low)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ $sortBy === 'quantity' && $order === 'asc' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'quantity', 'order' => 'asc']) }}">
                                        <i class="bi bi-caret-up-fill"></i> Quantity (Low-High)
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item {{ $sortBy === 'unit_price' && $order === 'desc' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'unit_price', 'order' => 'desc']) }}">
                                        <i class="bi bi-currency-dollar"></i> Price (High-Low)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ $sortBy === 'unit_price' && $order === 'asc' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'unit_price', 'order' => 'asc']) }}">
                                        Price (Low-High)
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item {{ $sortBy === 'subtotal' && $order === 'desc' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'subtotal', 'order' => 'desc']) }}">
                                        Subtotal (High-Low)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ $sortBy === 'subtotal' && $order === 'asc' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'subtotal', 'order' => 'asc']) }}">
                                        Subtotal (Low-High)
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th style="cursor: pointer;" onclick="sortDetails('product_name')">
                                        <div class="d-flex align-items-center">
                                            #
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'product_name',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    <th style="cursor: pointer;" onclick="sortDetails('product_name')">
                                        <div class="d-flex align-items-center">
                                            Product Name
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'product_name',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    <th class="text-end" style="cursor: pointer;" onclick="sortDetails('unit_price')">
                                        <div class="d-flex align-items-center justify-content-end">
                                            Unit Price
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'unit_price',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    <th class="text-center" style="cursor: pointer;" onclick="sortDetails('quantity')">
                                        <div class="d-flex align-items-center justify-content-center">
                                            Qty
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'quantity',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                    <th class="text-end" style="cursor: pointer;" onclick="sortDetails('subtotal')">
                                        <div class="d-flex align-items-center justify-content-end">
                                            Subtotal
                                            @include('transactions.partials.sort-arrows', [
                                                'field' => 'subtotal',
                                                'currentSort' => $sortBy,
                                                'currentOrder' => $order
                                            ])
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($details as $index => $detail)
                                    <tr>
                                        <td>{{ $details->firstItem() + $index }}</td>
                                        <td>{{ $detail->product_name }}</td>
                                        <td class="text-end">{{ number_format($detail->unit_price, 2) }}</td>
                                        <td class="text-center">{{ $detail->quantity }}</td>
                                        <td class="text-end">
                                            <strong>{{ number_format($detail->subtotal, 2) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                            <p class="mt-2">No details found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-primary">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-end">
                                        <strong>{{ number_format($details->sum('subtotal'), 2) }}</strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    @if($details->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-muted">
                                Showing {{ $details->firstItem() ?? 0 }} - {{ $details->lastItem() ?? 0 }} of {{ $details->total() }} items
                            </span>
                            {{ $details->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function sortDetails(field) {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    const currentSort = params.get('sort_by');
    const currentOrder = params.get('order') || 'asc';
    
    if (currentSort === field) {
        const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
        params.set('order', newOrder);
    } else {
        params.set('sort_by', field);
        params.set('order', 'asc');
    }
    
    url.search = params.toString();
    window.location.href = url.toString();
}
</script>
@endpush
@endsection
```

## CSS Styling

```css
/* resources/css/app.css or in layout */

/* Sortable column hover effect */
th[onclick] {
    transition: background-color 0.2s ease;
}

th[onclick]:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

/* Sort arrows animation */
.sort-arrows i {
    transition: all 0.2s ease;
}

th[onclick]:hover .sort-arrows i {
    opacity: 0.6;
}

th[onclick]:hover .sort-arrows .bi-caret-up-fill,
th[onclick]:hover .sort-arrows .bi-caret-down-fill {
    opacity: 1;
}

/* Table row highlight on hover */
.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.03);
}

/* Badge styles */
.badge {
    padding: 0.35em 0.65em;
    font-weight: 500;
}
```

## Summary

| Route | View | Sortable Fields |
|-------|------|-----------------|
| `/transactions` | `transactions.index` | Transaction #, Date, Customer, Status, Amount, Items, Details |
| `/transaction-body` | `transaction-body.index` | Transaction #, Date, Customer, Status, Amount |
| `/transaction-body/{id}` | `transaction-body.show` | Product, Qty, Unit Price, Subtotal |

**Features:**
- ✅ Up/down arrow icons (Bootstrap Icons)
- ✅ Click header to sort (toggle ASC/DESC)
- ✅ Active sort column highlighted
- ✅ Preserves filters during sort
- ✅ Pagination with preserved params
- ✅ Responsive Bootstrap tables
