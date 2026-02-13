@echo off
REM ============================================
REM Laravel Queue Worker Starter
REM ============================================

title Laravel Queue Worker - Service History

REM Change to project directory
cd /d %~dp0

REM Display info
echo ============================================
echo Laravel Queue Worker
echo ============================================
echo Project: Service History
echo Directory: %CD%
echo Time: %DATE% %TIME%
echo ============================================
echo.
echo Starting queue worker...
echo Press Ctrl+C to stop
echo.

REM Start queue worker with auto-restart
:loop
php artisan queue:work --sleep=3 --tries=3 --max-time=3600

REM If worker stops, wait 5 seconds and restart
echo.
echo ============================================
echo Queue worker stopped!
echo Restarting in 5 seconds...
echo Press Ctrl+C to cancel
echo ============================================
timeout /t 5
goto loop
