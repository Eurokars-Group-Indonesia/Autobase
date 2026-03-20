# Transaction Header & Details Sorting Skill

Provides sorting implementation patterns for transaction headers with related details in Laravel/Bootstrap.

## Database Schema Considerations

### Typical Transaction Structure

```php
// transactions table (header)
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->index();
    $table->string('transaction_number')->unique()->index();
    $table->date('transaction_date')->index();
    $table->enum('status', ['pending', 'completed', 'cancelled'])->index();
    $table->decimal('total_amount', 15, 2)->index();
    $table->integer('item_count')->default(0);  // Denormalized for sorting
    $table->timestamps();
    
    // Composite indexes for common sorts
    $table->index(['transaction_date', 'id']);
    $table->index(['status', 'transaction_date']);
    $table->index(['user_id', 'transaction_date']);
});

// transaction_details table
Schema::create('transaction_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('transaction_id')->constrained()->index();
    $table->string('product_name');
    $table->integer('quantity');
    $table->decimal('unit_price', 15, 2);
    $table->decimal('subtotal', 15, 2);
    $table->timestamps();
    
    $table->index(['transaction_id', 'product_name']);
});
```

## Sorting Implementation Patterns

### 1. Sort Builder Class

```php
class TransactionSort {
    
    protected array $allowedSorts = [
        'transaction_date' => 'transactions.transaction_date',
        'transaction_number' => 'transactions.transaction_number',
        'status' => 'transactions.status',
        'total_amount' => 'transactions.total_amount',
        'item_count' => 'transactions.item_count',
        'created_at' => 'transactions.created_at',
        'details_count' => 'details_count',  // Virtual field
        'latest_detail' => 'latest_detail_date',  // Virtual field
    ];
    
    public function apply(Builder $query, ?string $sortBy, string $order = 'desc'): Builder {
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
        
        // Default sort
        if (!$sortBy || !isset($this->allowedSorts[$sortBy])) {
            return $query->orderByDesc('transactions.transaction_date');
        }
        
        $column = $this->allowedSorts[$sortBy];
        
        // Handle virtual fields (aggregates)
        if ($sortBy === 'details_count') {
            return $this->sortByDetailsCount($query, $order);
        }
        
        if ($sortBy === 'latest_detail') {
            return $this->sortByLatestDetail($query, $order);
        }
        
        // Regular column sort
        return $query->orderBy($column, $order);
    }
    
    protected function sortByDetailsCount(Builder $query, string $order): Builder {
        return $query->withCount('details')
            ->orderBy('details_count', $order);
    }
    
    protected function sortByLatestDetail(Builder $query, string $order): Builder {
        return $query->with(['details' => function($q) use ($order) {
                $q->select('transaction_id', 'created_at')
                  ->orderByDesc('created_at')
                  ->limit(1);
            }])
            ->addSelect([
                'latest_detail_date' => \App\Models\TransactionDetail::select('created_at')
                    ->whereColumn('transaction_details.transaction_id', 'transactions.id')
                    ->orderByDesc('created_at')
                    ->limit(1)
            ])
            ->orderBy('latest_detail_date', $order);
    }
}

// Usage
$sort = new TransactionSort();
$transactions = $sort->apply(
    Transaction::with('details'),
    request('sort_by'),
    request('order', 'desc')
)->paginate(20);
```

### 2. Sortable Trait (Reusable)

```php
trait Sortable {
    
    protected array $customSorts = [];
    
    public function scopeSortable($query, ?string $sortBy, string $order = 'desc') {
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
        
        if (!$sortBy) {
            return $query;
        }
        
        // Check for custom sort methods
        if (isset($this->customSorts[$sortBy])) {
            $method = $this->customSorts[$sortBy];
            return $this->$method($query, $order);
        }
        
        // Prevent SQL injection - whitelist columns
        $allowedColumns = $this->getSortableColumns();
        
        if (in_array($sortBy, $allowedColumns)) {
            return $query->orderBy($sortBy, $order);
        }
        
        return $query;
    }
    
    protected function getSortableColumns(): array {
        return [
            'transaction_date',
            'transaction_number',
            'status',
            'total_amount',
            'created_at',
        ];
    }
}

// In Transaction Model
class Transaction extends Model {
    use Sortable;
    
    protected array $customSorts = [
        'details_count' => 'sortByDetailsCount',
        'latest_detail' => 'sortByLatestDetail',
        'oldest_detail' => 'sortByOldestDetail',
    ];
    
    protected function sortByDetailsCount($query, string $order) {
        return $query->withCount('details')->orderBy('details_count', $order);
    }
    
    protected function sortByLatestDetail($query, string $order) {
        return $query->addSelect([
                'latest_detail' => TransactionDetail::select('created_at')
                    ->whereColumn('transaction_details.transaction_id', 'transactions.id')
                    ->orderByDesc('created_at')
                    ->limit(1)
            ])
            ->orderBy('latest_detail', $order);
    }
    
    protected function sortByOldestDetail($query, string $order) {
        return $query->addSelect([
                'oldest_detail' => TransactionDetail::select('created_at')
                    ->whereColumn('transaction_details.transaction_id', 'transactions.id')
                    ->orderBy('created_at')
                    ->limit(1)
            ])
            ->orderBy('oldest_detail', $order);
    }
}

// Usage
$transactions = Transaction::sortable(
    request('sort_by'),
    request('order', 'desc')
)->paginate(20);
```

### 3. Sort with Aggregated Details Data

```php
class TransactionHeaderSort {
    
    /**
     * Sort transactions by header fields with details aggregation
     */
    public function sort(array $filters): LengthAwarePaginator {
        $query = Transaction::with('details');
        
        // Apply filters
        $this->applyFilters($query, $filters);
        
        // Apply sorting
        $this->applySort($query, $filters['sort_by'] ?? 'transaction_date', $filters['order'] ?? 'desc');
        
        return $query->simplePaginate(20);
    }
    
    protected function applyFilters($query, array $filters): void {
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('transaction_number', 'LIKE', "%{$filters['search']}%")
                  ->orWhereHas('details', function($q2) use ($filters) {
                      $q2->where('product_name', 'LIKE', "%{$filters['search']}%");
                  });
            });
        }
    }
    
    protected function applySort($query, string $sortBy, string $order): void {
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
        
        match($sortBy) {
            'transaction_number' => $query->orderBy('transactions.transaction_number', $order),
            'transaction_date' => $query->orderBy('transactions.transaction_date', $order),
            'status' => $query->orderBy('transactions.status', $order),
            'total_amount' => $query->orderBy('transactions.total_amount', $order),
            'item_count' => $query->orderBy('transactions.item_count', $order),
            
            // Details-based sorts
            'details_count' => $query->withCount('details')->orderBy('details_count', $order),
            'total_quantity' => $query->addSelect([
                    'total_qty' => TransactionDetail::selectRaw('SUM(quantity)')
                        ->whereColumn('transaction_details.transaction_id', 'transactions.id')
                ])
                ->orderBy('total_qty', $order),
            'latest_detail' => $query->addSelect([
                    'latest_detail' => TransactionDetail::select('created_at')
                        ->whereColumn('transaction_details.transaction_id', 'transactions.id')
                        ->orderByDesc('created_at')
                        ->limit(1)
                ])
                ->orderBy('latest_detail', $order),
            'oldest_detail' => $query->addSelect([
                    'oldest_detail' => TransactionDetail::select('created_at')
                        ->whereColumn('transaction_details.transaction_id', 'transactions.id')
                        ->orderBy('created_at')
                        ->limit(1)
                ])
                ->orderBy('oldest_detail', $order),
            
            // Default
            default => $query->orderBy('transactions.transaction_date', $order),
        };
    }
}
```

## Controller Implementation

```php
class TransactionController extends Controller {
    
    public function index(Request $request) {
        $filters = $request->validate([
            'sort_by' => 'nullable|string|in:transaction_number,transaction_date,status,total_amount,item_count,details_count,total_quantity,latest_detail',
            'order' => 'nullable|string|in:asc,desc',
            'user_id' => 'nullable|integer|exists:users,id',
            'status' => 'nullable|string|in:pending,completed,cancelled',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'search' => 'nullable|string|max:100',
        ]);
        
        $sorter = new TransactionHeaderSort();
        $transactions = $sorter->sort($filters);
        
        // Preserve query params for pagination links
        $transactions->appends($request->except('page'));
        
        return view('transactions.index', compact('transactions'));
    }
    
    public function details(Request $request, Transaction $transaction) {
        $details = $transaction->details()
            ->sortable($request->get('sort_by'), $request->get('order', 'asc'))
            ->paginate(20);
        
        return view('transactions.details', compact('transaction', 'details'));
    }
}
```

## TransactionDetail Model Sorting

```php
class TransactionDetail extends Model {
    
    public function scopeSortable($query, ?string $sortBy, string $order = 'asc') {
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
        
        $allowedSorts = [
            'product_name' => 'product_name',
            'quantity' => 'quantity',
            'unit_price' => 'unit_price',
            'subtotal' => 'subtotal',
            'created_at' => 'created_at',
        ];
        
        if ($sortBy && isset($allowedSorts[$sortBy])) {
            return $query->orderBy($allowedSorts[$sortBy], $order);
        }
        
        return $query->orderBy('product_name', 'asc');
    }
}
```

## Bootstrap UI Implementation

### Transaction List with Sortable Headers

```blade
{{-- transactions/index.blade.php --}}
<div class="card">
    <div class="card-body">
        {{-- Filters --}}
        @include('transactions.partials.filters')
        
        {{-- Sortable Table --}}
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        {{-- Sortable Headers --}}
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'transaction_number', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Transaction #
                                @include('transactions.partials.sort-icon', ['field' => 'transaction_number'])
                            </a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'transaction_date', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Date
                                @include('transactions.partials.sort-icon', ['field' => 'transaction_date'])
                            </a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Status
                                @include('transactions.partials.sort-icon', ['field' => 'status'])
                            </a>
                        </th>
                        <th class="text-end">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'total_amount', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Amount
                                @include('transactions.partials.sort-icon', ['field' => 'total_amount'])
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'details_count', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Items
                                @include('transactions.partials.sort-icon', ['field' => 'details_count'])
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'total_quantity', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Qty
                                @include('transactions.partials.sort-icon', ['field' => 'total_quantity'])
                            </a>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_number }}</td>
                            <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($transaction->total_amount, 2) }}</td>
                            <td class="text-center">{{ $transaction->details_count ?? $transaction->details->count() }}</td>
                            <td class="text-center">{{ $transaction->details->sum('quantity') }}</td>
                            <td>
                                <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-sm btn-primary">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No transactions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">
                Showing {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }}
            </span>
            {{ $transactions->links() }}
        </div>
    </div>
</div>
```

### Sort Icon Partial

```blade
{{-- transactions/partials/sort-icon.blade.php --}}
@php
    $currentSort = request('sort_by');
    $currentOrder = request('order', 'desc');
@endphp

@if($currentSort === $field)
    @if($currentOrder === 'asc')
        <i class="bi bi-caret-up-fill text-primary"></i>
    @else
        <i class="bi bi-caret-down-fill text-primary"></i>
    @endif
@else
    <i class="bi bi-caret-down text-muted"></i>
@endif
```

### Details Table with Sorting

```blade
{{-- transactions/show.blade.php --}}
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Transaction Details</h5>
        
        {{-- Details Sort Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-sort-numeric-down"></i> Sort
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item {{ request('detail_sort') === 'product_name' || !request('detail_sort') ? 'active' : '' }}" 
                       href="{{ request()->fullUrlWithQuery(['detail_sort' => 'product_name', 'detail_order' => 'asc']) }}">
                        Product Name (A-Z)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request('detail_sort') === 'product_name' && request('detail_order') === 'desc' ? 'active' : '' }}" 
                       href="{{ request()->fullUrlWithQuery(['detail_sort' => 'product_name', 'detail_order' => 'desc']) }}">
                        Product Name (Z-A)
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item {{ request('detail_sort') === 'quantity' ? 'active' : '' }}" 
                       href="{{ request()->fullUrlWithQuery(['detail_sort' => 'quantity', 'detail_order' => 'desc']) }}">
                        Quantity (High-Low)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request('detail_sort') === 'quantity' && request('detail_order') === 'asc' ? 'active' : '' }}" 
                       href="{{ request()->fullUrlWithQuery(['detail_sort' => 'quantity', 'detail_order' => 'asc']) }}">
                        Quantity (Low-High)
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item {{ request('detail_sort') === 'subtotal' || !request('detail_sort') ? 'active' : '' }}" 
                       href="{{ request()->fullUrlWithQuery(['detail_sort' => 'subtotal', 'detail_order' => 'desc']) }}">
                        Price (High-Low)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request('detail_sort') === 'subtotal' && request('detail_order') === 'asc' ? 'active' : '' }}" 
                       href="{{ request()->fullUrlWithQuery(['detail_sort' => 'subtotal', 'detail_order' => 'asc']) }}">
                        Price (Low-High)
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->details as $index => $detail)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $detail->product_name }}</td>
                            <td class="text-end">{{ number_format($detail->unit_price, 2) }}</td>
                            <td class="text-center">{{ $detail->quantity }}</td>
                            <td class="text-end">{{ number_format($detail->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <th colspan="4">Total</th>
                        <th class="text-end">{{ number_format($transaction->details->sum('subtotal'), 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
```

## JavaScript Enhanced Sorting

```blade
{{-- Add to transactions/index.blade.php --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for sortable headers
    document.querySelectorAll('th a[href*="sort_by"]').forEach(link => {
        link.addEventListener('click', function(e) {
            // Optional: Add loading indicator
            const table = this.closest('table');
            table.style.opacity = '0.5';
            
            // Let the link navigate normally
            // Table will reload with new sort
        });
    });
    
    // Keyboard navigation for accessibility
    document.querySelectorAll('th a[href*="sort_by"]').forEach(link => {
        link.setAttribute('tabindex', '0');
        link.setAttribute('role', 'button');
        link.setAttribute('aria-label', 'Sort by ' + link.textContent.trim());
        
        link.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});

// Optional: AJAX sorting without page reload
function sortTransactions(sortBy, order) {
    const params = new URLSearchParams(window.location.search);
    params.set('sort_by', sortBy);
    params.set('order', order);
    
    fetch(window.location.pathname + '?' + params.toString())
        .then(response => response.text())
        .then(html => {
            // Update table body
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('table tbody');
            document.querySelector('table tbody').innerHTML = newTable.innerHTML;
        });
}
</script>
@endpush
```

## Performance Optimization for Sorting

### 1. Cache Sort Results

```php
use Illuminate\Support\Facades\Cache;

class CachedTransactionSort {
    
    public function sort(array $filters): LengthAwarePaginator {
        $cacheKey = $this->buildCacheKey($filters);
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function() use ($filters) {
            return (new TransactionHeaderSort())->sort($filters);
        });
    }
    
    protected function buildCacheKey(array $filters): string {
        return 'transactions.sort.' . md5(json_encode($filters));
    }
}

// Invalidate cache when data changes
class TransactionObserver {
    public function saved(Transaction $transaction) {
        Cache::tags(['transactions'])->flush();
    }
}
```

### 2. Denormalize for Frequent Sorts

```php
// In Transaction model
class Transaction extends Model {
    
    protected static function boot() {
        parent::boot();
        
        static::saving(function($transaction) {
            // Denormalize details count for faster sorting
            if ($transaction->exists) {
                $transaction->item_count = $transaction->details()->count();
            }
        });
    }
}

// Update denormalized fields via job/observer
class UpdateTransactionCounts {
    public function handle() {
        Transaction::chunk(100, function($transactions) {
            foreach ($transactions as $transaction) {
                $transaction->update([
                    'item_count' => $transaction->details()->count(),
                    'total_quantity' => $transaction->details()->sum('quantity'),
                ]);
            }
        });
    }
}
```

### 3. Index Optimization

```php
// Ensure indexes support sorting
Schema::table('transactions', function (Blueprint $table) {
    // For ORDER BY with WHERE
    $table->index(['status', 'transaction_date', 'id']);
    $table->index(['user_id', 'transaction_date', 'id']);
    
    // For ORDER BY amount
    $table->index(['transaction_date', 'total_amount']);
});
```

## Testing Sort Functionality

```php
class TransactionSortTest extends TestCase {
    
    public function test_sort_by_transaction_date() {
        Transaction::factory()->create(['transaction_date' => '2024-01-01']);
        Transaction::factory()->create(['transaction_date' => '2024-01-03']);
        Transaction::factory()->create(['transaction_date' => '2024-01-02']);
        
        $response = $this->get('/transactions?sort_by=transaction_date&order=asc');
        
        $response->assertSuccessful();
        // Assert order...
    }
    
    public function test_sort_by_details_count() {
        $t1 = Transaction::factory()->create();
        TransactionDetail::factory()->count(3)->create(['transaction_id' => $t1->id]);
        
        $t2 = Transaction::factory()->create();
        TransactionDetail::factory()->count(1)->create(['transaction_id' => $t2->id]);
        
        $response = $this->get('/transactions?sort_by=details_count&order=desc');
        
        $response->assertSuccessful();
        // First result should have 3 details
    }
    
    public function test_sort_prevents_sql_injection() {
        $response = $this->get('/transactions?sort_by=id; DROP TABLE users;--&order=asc');
        
        // Should use default sort, not crash
        $response->assertSuccessful();
    }
}
```

## Quick Reference

| Sort Field | Type | Performance |
|------------|------|-------------|
| `transaction_number` | Header | ⚡ Fast (indexed) |
| `transaction_date` | Header | ⚡ Fast (indexed) |
| `status` | Header | ⚡ Fast (indexed) |
| `total_amount` | Header | ⚡ Fast (indexed) |
| `item_count` | Denormalized | ⚡ Fast (column) |
| `details_count` | Aggregate | ⚠️ Medium (COUNT) |
| `total_quantity` | Aggregate | ⚠️ Medium (SUM) |
| `latest_detail` | Subquery | ⚠️ Medium (subquery) |

## Best Practices

1. **Whitelist sort columns** - Never trust user input directly
2. **Use indexed columns** for frequent sorts
3. **Denormalize** frequently sorted aggregate fields
4. **Cache** expensive sort results
5. **Limit sort options** in UI to essential fields
6. **Show sort direction** with icons
7. **Preserve filters** when sorting
8. **Test for SQL injection** vulnerabilities
