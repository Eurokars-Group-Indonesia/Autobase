# Queue Setup Guide

## Overview
Aplikasi ini menggunakan Laravel Queue untuk async processing, khususnya untuk logging search history agar tidak mengganggu response time.

## Queue Configuration

### Development Environment

Untuk development, gunakan `sync` driver (langsung dijalankan tanpa queue):

```env
QUEUE_CONNECTION=sync
```

Dengan konfigurasi ini, job akan dijalankan secara synchronous (langsung), tidak perlu menjalankan queue worker.

### Production Environment

Untuk production, gunakan `database` atau `redis` driver untuk async processing.

#### Option 1: Database Queue (Simple)

1. **Update .env**:
```env
QUEUE_CONNECTION=database
```

2. **Migrate jobs table** (jika belum):
```bash
php artisan queue:table
php artisan migrate
```

3. **Run queue worker**:
```bash
php artisan queue:work
```

4. **Run as background service** (Linux):
```bash
# Using supervisor
sudo apt-get install supervisor

# Create supervisor config: /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/service_history/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/service_history/storage/logs/worker.log
stopwaitsecs=3600

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

5. **Run as background service** (Windows):

**Option A: Using NSSM (Recommended)**

NSSM (Non-Sucking Service Manager) adalah tool terbaik untuk menjalankan queue worker sebagai Windows Service.

```bash
# 1. Download NSSM
# Download dari: https://nssm.cc/download
# Extract ke folder, misalnya: C:\nssm

# 2. Buka Command Prompt as Administrator

# 3. Install service
C:\nssm\win64\nssm.exe install LaravelQueue

# 4. NSSM GUI akan terbuka, isi:
# Path: C:\laragon\bin\php\php-8.2.x\php.exe
# Startup directory: C:\laragon\www\service_history
# Arguments: artisan queue:work --sleep=3 --tries=3 --max-time=3600

# 5. Tab "Details" (optional):
# Display name: Laravel Queue Worker
# Description: Laravel Queue Worker for Service History

# 6. Tab "I/O" (optional):
# Output (stdout): C:\laragon\www\service_history\storage\logs\queue-worker.log
# Error (stderr): C:\laragon\www\service_history\storage\logs\queue-worker-error.log

# 7. Klik "Install service"

# 8. Start service
nssm start LaravelQueue

# 9. Check status
nssm status LaravelQueue

# 10. Stop service (jika perlu)
nssm stop LaravelQueue

# 11. Restart service (setelah deploy)
nssm restart LaravelQueue

# 12. Remove service (jika perlu uninstall)
nssm remove LaravelQueue confirm
```

**Option B: Using Task Scheduler**

Menggunakan Windows Task Scheduler untuk auto-start queue worker saat boot.

```bash
# 1. Buat batch file: C:\laragon\www\service_history\start-queue.bat
@echo off
cd C:\laragon\www\service_history
C:\laragon\bin\php\php-8.2.x\php.exe artisan queue:work --sleep=3 --tries=3

# 2. Buka Task Scheduler (taskschd.msc)

# 3. Create Basic Task
# Name: Laravel Queue Worker
# Description: Run Laravel queue worker for Service History

# 4. Trigger: When the computer starts

# 5. Action: Start a program
# Program/script: C:\laragon\www\service_history\start-queue.bat

# 6. Settings:
# ✓ Allow task to be run on demand
# ✓ Run task as soon as possible after a scheduled start is missed
# ✓ If the task fails, restart every: 1 minute
# ✓ Attempt to restart up to: 3 times

# 7. Finish
```

**Option C: Using Laragon Auto-Start (Development Only)**

Untuk development di Laragon, bisa menggunakan auto-start script.

```bash
# 1. Buat file: C:\laragon\www\service_history\queue-worker.bat
@echo off
:loop
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
timeout /t 5
goto loop

# 2. Buat shortcut dari queue-worker.bat

# 3. Copy shortcut ke:
# C:\Users\[YourUsername]\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup

# 4. Queue worker akan auto-start saat Windows boot
```

**Option D: Manual Start (Development)**

Untuk development, cukup jalankan manual di terminal:

```bash
# Buka terminal di folder project
cd C:\laragon\www\service_history

# Jalankan queue worker
php artisan queue:work

# Atau dengan parameter
php artisan queue:work --sleep=3 --tries=3
```

#### Option 2: Redis Queue (Recommended for Production)

1. **Install Redis**:
```bash
# Ubuntu/Debian
sudo apt-get install redis-server

# Windows (using Memurai)
# Download from: https://www.memurai.com/
```

2. **Install PHP Redis extension**:
```bash
# Ubuntu/Debian
sudo apt-get install php-redis

# Windows (Laragon)
# Enable redis extension in php.ini
extension=redis
```

3. **Install Predis package**:
```bash
composer require predis/predis
```

4. **Update .env**:
```env
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

5. **Run queue worker**:
```bash
php artisan queue:work redis
```

## Queue Worker Commands

### Basic Commands

```bash
# Start queue worker
php artisan queue:work

# Start with specific connection
php artisan queue:work redis

# Process only one job
php artisan queue:work --once

# Process jobs for 60 seconds then exit
php artisan queue:work --max-time=60

# Process 100 jobs then exit
php artisan queue:work --max-jobs=100

# Sleep 3 seconds when no jobs available
php artisan queue:work --sleep=3

# Retry failed jobs 3 times
php artisan queue:work --tries=3
```

### Monitoring Commands

```bash
# List all failed jobs
php artisan queue:failed

# Retry a specific failed job
php artisan queue:retry {id}

# Retry all failed jobs
php artisan queue:retry all

# Delete a failed job
php artisan queue:forget {id}

# Flush all failed jobs
php artisan queue:flush

# Listen to queue (auto-reload on code changes)
php artisan queue:listen
```

### Restart Queue Worker

```bash
# Gracefully restart all queue workers
php artisan queue:restart
```

**Important**: Setelah deploy code baru, selalu restart queue worker!

## Jobs in This Application

### LogSearchHistory Job

- **File**: `app/Jobs/LogSearchHistory.php`
- **Purpose**: Log search history ke database secara async
- **Queue**: default
- **Triggered by**: 
  - Transaction Header search
  - Transaction Body search
- **Parameters**:
  - userId
  - search query
  - date_from
  - date_to
  - execution_time
  - transaction_type (H/B)

## Monitoring Queue

### Check Queue Status

```bash
# Check jobs in queue
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed
```

### Queue Dashboard (Optional)

Install Laravel Horizon for better queue monitoring:

```bash
composer require laravel/horizon

php artisan horizon:install

php artisan migrate

# Start Horizon
php artisan horizon

# Access dashboard at: http://your-app.test/horizon
```

## Windows-Specific Setup

### Prerequisites

1. **PHP CLI** harus bisa diakses dari command line
2. **Composer** sudah terinstall
3. **Database** sudah running (MySQL/MariaDB)

### Quick Start for Development (Windows)

```bash
# 1. Buka Command Prompt atau PowerShell
cd C:\laragon\www\service_history

# 2. Set queue connection ke sync untuk development
# Edit .env:
QUEUE_CONNECTION=sync

# 3. Tidak perlu queue worker, job langsung dijalankan
```

### Production Setup (Windows)

#### Method 1: NSSM (Recommended)

**Step 1: Download NSSM**
- Download dari: https://nssm.cc/download
- Extract ke folder, contoh: `C:\nssm`
- Pilih `win64` untuk Windows 64-bit atau `win32` untuk 32-bit

**Step 2: Install Service**
```bash
# Buka Command Prompt as Administrator
cd C:\nssm\win64

# Install service dengan GUI
nssm.exe install LaravelQueue
```

**Step 3: Configure Service (NSSM GUI)**
- **Path**: `C:\laragon\bin\php\php-8.2.x\php.exe` (sesuaikan versi PHP)
- **Startup directory**: `C:\laragon\www\service_history`
- **Arguments**: `artisan queue:work --sleep=3 --tries=3 --max-time=3600`

**Tab Details:**
- **Display name**: Laravel Queue Worker
- **Description**: Laravel Queue Worker for Service History Application

**Tab I/O (Logging):**
- **Output (stdout)**: `C:\laragon\www\service_history\storage\logs\queue-worker.log`
- **Error (stderr)**: `C:\laragon\www\service_history\storage\logs\queue-worker-error.log`

**Tab Rotation (Optional):**
- **Rotate files**: Yes
- **Restrict rotation to files bigger than**: 1048576 bytes (1MB)

**Step 4: Manage Service**
```bash
# Start service
nssm start LaravelQueue

# Stop service
nssm stop LaravelQueue

# Restart service (after code deploy)
nssm restart LaravelQueue

# Check status
nssm status LaravelQueue

# View service details
nssm status LaravelQueue

# Remove service (if needed)
nssm remove LaravelQueue confirm
```

**Step 5: Verify Service**
```bash
# Check Windows Services
services.msc

# Look for "Laravel Queue Worker"
# Status should be "Running"
# Startup Type should be "Automatic"
```

#### Method 2: Task Scheduler

**Step 1: Create Batch File**

Create file: `C:\laragon\www\service_history\start-queue.bat`
```batch
@echo off
cd /d C:\laragon\www\service_history
C:\laragon\bin\php\php-8.2.x\php.exe artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

**Step 2: Create Scheduled Task**
```bash
# Open Task Scheduler
Win + R → taskschd.msc → Enter

# Create Task (not Basic Task)
# General Tab:
Name: Laravel Queue Worker
Description: Run Laravel queue worker for Service History
Security options:
  ☑ Run whether user is logged on or not
  ☑ Run with highest privileges
Configure for: Windows 10/11

# Triggers Tab:
New Trigger:
  Begin the task: At startup
  Delay task for: 30 seconds
  ☑ Enabled

# Actions Tab:
New Action:
  Action: Start a program
  Program/script: C:\laragon\www\service_history\start-queue.bat
  Start in: C:\laragon\www\service_history

# Conditions Tab:
☐ Start the task only if the computer is on AC power
☑ Wake the computer to run this task

# Settings Tab:
☑ Allow task to be run on demand
☑ Run task as soon as possible after a scheduled start is missed
☑ If the task fails, restart every: 1 minute
☑ Attempt to restart up to: 3 times
☑ Stop the task if it runs longer than: 0 (disabled)
If the running task does not end when requested, force it to stop: Yes
```

**Step 3: Test Task**
```bash
# Right-click task → Run
# Check if queue worker is running in Task Manager
```

#### Method 3: Startup Folder (Simple)

**Step 1: Create Batch File**

Create file: `C:\laragon\www\service_history\queue-worker.bat`
```batch
@echo off
title Laravel Queue Worker
cd /d C:\laragon\www\service_history

:loop
C:\laragon\bin\php\php-8.2.x\php.exe artisan queue:work --sleep=3 --tries=3 --max-time=3600
echo Queue worker stopped. Restarting in 5 seconds...
timeout /t 5
goto loop
```

**Step 2: Create Shortcut**
```bash
# Right-click queue-worker.bat → Create shortcut
# Copy shortcut to Startup folder:
Win + R → shell:startup → Enter
# Paste shortcut here
```

**Step 3: Auto-start on Boot**
- Queue worker akan otomatis start saat Windows boot
- Window akan muncul (bisa diminimize)

### Troubleshooting Windows

#### Queue Worker Not Starting

**Check PHP Path:**
```bash
# Test PHP CLI
php -v

# If not found, add to PATH:
# System Properties → Environment Variables → Path
# Add: C:\laragon\bin\php\php-8.2.x
```

**Check Permissions:**
```bash
# Run Command Prompt as Administrator
# Try starting queue worker manually
cd C:\laragon\www\service_history
php artisan queue:work
```

**Check Logs:**
```bash
# View Laravel logs
type storage\logs\laravel.log

# View queue worker logs (if using NSSM)
type storage\logs\queue-worker.log
type storage\logs\queue-worker-error.log
```

#### Service Stops Unexpectedly

**Using NSSM:**
```bash
# Check service status
nssm status LaravelQueue

# View service logs
type storage\logs\queue-worker-error.log

# Restart service
nssm restart LaravelQueue
```

**Using Task Scheduler:**
```bash
# Open Task Scheduler
# Check task history
# Right-click task → Properties → History tab
```

#### After Code Deploy

**Always restart queue worker after deploy:**

**NSSM:**
```bash
nssm restart LaravelQueue
```

**Task Scheduler:**
```bash
# Stop task
schtasks /end /tn "Laravel Queue Worker"

# Start task
schtasks /run /tn "Laravel Queue Worker"
```

**Manual:**
```bash
# Find PHP process
tasklist | findstr php

# Kill process
taskkill /F /PID [process_id]

# Start again
php artisan queue:work
```

### Windows Service Management Commands

```bash
# Using NSSM
nssm start LaravelQueue      # Start service
nssm stop LaravelQueue       # Stop service
nssm restart LaravelQueue    # Restart service
nssm status LaravelQueue     # Check status
nssm edit LaravelQueue       # Edit configuration
nssm remove LaravelQueue     # Remove service

# Using sc (Windows built-in)
sc start LaravelQueue        # Start service
sc stop LaravelQueue         # Stop service
sc query LaravelQueue        # Check status
sc delete LaravelQueue       # Delete service

# Using net
net start LaravelQueue       # Start service
net stop LaravelQueue        # Stop service

# Using PowerShell
Start-Service LaravelQueue   # Start service
Stop-Service LaravelQueue    # Stop service
Restart-Service LaravelQueue # Restart service
Get-Service LaravelQueue     # Check status
```

### Monitoring Queue on Windows

**Check if Queue Worker is Running:**
```bash
# Using Task Manager
Ctrl + Shift + Esc → Details tab → Look for php.exe

# Using Command Prompt
tasklist | findstr php

# Using PowerShell
Get-Process php
```

**View Queue Logs:**
```bash
# Real-time log viewing (PowerShell)
Get-Content storage\logs\laravel.log -Wait -Tail 50

# Or use a log viewer tool like:
# - Notepad++
# - Visual Studio Code
# - BareTail
```

### Recommended Setup for Windows Production

1. **Use NSSM** untuk service management (paling reliable)
2. **Set QUEUE_CONNECTION=database** atau **redis** di .env
3. **Configure logging** di NSSM untuk troubleshooting
4. **Set auto-restart** di NSSM settings
5. **Monitor logs** regularly
6. **Restart service** setelah setiap deploy

### Development vs Production

**Development (Laragon/XAMPP):**
```env
QUEUE_CONNECTION=sync
```
- Tidak perlu queue worker
- Job langsung dijalankan
- Mudah untuk debugging

**Production (Windows Server):**
```env
QUEUE_CONNECTION=database
# atau
QUEUE_CONNECTION=redis
```
- Gunakan NSSM untuk service
- Set auto-restart
- Monitor logs
- Restart after deploy

### Jobs Not Processing

1. **Check queue worker is running**:
```bash
ps aux | grep "queue:work"
```

2. **Check failed jobs**:
```bash
php artisan queue:failed
```

3. **Check logs**:
```bash
tail -f storage/logs/laravel.log
```

### Jobs Failing

1. **View failed job details**:
```bash
php artisan queue:failed
```

2. **Retry failed job**:
```bash
php artisan queue:retry {id}
```

3. **Check error in logs**:
```bash
tail -f storage/logs/laravel.log
```

### Queue Worker Stops

1. **Use supervisor** (Linux) or **NSSM** (Windows) to auto-restart
2. **Monitor with Horizon** for better visibility
3. **Set max-time and max-jobs** to prevent memory leaks:
```bash
php artisan queue:work --max-time=3600 --max-jobs=1000
```

## Best Practices

1. **Always restart queue worker after deploy**:
```bash
php artisan queue:restart
```

2. **Use supervisor/NSSM for production** to ensure queue worker always running

3. **Monitor failed jobs regularly**:
```bash
php artisan queue:failed
```

4. **Set appropriate timeouts**:
```php
// In Job class
public $timeout = 60; // 60 seconds
public $tries = 3; // Retry 3 times
```

5. **Use Redis for better performance** in production

6. **Log errors properly** for debugging:
```php
try {
    // Job logic
} catch (\Exception $e) {
    Log::error('Job failed', ['error' => $e->getMessage()]);
}
```

## Performance Tips

1. **Use multiple workers** for high traffic:
```bash
# Run 3 workers
php artisan queue:work --queue=default --sleep=3 &
php artisan queue:work --queue=default --sleep=3 &
php artisan queue:work --queue=default --sleep=3 &
```

2. **Prioritize queues**:
```bash
php artisan queue:work --queue=high,default,low
```

3. **Use Redis** instead of database for better performance

4. **Monitor memory usage** and set max-time to prevent leaks

5. **Use Horizon** for better monitoring and auto-scaling
