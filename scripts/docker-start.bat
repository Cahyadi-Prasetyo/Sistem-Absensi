@echo off
echo ========================================
echo Starting Sistem Absensi Docker Services
echo ========================================
echo.

REM Load environment variables
if not exist .env.docker-compose (
    echo ERROR: .env.docker-compose file not found!
    echo Please create it from .env.docker-compose.example
    exit /b 1
)

REM Check if APP_KEY is set in docker/.env.docker
findstr /C:"APP_KEY=base64:" docker\.env.docker >nul
if errorlevel 1 (
    echo Generating application key...
    docker-compose run --rm app-node-1 php artisan key:generate --show
    echo.
    echo Please copy the generated key to docker/.env.docker
    echo Then run this script again.
    pause
    exit /b 1
)

echo Building and starting Docker containers...
docker-compose --env-file .env.docker-compose up -d

echo.
echo ========================================
echo Services Status:
echo ========================================
docker-compose ps

echo.
echo ========================================
echo Service URLs:
echo ========================================
echo Application: http://localhost
echo WebSocket (Reverb): ws://localhost:8080
echo MySQL: localhost:3306
echo Redis: localhost:6379
echo.
echo ========================================
echo Useful Commands:
echo ========================================
echo View logs: docker-compose logs -f
echo View specific service: docker-compose logs -f reverb
echo Stop services: docker-compose down
echo Restart services: docker-compose restart
echo.
echo To view real-time logs, run: docker-compose logs -f
echo.
pause
