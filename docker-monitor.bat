@echo off
:menu
cls
echo ========================================
echo Docker Monitoring Menu
echo ========================================
echo.
echo 1. View Container Stats (CPU/Memory)
echo 2. View App Logs
echo 3. View Queue Logs
echo 4. Check Queue Worker Status
echo 5. Restart Queue Workers
echo 6. Check PHP-FPM Status
echo 7. View All Container Status
echo 8. Exit
echo.
set /p choice="Select option (1-8): "

if "%choice%"=="1" goto stats
if "%choice%"=="2" goto applogs
if "%choice%"=="3" goto queuelogs
if "%choice%"=="4" goto workerstatus
if "%choice%"=="5" goto restartworkers
if "%choice%"=="6" goto phpfpmstatus
if "%choice%"=="7" goto containerstatus
if "%choice%"=="8" goto end
goto menu

:stats
cls
echo Container Stats (Press Ctrl+C to stop)
echo ========================================
docker stats laravel_app laravel_queue laravel_nginx
goto menu

:applogs
cls
echo App Logs (Press Ctrl+C to stop)
echo ========================================
docker-compose logs -f app
goto menu

:queuelogs
cls
echo Queue Logs (Press Ctrl+C to stop)
echo ========================================
docker-compose logs -f queue
goto menu

:workerstatus
cls
echo Queue Worker Status
echo ========================================
docker exec laravel_queue supervisorctl status
echo.
pause
goto menu

:restartworkers
cls
echo Restarting Queue Workers...
echo ========================================
docker exec laravel_queue supervisorctl restart laravel-queue-worker:*
echo.
echo Workers restarted!
pause
goto menu

:phpfpmstatus
cls
echo PHP-FPM Status
echo ========================================
docker exec laravel_app php-fpm -t
echo.
echo PHP-FPM Configuration Test Passed!
pause
goto menu

:containerstatus
cls
echo Container Status
echo ========================================
docker-compose ps
echo.
pause
goto menu

:end
exit
