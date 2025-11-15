@echo off
echo ========================================
echo Docker Setup for Absensi System
echo ========================================
echo.

echo [1/4] Building Docker images...
docker-compose build

echo.
echo [2/4] Starting containers...
docker-compose up -d

echo.
echo [3/4] Waiting for services to be ready...
timeout /t 10 /nobreak >nul

echo.
echo [4/4] Running migrations and seeders...
docker-compose exec app-node-1 php artisan migrate:fresh --seed

echo.
echo ========================================
echo Setup Complete!
echo ========================================
echo Application: http://localhost
echo Reverb WebSocket: ws://localhost:8080
echo PostgreSQL: localhost:5432
echo Redis: localhost:6379
echo ========================================
echo.
echo To view logs: docker-compose logs -f
echo To stop: docker-compose down
echo.
pause
