# Query & Search Optimization Skill (Large Datasets)

Provides query optimization, search patterns, and performance guidelines for handling millions of rows in Laravel/MySQL.

## Database Indexing Strategies

### Essential Indexes for Search

```php
// Single column indexes
Schema::table('transactions', function (Blueprint $table) {
    $table->index('user_id');
    $table->index('status');
    $table->index('created_at');
    $table->index('transaction_date');
});

// Composite indexes (order matters!)
Schema::table('transactions', function (Blueprint $table) {
    // For: WHERE user_id = ? AND status = ?
    $table->index(['user_id', 'status']);
    
    // For: WHERE status = ? AND created_at > ?
    $table->index(['status', 'created_at']);
    
    // For: WHERE user_id = ? ORDER BY created_at DESC
    $table->index(['user_id', 'created_at']);
});

// Covering indexes (include all queried columns)
Schema::table('users', function (Blueprint $table) {
    // Index includes columns typically selected together
    $table->index(['status', 'email', 'name']);
});
```

### Index Selection Guidelines

```php
// ✅ Good: Index matches WHERE clause column order
// Index: [user_id, status, created_at]
User::where('user_id', $id)
    ->where('status', 'active')
    ->where('created_at', '>', $date)
    ->get();

// ❌ Bad: Skipping first index column
User::where('status', 'active')  // Skips user_id
    ->where('created_at', '>', $date)
    ->get(); // Index not used efficiently
```

### Full-Text Indexes (MySQL)

```php
// Create full-text index
Schema::table('transactions', function (Blueprint $table) {
    $table->fullText(['description', 'reference_number']);
});

// Full-text search
$results = DB::table('transactions')
    ->whereFullText(['description', 'reference_number'], 'search term')
    ->get();

// With relevance scoring
$results = DB::table('transactions')
    ->select('*', DB::raw('MATCH(description, reference_number) AGAINST("search term") as relevance'))
    ->whereFullText(['description', 'reference_number'], 'search term')
    ->orderByDesc('relevance')
    ->get();
```

## Query Optimization Patterns

### 1. Efficient Pagination (Large Datasets)

```php
// ❌ Bad: OFFSET becomes slow with large numbers
$items = Transaction::where('user_id', $userId)
    ->offset(100000)  // Very slow!
    ->limit(20)
    ->get();

// ✅ Good: Keyset pagination (cursor-based)
$items = Transaction::where('user_id', $userId)
    ->where('id', '>', $lastSeenId)
    ->orderBy('id')
    ->limit(20)
    ->get();

// ✅ Laravel 11+ simplePaginate (uses keyset)
$items = Transaction::where('user_id', $userId)
    ->orderByDesc('id')
    ->simplePaginate(20);

// ✅ Custom cursor pagination
class CursorPaginator {
    public function paginate($query, $cursor, $limit = 20) {
        return $query->where('id', '>', $cursor)
            ->orderBy('id')
            ->limit($limit + 1)
            ->get()
            ->tap(function ($items) use ($limit) {
                $this->hasMore = $items->count() > $limit;
            })
            ->take($limit);
    }
}
```

### 2. Query Scopes for Reusability

```php
// In Model
class Transaction extends Model {
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }
    
    public function scopeDateRange($query, $start, $end) {
        return $query->whereBetween('transaction_date', [$start, $end]);
    }
    
    public function scopeUser($query, $userId) {
        return $query->where('user_id', $userId);
    }
    
    public function scopeSearch($query, $term) {
        return $query->where(function($q) use ($term) {
            $q->where('description', 'LIKE', "%{$term}%")
              ->orWhere('reference_number', 'LIKE', "%{$term}%");
        });
    }
}

// Usage
$results = Transaction::user($userId)
    ->active()
    ->dateRange($startDate, $endDate)
    ->search($searchTerm)
    ->orderByDesc('created_at')
    ->simplePaginate(20);
```

### 3. Eager Loading to Prevent N+1

```php
// ❌ Bad: N+1 queries
$transactions = Transaction::where('status', 'pending')->get();
foreach ($transactions as $t) {
    echo $t->user->name;  // Query per transaction
}

// ✅ Good: Eager load relationships
$transactions = Transaction::with(['user', 'category', 'metadata'])
    ->where('status', 'pending')
    ->get();

// ✅ Eager load with constraints
$transactions = Transaction::with(['user' => function($q) {
        $q->select('id', 'name', 'email');
    }])
    ->where('status', 'pending')
    ->get();

// ✅ Eager load nested relationships
$transactions = Transaction::with('user.roles', 'category.parent')
    ->get();
```

### 4. Select Only Needed Columns

```php
// ❌ Bad: Selecting all columns
$users = User::all();

// ✅ Good: Select only needed columns
$users = User::select('id', 'name', 'email')->get();

// ✅ When using relationships
$transactions = Transaction::with(['user' => function($q) {
        $q->select('id', 'name');  // Only select needed
    }])
    ->select('id', 'user_id', 'amount', 'status')
    ->get();
```

### 5. Use EXISTS Instead of IN for Large Subqueries

```php
// ❌ Bad: IN with large subquery
$users = User::whereIn('id', function($q) {
        $q->select('user_id')
          ->from('transactions')
          ->where('amount', '>', 1000);
    })->get();

// ✅ Good: EXISTS (stops at first match)
$users = User::whereExists(function($q) {
        $q->select(DB::raw(1))
          ->from('transactions')
          ->whereRaw('transactions.user_id = users.id')
          ->where('amount', '>', 1000);
    })->get();

// ✅ Also good: Join (when you need transaction data too)
$users = User::join('transactions', 'users.id', '=', 'transactions.user_id')
    ->where('transactions.amount', '>', 1000)
    ->select('users.*')
    ->distinct()
    ->get();
```

## Search Implementation Patterns

### 1. Advanced Search Builder

```php
class TransactionSearch {
    public function __construct(
        private Builder $query
    ) {}
    
    public static function search(array $filters): LengthAwarePaginator {
        $query = Transaction::query();
        
        $search = new self($query);
        
        // Apply filters
        $search->applyUserFilter($filters['user_id'] ?? null);
        $search->applyDateRange($filters['date_from'] ?? null, $filters['date_to'] ?? null);
        $search->applyStatusFilter($filters['status'] ?? null);
        $search->applyAmountRange($filters['min_amount'] ?? null, $filters['max_amount'] ?? null);
        $search->applySearchTerm($filters['search'] ?? null);
        
        // Sorting
        $search->applySorting($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');
        
        return $search->query->simplePaginate(20);
    }
    
    private function applyUserFilter(?int $userId): void {
        if ($userId) {
            $this->query->where('user_id', $userId);
        }
    }
    
    private function applyDateRange(?string $from, ?string $to): void {
        if ($from) {
            $this->query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $this->query->whereDate('created_at', '<=', $to);
        }
    }
    
    private function applyStatusFilter(?string $status): void {
        if ($status) {
            $this->query->where('status', $status);
        }
    }
    
    private function applyAmountRange(?float $min, ?float $max): void {
        if ($min !== null) {
            $this->query->where('amount', '>=', $min);
        }
        if ($max !== null) {
            $this->query->where('amount', '<=', $max);
        }
    }
    
    private function applySearchTerm(?string $term): void {
        if ($term) {
            $this->query->where(function($q) use ($term) {
                $q->where('description', 'LIKE', "%{$term}%")
                  ->orWhere('reference_number', 'LIKE', "%{$term}%");
            });
        }
    }
    
    private function applySorting(string $sortBy, string $order): void {
        $allowedSorts = ['created_at', 'transaction_date', 'amount', 'status'];
        
        if (in_array($sortBy, $allowedSorts)) {
            $this->query->orderBy($sortBy, strtolower($order) === 'asc' ? 'asc' : 'desc');
        } else {
            $this->query->orderByDesc('created_at');
        }
    }
}

// Usage in Controller
$results = TransactionSearch::search($request->all());
```

### 2. Search with Caching

```php
use Illuminate\Support\Facades\Cache;

class CachedTransactionSearch {
    public function search(array $filters, int $userId): LengthAwarePaginator {
        // Create cache key from filters
        $cacheKey = 'user_' . $userId . '_search_' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function() use ($filters, $userId) {
            return TransactionSearch::search(array_merge($filters, ['user_id' => $userId]));
        });
    }
}

// Clear cache when data changes
class TransactionObserver {
    public function updated(Transaction $transaction) {
        Cache::tags(['user_' . $transaction->user_id])->flush();
    }
}
```

### 3. Search with Query Logging (Debug)

```php
// Enable query logging (development only!)
DB::enableQueryLog();

// Run search
$results = TransactionSearch::search($filters);

// Check queries
$queries = DB::getQueryLog();
foreach ($queries as $query) {
    Log::info('Query: ' . $query['query']);
    Log::info('Time: ' . $query['time'] . 'ms');
}

// Check for N+1
// Look for repeated similar queries in the log
```

## Database Query Optimization

### 1. EXPLAIN Analysis

```php
// Check query execution plan
$explain = DB::select('EXPLAIN SELECT * FROM transactions WHERE user_id = ? AND status = ?', [1, 'pending']);

// Key things to check:
// - type: Should be 'ref' or 'range', not 'ALL' (full table scan)
// - key: Which index is being used
// - rows: Estimated rows to examine (lower is better)
// - Extra: Should not have "Using temporary" or "Using filesort"
```

### 2. Query Performance Monitoring

```php
// Log slow queries (in AppServiceProvider)
DB::listen(function($query) {
    if ($query->time > 100) {  // > 100ms
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
        ]);
    }
});
```

### 3. Use Database Views for Complex Queries

```php
// Create a view for complex reporting
DB::statement('
    CREATE OR REPLACE VIEW transaction_summary AS
    SELECT 
        user_id,
        status,
        DATE(transaction_date) as date,
        COUNT(*) as count,
        SUM(amount) as total_amount,
        AVG(amount) as avg_amount
    FROM transactions
    GROUP BY user_id, status, DATE(transaction_date)
');

// Query the view (much faster)
$summary = DB::table('transaction_summary')
    ->where('user_id', $userId)
    ->get();
```

## Caching Strategies

### 1. Query Result Caching

```php
use Illuminate\Support\Facades\Cache;

// Cache query results
$transactions = Cache::remember(
    "transactions.user_{$userId}.page_{$page}",
    now()->addMinutes(10),
    function() use ($userId, $page) {
        return Transaction::where('user_id', $userId)
            ->orderByDesc('id')
            ->simplePaginate(20);
    }
);

// Cache tags for selective invalidation
$transactions = Cache::tags(["user_{$userId}", 'transactions'])
    ->remember($cacheKey, now()->addMinutes(10), $callback);

// Invalidate when data changes
Cache::tags(["user_{$userId}"])->flush();
```

### 2. Count Cache

```php
// Cache expensive COUNT queries
$count = Cache::remember(
    "transactions.user_{$userId}.count",
    now()->addHour(),
    function() use ($userId) {
        return Transaction::where('user_id', $userId')->count();
    }
);

// Update cache on create/delete
class TransactionObserver {
    public function created(Transaction $t) {
        Cache::forget("transactions.user_{$t->user_id}.count");
    }
    
    public function deleted(Transaction $t) {
        Cache::forget("transactions.user_{$t->user_id}.count");
    }
}
```

## Laravel-Specific Optimizations

### 1. Chunking for Large Result Sets

```php
// Process large datasets without memory issues
Transaction::where('status', 'pending')
    ->orderBy('id')
    ->chunk(500, function ($transactions) {
        foreach ($transactions as $transaction) {
            // Process...
        }
    });

// With lazy loading (memory efficient)
Transaction::where('status', 'pending')
    ->lazy()
    ->each(function ($transaction) {
        // Process...
    });
```

### 2. Subquery Selects

```php
// Get latest transaction per user efficiently
$users = User::withCount(['transactions' => function ($q) {
        $q->select(DB::raw('count(*)'))
          ->where('status', 'completed');
    }])
    ->with(['latestTransaction' => function ($q) {
        $q->latest()->limit(1);
    }])
    ->get();

// Using subquery in select
$users = User::addSelect([
        'latest_transaction_date' => Transaction::select('created_at')
            ->whereColumn('transactions.user_id', 'users.id')
            ->orderByDesc('created_at')
            ->limit(1)
    ])
    ->get();
```

### 3. Union for Complex Searches

```php
// Search across multiple tables
$basicSearch = DB::table('transactions')
    ->select('id', 'transaction_date as date', 'amount', 'transactions as type')
    ->where('description', 'LIKE', "%{$term}%");

$referenceSearch = DB::table('transactions')
    ->select('id', 'transaction_date as date', 'amount', 'transactions as type')
    ->where('reference_number', 'LIKE', "%{$term}%");

$results = $basicSearch->union($referenceSearch)
    ->orderByDesc('date')
    ->simplePaginate(20);
```

## MySQL Configuration for Large Datasets

### Recommended my.cnf Settings

```ini
[mysqld]
# Buffer pool (70-80% of RAM for dedicated DB server)
innodb_buffer_pool_size = 4G

# Log file size (25% of buffer pool)
innodb_log_file_size = 1G

# Flush method
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Connection settings
max_connections = 200
thread_cache_size = 50

# Query cache (MySQL 5.7, disabled in 8.0)
query_cache_type = 1
query_cache_size = 64M

# Table cache
table_open_cache = 2000
table_definition_cache = 2000

# Sort and join buffers
sort_buffer_size = 2M
join_buffer_size = 2M
read_buffer_size = 2M
```

## Search UI Implementation (Bootstrap)

```blade
{{-- Search Form --}}
<form method="GET" action="{{ route('transactions.index') }}" class="mb-4">
    <div class="row g-3">
        <div class="col-md-3">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Search..."
                value="{{ request('search') }}"
            >
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Search
            </button>
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Clear
            </a>
        </div>
    </div>
</form>

{{-- Results with count --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted">
        Found {{ $transactions->total() }} results
    </span>
    
    {{-- Sort dropdown --}}
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            Sort: {{ request('sort_by', 'created_at') }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="?sort_by=created_at&sort_order=desc">Newest First</a></li>
            <li><a class="dropdown-item" href="?sort_by=created_at&sort_order=asc">Oldest First</a></li>
            <li><a class="dropdown-item" href="?sort_by=amount&sort_order=desc">Highest Amount</a></li>
            <li><a class="dropdown-item" href="?sort_by=amount&sort_order=asc">Lowest Amount</a></li>
        </ul>
    </div>
</div>

{{-- Pagination --}}
{{ $transactions->links() }}
```

## Performance Checklist

- [ ] Add indexes on frequently queried columns
- [ ] Use composite indexes for multi-column WHERE clauses
- [ ] Implement cursor/keyset pagination
- [ ] Eager load relationships (prevent N+1)
- [ ] Select only needed columns
- [ ] Cache expensive queries
- [ ] Use EXISTS instead of IN for large subqueries
- [ ] Monitor slow queries (>100ms)
- [ ] Use EXPLAIN to analyze query plans
- [ ] Implement search builder pattern
- [ ] Use chunking for bulk operations
- [ ] Consider full-text search for text-heavy queries
- [ ] Archive old data to separate tables
