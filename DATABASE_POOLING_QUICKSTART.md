# Database Connection Pooling - Quick Start Guide

## Setup (5 menit)

### 1. Update Environment Variables

Tambahkan ke file `.env`:

```env
# Database Connection Pooling Settings
DB_PERSISTENT=false
DB_TIMEOUT=5
DB_EMULATE_PREPARES=true
DB_POOL_MIN=2
DB_POOL_MAX=10
DB_STICKY=false
DB_READ_WRITE_TIMEOUT=60
```

### 2. Clear Config Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### 3. Test Connection

```bash
php artisan db:pool:monitor
```

## Monitoring

### Check Pool Status
```bash
# Single check
php artisan db:pool:monitor

# Watch mode (refresh every 2 seconds)
php artisan db:pool:monitor --watch
```

### Output Example
```
=== Database Connection Pool Status ===

+----------------+--------+
| Setting        | Value  |
+----------------+--------+
| Connection     | mysql  |
| Driver         | mysql  |
| Host           | 127.0.0.1 |
| Database       | service_history |
| Persistent     | No     |
| Min Pool Size  | 2      |
| Max Pool Size  | 10     |
| Timeout        | 5s     |
+----------------+--------+

=== MySQL Server Status ===

+-------------------+--------+
| Metric            | Value  |
+-------------------+--------+
| Threads Connected | 5      |
| Max Connections   | 151    |
| Threads Running   | 1      |
| Threads Cached    | 2      |
| Aborted Connects  | 0      |
| Connection Errors | 0      |
| Server Uptime     | 2d 5h 30m |
+-------------------+--------+

✓ Healthy connection usage: 3.3%
```

## Production Settings

### Enable Persistent Connections

Edit `.env`:
```env
DB_PERSISTENT=true
DB_POOL_MIN=5
DB_POOL_MAX=20
```

### Restart Application
```bash
php artisan config:clear
php artisan cache:clear

# If using queue workers
php artisan queue:restart
```

## Troubleshooting

### Too Many Connections
```bash
# Check current connections
php artisan db:pool:monitor

# Reduce max pool size in .env
DB_POOL_MAX=5
```

### Slow Queries
```bash
# Enable query logging in .env
APP_DEBUG=true

# Check logs
tail -f storage/logs/laravel.log
```

### Connection Timeout
```bash
# Increase timeout in .env
DB_TIMEOUT=10
DB_READ_WRITE_TIMEOUT=120
```

## Performance Tips

1. **Use Persistent Connections in Production**
   ```env
   DB_PERSISTENT=true
   ```

2. **Set Appropriate Pool Size**
   - Low traffic: `DB_POOL_MAX=10`
   - Medium traffic: `DB_POOL_MAX=20`
   - High traffic: `DB_POOL_MAX=50`

3. **Monitor Regularly**
   ```bash
   php artisan db:pool:monitor --watch
   ```

4. **Optimize Queries**
   - Add indexes
   - Use eager loading
   - Cache results

## Next Steps

- Read full documentation: `DATABASE_POOLING.md`
- Configure MySQL server settings
- Set up monitoring alerts
- Optimize application queries
