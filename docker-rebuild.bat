@echo off
echo ========================================
echo Docker Rebuild Script - Optimized Setup
echo ========================================
echo.

echo [1/4] Stopping containers...
docker-compose down
echo.

echo [2/4] Rebuilding images (this may take a few minutes)...
docker-compose build --no-cache
echo.

echo [3/4] Starting containers...
docker-compose up -d
echo.

echo [4/4] Waiting for services to be ready...
timeout /t 10 /nobreak > nul
echo.

echo ========================================
echo Checking container status...
echo ========================================
docker-compose ps
echo.

echo ========================================
echo Checking queue workers...
echo ========================================
docker exec laravel_queue supervisorctl status
echo.

echo ========================================
echo Docker rebuild completed!
echo ========================================
echo.
echo Useful commands:
echo - View app logs:   docker-compose logs -f app
echo - View queue logs: docker-compose logs -f queue
echo - Check stats:     docker stats laravel_app laravel_queue
echo - Restart workers: docker exec laravel_queue supervisorctl restart laravel-queue-worker:*
echo.
pause
