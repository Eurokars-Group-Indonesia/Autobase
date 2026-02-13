# Enable Database Monitoring Middleware (Optional)

Middleware untuk monitoring database connections sudah dibuat di:
`app/Http/Middleware/MonitorDatabaseConnections.php`

## Cara Mengaktifkan

Edit file `bootstrap/app.php` dan tambahkan middleware:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'permission' => \App\Http\Middleware\CheckPermission::class,
        'role' => \App\Http\Middleware\CheckRole::class,
        'db.monitor' => \App\Http\Middleware\MonitorDatabaseConnections::class,
    ]);
    
    // Add security headers to all web requests
    $middleware->web(append: [
        \App\Http\Middleware\SecurityHeaders::class,
        \App\Http\Middleware\MonitorDatabaseConnections::class, // Add this line
    ]);
})
```

## Fitur Middleware

1. **Query Count Tracking** - Hitung jumlah query per request
2. **Execution Time** - Monitor waktu eksekusi request
3. **Memory Usage** - Track penggunaan memory
4. **Slow Query Alert** - Log jika request > 1000ms atau query > 50
5. **Debug Headers** - Tambahkan headers X-Database-Queries dan X-Execution-Time

## Output

Middleware akan menambahkan headers di response (saat APP_DEBUG=true):
- `X-Database-Queries: 15`
- `X-Execution-Time: 234.56ms`

Dan log warning jika ada masalah:
```
[2026-01-27 10:30:45] local.WARNING: Slow request or too many queries
{
    "url": "http://localhost/transactions",
    "method": "GET",
    "execution_time": "1234.56ms",
    "query_count": 75,
    "memory_usage": "12.5MB"
}
```
