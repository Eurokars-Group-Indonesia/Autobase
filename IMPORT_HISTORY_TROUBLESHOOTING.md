# Import History Troubleshooting

## Issue: Transaction Type Salah (H vs B)

### Problem
Ketika import Transaction Body, field `transaction_type` di table `hs_import` terisi 'H' (Header) padahal seharusnya 'B' (Body).

### Root Cause
1. Kolom `transaction_type` di migration awal tidak memiliki default value
2. Jika ada issue saat insert, database bisa menggunakan nilai pertama di enum ('H')

### Solution

#### 1. Migration Fix
File: `database/migrations/2026_02_13_000000_fix_transaction_type_in_hs_import.php`

Migration ini menambahkan default value 'H' ke kolom `transaction_type`:
```php
Schema::table('hs_import', function (Blueprint $table) {
    $table->enum('transaction_type', ['H', 'B'])->default('H')->change();
});
```

#### 2. Enhanced Logging
File: `app/Jobs/LogImportHistory.php`

Ditambahkan logging untuk debugging:
```php
Log::info('LogImportHistory job started', [
    'user_id' => $this->userId,
    'transaction_type' => $this->transactionType,
    'total_row' => $this->totalRow,
]);
```

### Verification

#### Check Controller Code
**TransactionBodyController.php** (Line ~298):
```php
LogImportHistory::dispatch(
    auth()->id(),
    'B',  // ✅ Correct: Body
    $totalRows,
    $successCount,
    count($allErrors),
    $executionTime
);
```

**TransactionHeaderController.php** (Line ~268):
```php
LogImportHistory::dispatch(
    auth()->id(),
    'H',  // ✅ Correct: Header
    $totalRows,
    $successCount,
    count($allErrors),
    $executionTime
);
```

#### Check Database
```sql
-- Check recent import history
SELECT 
    import_id,
    user_id,
    transaction_type,
    total_row,
    success_row,
    executed_date
FROM hs_import
ORDER BY executed_date DESC
LIMIT 10;

-- Count by transaction type
SELECT 
    transaction_type,
    COUNT(*) as count
FROM hs_import
GROUP BY transaction_type;
```

#### Check Logs
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log | grep "LogImportHistory"

# Look for:
# - "LogImportHistory job started" with transaction_type
# - "Saving import history" with full data
# - "Import history saved successfully"
```

### Testing

#### Test Body Import
1. Go to Transaction Body → Import
2. Upload a valid CSV file
3. Check `hs_import` table:
```sql
SELECT * FROM hs_import ORDER BY import_id DESC LIMIT 1;
```
4. Verify `transaction_type` = 'B'

#### Test Header Import
1. Go to Transaction Header → Import
2. Upload a valid CSV file
3. Check `hs_import` table:
```sql
SELECT * FROM hs_import ORDER BY import_id DESC LIMIT 1;
```
4. Verify `transaction_type` = 'H'

### Fix Existing Wrong Data

If you have existing records with wrong transaction_type, you can fix them manually:

```sql
-- WARNING: This will update ALL records
-- Make sure to backup first!

-- If you can identify Body imports by some pattern
-- For example, if Body imports have specific user_id or date range
UPDATE hs_import
SET transaction_type = 'B'
WHERE transaction_type = 'H'
  AND import_id IN (
    -- Add your condition here to identify Body imports
    -- Example: SELECT import_id FROM hs_import WHERE ...
  );
```

### Prevention

1. ✅ **Default Value**: Kolom `transaction_type` sekarang punya default 'H'
2. ✅ **Explicit Value**: Controller selalu mengirim value explicit ('H' atau 'B')
3. ✅ **Logging**: Job sekarang log semua data sebelum insert
4. ✅ **Validation**: Enum hanya accept 'H' atau 'B'

### Queue Configuration

Pastikan queue worker berjalan dengan baik:

```bash
# Check queue worker status
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Common Issues

#### Issue: Job tidak jalan
**Check**:
1. Queue driver di `.env`: `QUEUE_CONNECTION=sync` atau `database`
2. Queue worker running: `php artisan queue:work`

#### Issue: Data masih salah setelah fix
**Solution**:
1. Clear cache: `php artisan cache:clear`
2. Restart queue worker: `php artisan queue:restart`
3. Check logs untuk error

#### Issue: Migration error
**Solution**:
```bash
# Rollback last migration
php artisan migrate:rollback --step=1

# Run migration again
php artisan migrate
```

### Monitoring

Add monitoring untuk import history:

```php
// In AppServiceProvider or custom monitoring
DB::listen(function ($query) {
    if (str_contains($query->sql, 'hs_import')) {
        Log::info('Import History Query', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time
        ]);
    }
});
```

### References

- Controller: `app/Http/Controllers/TransactionBodyController.php`
- Controller: `app/Http/Controllers/TransactionHeaderController.php`
- Job: `app/Jobs/LogImportHistory.php`
- Model: `app/Models/ImportHistory.php`
- Migration: `database/migrations/2026_02_13_000000_fix_transaction_type_in_hs_import.php`
