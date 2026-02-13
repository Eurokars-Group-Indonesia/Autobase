# Windows Queue Worker Helper Scripts

## Overview
Batch files untuk memudahkan management queue worker di Windows.

## Files

### 1. start-queue-worker.bat
Menjalankan queue worker dengan auto-restart.

**Usage:**
```bash
# Double-click file atau jalankan dari command prompt
start-queue-worker.bat
```

**Features:**
- Auto-restart jika worker crash
- Display info (directory, time)
- Max execution time: 3600 seconds (1 hour)
- Sleep: 3 seconds when no jobs
- Retry: 3 times on failure

**Window akan tetap terbuka** dan menampilkan log real-time.

### 2. stop-queue-worker.bat
Menghentikan semua queue worker yang sedang berjalan.

**Usage:**
```bash
# Double-click file atau jalankan dari command prompt
stop-queue-worker.bat
```

**Features:**
- Mencari semua PHP process yang menjalankan queue:work
- Menghentikan process secara paksa (force kill)
- Menampilkan PID yang dihentikan

### 3. restart-queue-worker.bat
Restart queue worker (stop + start).

**Usage:**
```bash
# Double-click file atau jalankan dari command prompt
restart-queue-worker.bat
```

**Features:**
- Stop queue worker yang sedang berjalan
- Wait 3 seconds
- Start queue worker baru di window terpisah

**Gunakan setelah:**
- Deploy code baru
- Update dependencies
- Change .env configuration

## Quick Start

### Development (Sync Queue)

1. **Edit .env:**
```env
QUEUE_CONNECTION=sync
```

2. **Tidak perlu queue worker**, job langsung dijalankan

### Development (Database Queue)

1. **Edit .env:**
```env
QUEUE_CONNECTION=database
```

2. **Start queue worker:**
```bash
# Double-click
start-queue-worker.bat
```

3. **Minimize window** (jangan close)

### Production

Gunakan NSSM untuk production (lihat QUEUE_SETUP.md)

## Common Tasks

### Start Queue Worker
```bash
start-queue-worker.bat
```
atau
```bash
php artisan queue:work
```

### Stop Queue Worker
```bash
stop-queue-worker.bat
```
atau tekan `Ctrl+C` di window queue worker

### Restart After Deploy
```bash
restart-queue-worker.bat
```
atau
```bash
php artisan queue:restart
```

### Check if Running
```bash
# Open Task Manager (Ctrl+Shift+Esc)
# Look for php.exe process
# Or use command:
tasklist | findstr php
```

### View Logs
```bash
# Laravel logs
type storage\logs\laravel.log

# Real-time (PowerShell)
Get-Content storage\logs\laravel.log -Wait -Tail 50
```

## Troubleshooting

### Queue Worker Won't Start

**Check PHP:**
```bash
php -v
```
If error, add PHP to PATH:
- System Properties → Environment Variables → Path
- Add: `C:\laragon\bin\php\php-8.2.x`

**Check Database:**
```bash
php artisan migrate:status
```

**Check Permissions:**
- Run Command Prompt as Administrator
- Try starting manually

### Queue Worker Stops Immediately

**Check for errors:**
```bash
type storage\logs\laravel.log
```

**Check database connection:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

**Check queue table:**
```bash
php artisan queue:failed
```

### Jobs Not Processing

**Check queue connection:**
```bash
# In .env
QUEUE_CONNECTION=database  # or redis, not sync
```

**Check if worker is running:**
```bash
tasklist | findstr php
```

**Check failed jobs:**
```bash
php artisan queue:failed
```

**Retry failed jobs:**
```bash
php artisan queue:retry all
```

## Auto-Start on Windows Boot

### Method 1: Startup Folder (Simple)

1. Create shortcut of `start-queue-worker.bat`
2. Press `Win+R`, type `shell:startup`, press Enter
3. Paste shortcut in Startup folder
4. Queue worker will auto-start on boot

### Method 2: Task Scheduler (Better)

See QUEUE_SETUP.md → Windows-Specific Setup → Method 2

### Method 3: NSSM Service (Best)

See QUEUE_SETUP.md → Windows-Specific Setup → Method 1

## Tips

1. **Development**: Use `QUEUE_CONNECTION=sync` (no worker needed)
2. **Testing**: Use `start-queue-worker.bat` (easy to stop/restart)
3. **Production**: Use NSSM service (reliable, auto-restart)
4. **Always restart** after code deploy
5. **Monitor logs** regularly
6. **Check failed jobs** with `php artisan queue:failed`

## Commands Reference

```bash
# Start worker
start-queue-worker.bat

# Stop worker
stop-queue-worker.bat

# Restart worker
restart-queue-worker.bat

# Check status
tasklist | findstr php

# View logs
type storage\logs\laravel.log

# Failed jobs
php artisan queue:failed

# Retry failed
php artisan queue:retry all

# Clear failed
php artisan queue:flush

# Restart all workers
php artisan queue:restart
```

## Notes

- Batch files harus dijalankan dari project root directory
- Window queue worker harus tetap terbuka (bisa diminimize)
- Untuk production, gunakan NSSM service (lebih reliable)
- Selalu restart queue worker setelah deploy code baru
- Monitor logs untuk troubleshooting
