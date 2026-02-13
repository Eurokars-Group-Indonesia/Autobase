# Database Connection Pooling

Database connection pooling telah dikonfigurasi untuk meningkatkan performa aplikasi dengan mengelola koneksi database secara efisien.

## Fitur

### 1. Persistent Connections
- Koneksi database dapat di-reuse antar request
- Mengurangi overhead membuat koneksi baru
- Meningkatkan response time

### 2. Connection Pool Management
- **Min Connections**: Jumlah minimum koneksi yang selalu tersedia
- **Max Connections**: Jumlah maksimum koneksi yang dapat dibuat
- Automatic reconnection jika koneksi terputus

### 3. Timeout Configuration
- **Connection Timeout**: Waktu maksimal untuk membuat koneksi
- **Read/Write Timeout**: Waktu maksimal untuk operasi baca/tulis
- **Wait Timeout**: Waktu idle sebelum koneksi ditutup

### 4. Query Monitoring (Development)
- Log semua query database saat `APP_DEBUG=true`
- Monitor waktu eksekusi query
- Track connection yang digunakan

## Konfigurasi

### Environment Variables

Tambahkan ke file `.env`:

```env
# Database Connection Pooling Settings
# Enable persistent connections (recommended for production)
DB_PERSISTENT=false

# Connection timeout in seconds
DB_TIMEOUT=5

# Enable emulated prepares for better performance
DB_EMULATE_PREPARES=true

# Minimum connections in pool
DB_POOL_MIN=2

# Maximum connections in pool
DB_POOL_MAX=10

# Sticky connections (keep same connection for request)
DB_STICKY=false

# Read/Write timeout in seconds
DB_READ_WRITE_TIMEOUT=60
```

### Rekomendasi Setting

#### Development
```env
DB_PERSISTENT=false
DB_POOL_MIN=2
DB_POOL_MAX=5
DB_TIMEOUT=5
```

#### Production (Low Traffic)
```env
DB_PERSISTENT=true
DB_POOL_MIN=5
DB_POOL_MAX=20
DB_TIMEOUT=10
```

#### Production (High Traffic)
```env
DB_PERSISTENT=true
DB_POOL_MIN=10
DB_POOL_MAX=50
DB_TIMEOUT=15
```

## Cara Kerja

### 1. Connection Reuse
Dengan persistent connections, PHP akan mereuse koneksi yang sudah ada:
```php
// Koneksi pertama - membuat koneksi baru
DB::table('users')->get();

// Koneksi kedua - reuse koneksi yang ada
DB::table('transactions')->get();
```

### 2. Automatic Reconnection
Jika koneksi terputus, sistem akan otomatis reconnect:
```php
// DatabasePoolServiceProvider akan handle reconnection
DB::beforeExecuting(function ($query, $bindings, $connection) {
    try {
        $connection->getPdo();
    } catch (\Exception $e) {
        $connection->reconnect();
    }
});
```

### 3. Pool Limits
Pool akan menjaga jumlah koneksi dalam batas yang ditentukan:
- Minimum connections selalu ready
- Maximum connections tidak akan dilampaui
- Idle connections akan ditutup setelah timeout

## MySQL Server Configuration

Untuk hasil optimal, sesuaikan konfigurasi MySQL server (`my.cnf` atau `my.ini`):

```ini
[mysqld]
# Maximum connections
max_connections = 200

# Connection timeout
connect_timeout = 10

# Wait timeout (idle connection)
wait_timeout = 600
interactive_timeout = 600

# Thread cache for connection reuse
thread_cache_size = 50

# Query cache (optional)
query_cache_type = 1
query_cache_size = 64M
```

## Monitoring

### Check Active Connections
```sql
-- Lihat jumlah koneksi aktif
SHOW STATUS LIKE 'Threads_connected';

-- Lihat semua koneksi
SHOW PROCESSLIST;

-- Lihat max connections
SHOW VARIABLES LIKE 'max_connections';
```

### Laravel Logs
Saat `APP_DEBUG=true`, semua query akan di-log:
```
[2025-01-27 10:30:45] local.DEBUG: Database Query
{
    "sql": "select * from users where id = ?",
    "bindings": [1],
    "time": "2.5ms",
    "connection": "mysql"
}
```

## Troubleshooting

### Too Many Connections Error
```
SQLSTATE[HY000] [1040] Too many connections
```

**Solusi:**
1. Tingkatkan `max_connections` di MySQL
2. Kurangi `DB_POOL_MAX` di `.env`
3. Pastikan koneksi ditutup dengan benar

### Connection Timeout
```
SQLSTATE[HY000] [2002] Connection timed out
```

**Solusi:**
1. Tingkatkan `DB_TIMEOUT` di `.env`
2. Check network connectivity
3. Pastikan MySQL server running

### Slow Queries
```
Query took 5000ms to execute
```

**Solusi:**
1. Add database indexes
2. Optimize query dengan `EXPLAIN`
3. Enable query cache di MySQL
4. Consider using Redis cache

## Best Practices

### 1. Use Persistent Connections in Production
```env
# Production
DB_PERSISTENT=true
```

### 2. Set Appropriate Pool Size
- Terlalu kecil: bottleneck
- Terlalu besar: waste resources
- Rule of thumb: `(CPU cores * 2) + disk spindles`

### 3. Monitor Connection Usage
```php
// Check connection count
$connections = DB::select('SHOW STATUS LIKE "Threads_connected"');
Log::info('Active connections: ' . $connections[0]->Value);
```

### 4. Close Long-Running Connections
```php
// Untuk long-running jobs
DB::disconnect();
// ... do work ...
DB::reconnect();
```

### 5. Use Transactions Wisely
```php
DB::transaction(function () {
    // Keep transactions short
    // Don't hold connections too long
});
```

## Performance Benefits

### Before Connection Pooling
- Setiap request membuat koneksi baru: ~50-100ms overhead
- 100 requests = 5-10 detik overhead

### After Connection Pooling
- Reuse existing connections: ~1-5ms overhead
- 100 requests = 0.1-0.5 detik overhead

**Improvement: 10-20x faster connection handling**

## Security Considerations

### 1. Limit Max Connections
Prevent DoS attacks dengan membatasi max connections:
```env
DB_POOL_MAX=50
```

### 2. Set Connection Timeout
Prevent hanging connections:
```env
DB_TIMEOUT=10
```

### 3. Monitor Suspicious Activity
```php
DB::listen(function ($query) {
    if ($query->time > 1000) {
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'time' => $query->time,
        ]);
    }
});
```

## Additional Resources

- [Laravel Database Documentation](https://laravel.com/docs/database)
- [MySQL Connection Pooling](https://dev.mysql.com/doc/refman/8.0/en/connection-pooling.html)
- [PDO Persistent Connections](https://www.php.net/manual/en/pdo.connections.php)
