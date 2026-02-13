# Windows Setup Summary - Queue Worker

## Quick Setup Guide

### For Development (Recommended)

**1. Edit .env:**
```env
QUEUE_CONNECTION=sync
```

**2. Done!**
- Tidak perlu queue worker
- Job langsung dijalankan
- Mudah untuk debugging

### For Testing Queue System

**1. Edit .env:**
```env
QUEUE_CONNECTION=database
```

**2. Start queue worker:**
```bash
# Double-click file ini:
start-queue-worker.bat
```

**3. Minimize window** (jangan close)

**4. Test search** di Transaction Header atau Body

**5. Check logs:**
```bash
type storage\logs\laravel.log
```

### For Production

**1. Download NSSM:**
- https://nssm.cc/download
- Extract ke `C:\nssm`

**2. Install service (as Administrator):**
```bash
C:\nssm\win64\nssm.exe install LaravelQueue
```

**3. Configure in NSSM GUI:**
- Path: `C:\laragon\bin\php\php-8.2.x\php.exe`
- Startup directory: `C:\laragon\www\service_history`
- Arguments: `artisan queue:work --sleep=3 --tries=3 --max-time=3600`

**4. Start service:**
```bash
nssm start LaravelQueue
```

**5. Verify:**
```bash
nssm status LaravelQueue
```

## Helper Scripts

### start-queue-worker.bat
✅ Start queue worker dengan auto-restart
✅ Display real-time logs
✅ Double-click untuk menjalankan

### stop-queue-worker.bat
✅ Stop semua queue worker
✅ Force kill PHP processes
✅ Double-click untuk menjalankan

### restart-queue-worker.bat
✅ Restart queue worker
✅ Gunakan setelah deploy
✅ Double-click untuk menjalankan

## Common Commands

```bash
# Start worker
start-queue-worker.bat

# Stop worker
stop-queue-worker.bat

# Restart worker (after deploy)
restart-queue-worker.bat

# Check if running
tasklist | findstr php

# View logs
type storage\logs\laravel.log

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## Troubleshooting

### Queue worker won't start?
```bash
# Check PHP
php -v

# Check database
php artisan migrate:status

# Check logs
type storage\logs\laravel.log
```

### Jobs not processing?
```bash
# Check .env
QUEUE_CONNECTION=database  # not sync

# Check if worker running
tasklist | findstr php

# Check failed jobs
php artisan queue:failed
```

### After code deploy?
```bash
# Always restart queue worker
restart-queue-worker.bat

# Or if using NSSM
nssm restart LaravelQueue
```

## Documentation

📖 **QUEUE_SETUP.md** - Complete queue setup guide
📖 **WINDOWS_QUEUE_HELPER.md** - Batch files documentation
📖 **SEARCH_HISTORY_FEATURE.md** - Search history feature docs

## Support

For detailed documentation, see:
- QUEUE_SETUP.md → Windows-Specific Setup
- WINDOWS_QUEUE_HELPER.md → Helper Scripts Guide
