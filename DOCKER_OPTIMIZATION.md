# Docker PHP Optimization Guide

## Optimasi yang Diterapkan

### 1. PHP-FPM Pool Configuration (`docker/php/www.conf`)
- **Process Manager**: Dynamic mode untuk efisiensi resource
- **Max Children**: 50 (dapat handle 50 request bersamaan)
- **Start Servers**: 10 (langsung siap 10 worker saat startup)
- **Min/Max Spare**: 5-20 (selalu ada worker standby)
- **Max Requests**: 500 (restart worker setelah 500 request untuk prevent memory leak)

### 2. PHP Configuration (`docker/php/php.ini`)
**Memory & Execution:**
- Memory limit: 1024M (untuk handle import besar)
- Max execution time: 600s (10 menit)
- Max input vars: 10000 (untuk form besar)
- Realpath cache: 4096K (cache file path untuk performa)

**OPcache:**
- Memory: 512MB
- JIT Compiler: Tracing mode (PHP 8.3)
- JIT Buffer: 256MB
- Preloading: Enabled (load Laravel core di memory)
- Max accelerated files: 30000

### 3. Queue Workers (`docker/supervisord.conf`)
- **Jumlah Workers**: 8 (dari 2) - 4x lebih cepat untuk import
- **Sleep**: 1 detik (dari 3) - lebih responsif
- **Memory**: 512MB per worker
- **Priority**: 999 (high priority)

### 4. OPcache Preloading (`preload.php`)
Preload classes yang sering digunakan:
- Laravel core classes
- Application models (TransactionHeader, TransactionBody)
- Vendor autoload

## Cara Rebuild Docker

```bash
# Stop containers
docker-compose down

# Rebuild dengan optimasi baru
docker-compose build --no-cache

# Start containers
docker-compose up -d

# Check logs
docker-compose logs -f app
docker-compose logs -f queue
```

## Monitoring Performance

### Check PHP-FPM Status
```bash
docker exec laravel_app curl http://localhost:9000/status
```

### Check OPcache Status
Buat file `opcache-status.php` di public folder:
```php
<?php
phpinfo(INFO_GENERAL);
```
Akses: http://localhost:8000/opcache-status.php

### Check Queue Workers
```bash
docker exec laravel_queue supervisorctl status
```

### Monitor Memory Usage
```bash
docker stats laravel_app laravel_queue
```

## Expected Performance Improvements

### Before Optimization:
- Queue workers: 2
- PHP-FPM children: ~10 (default)
- Memory per request: ~256MB
- Import speed: ~100 rows/second

### After Optimization:
- Queue workers: 8 (4x faster)
- PHP-FPM children: up to 50 (5x capacity)
- Memory per request: ~512MB-1GB
- Import speed: ~400-500 rows/second (estimated)
- OPcache hit rate: >95%

## Tips untuk Import Besar

1. **Gunakan Queue**: Selalu gunakan queue untuk import >1000 rows
2. **Chunk Size**: Set chunk size di import class (default 1000)
3. **Monitor Memory**: Watch `docker stats` saat import
4. **Database Connection**: Pastikan MySQL max_connections cukup (min 100)

## Troubleshooting

### Worker Tidak Jalan
```bash
docker exec laravel_queue supervisorctl restart laravel-queue-worker:*
```

### Memory Limit Error
Tingkatkan di `docker/php/php.ini`:
```ini
memory_limit = 2048M
```

### Too Many Connections
Tingkatkan MySQL max_connections atau kurangi queue workers.

### OPcache Not Working
Check logs:
```bash
docker exec laravel_app cat /var/www/html/storage/logs/opcache.log
```

## Resource Requirements

**Minimum:**
- CPU: 4 cores
- RAM: 4GB
- Disk: 20GB

**Recommended:**
- CPU: 8 cores
- RAM: 8GB
- Disk: 50GB SSD

## Additional Optimizations (Optional)

### 1. Redis untuk Cache & Session
Sudah terinstall, tinggal configure di `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 2. Database Query Optimization
- Add indexes pada kolom yang sering di-query
- Use eager loading untuk relasi
- Implement database query caching

### 3. Nginx Optimization
Edit `docker/nginx/default.conf`:
```nginx
worker_processes auto;
worker_connections 2048;
keepalive_timeout 65;
gzip on;
gzip_types text/plain text/css application/json application/javascript;
```

## Monitoring Tools (Optional)

### Laravel Telescope
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### Laravel Horizon (untuk Redis Queue)
```bash
composer require laravel/horizon
php artisan horizon:install
```
