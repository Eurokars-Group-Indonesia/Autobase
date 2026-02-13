@echo off
REM ============================================
REM Laravel Queue Worker Stopper
REM ============================================

title Stop Laravel Queue Worker

echo ============================================
echo Stopping Laravel Queue Worker
echo ============================================
echo.

REM Find and kill all PHP processes running queue:work
for /f "tokens=2" %%i in ('tasklist ^| findstr /i "php.exe"') do (
    echo Checking process ID: %%i
    wmic process where "ProcessId=%%i" get CommandLine | findstr /i "queue:work" >nul
    if not errorlevel 1 (
        echo Stopping queue worker (PID: %%i)
        taskkill /F /PID %%i
    )
)

echo.
echo ============================================
echo Done!
echo ============================================
pause
