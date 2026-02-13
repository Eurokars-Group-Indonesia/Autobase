@echo off
REM ============================================
REM Laravel Queue Worker Restarter
REM ============================================

title Restart Laravel Queue Worker

echo ============================================
echo Restarting Laravel Queue Worker
echo ============================================
echo.

REM Stop queue worker
echo Step 1: Stopping queue worker...
call stop-queue-worker.bat

echo.
echo Step 2: Waiting 3 seconds...
timeout /t 3 >nul

echo.
echo Step 3: Starting queue worker...
start "Laravel Queue Worker" cmd /k start-queue-worker.bat

echo.
echo ============================================
echo Queue worker restarted!
echo ============================================
pause
