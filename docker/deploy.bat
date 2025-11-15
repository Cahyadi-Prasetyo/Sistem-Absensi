@echo off
setlocal enabledelayedexpansion

echo ==========================================
echo   Sistem Absensi - Docker Deployment
echo ==========================================
echo.

REM Check if Docker is installed
docker --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Docker is not installed
    echo Please install Docker Desktop first: https://docs.docker.com/desktop/install/windows-install/
    pause
    exit /b 1
)

REM Check if Docker Compose is installed
docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Docker Compose is not installed
    echo Please install Docker Compose first
    pause
    exit /b 1
)

echo [OK] Docker and Docker Compose are installed
echo.

REM Check if docker/.env.docker exists
if not exist docker\.env.docker (
    echo [WARNING] docker\.env.docker not found, copying from example
    copy docker\.env.docker.example docker\.env.docker
    echo ERROR: Please configure docker\.env.docker with your credentials
    echo Required settings:
    echo   - APP_KEY (run: php artisan key:generate)
    echo   - DB_PASSWORD
    echo   - MYSQL_ROOT_PASSWORD
    echo   - REVERB_APP_KEY
    echo   - REVERB_APP_SECRET
    pause
    exit /b 1
) else (
    echo [OK] docker\.env.docker exists
)

REM Check if APP_KEY is set
findstr /C:"APP_KEY=base64:" docker\.env.docker >nul 2>&1
if errorlevel 1 (
    echo ERROR: APP_KEY is not set in docker\.env.docker
    echo Run: php artisan key:generate
    echo Then copy the generated key to docker\.env.docker
    pause
    exit /b 1
)

echo.
echo Building Docker images...
echo This may take several minutes on first run...
echo.

REM Build and start containers
docker-compose up -d --build

echo.
echo Waiting for services to be ready...
timeout /t 10 /nobreak >nul

REM Check if MySQL is ready
echo Checking MySQL connection...
set max_attempts=30
set attempt=0

:mysql_check
set /a attempt+=1
docker-compose exec -T mysql mysqladmin ping -h localhost -u root --silent >nul 2>&1
if errorlevel 1 (
    if !attempt! geq %max_attempts% (
        echo ERROR: MySQL failed to start
        echo Check logs with: docker-compose logs mysql
        pause
        exit /b 1
    )
    echo Waiting for MySQL... (!attempt!/%max_attempts%)
    timeout /t 2 /nobreak >nul
    goto mysql_check
)

echo [OK] MySQL is ready

REM Run migrations and seeders
echo.
echo Running database migrations and seeders...
docker-compose exec -T app-node-1 php artisan migrate --force
docker-compose exec -T app-node-1 php artisan db:seed --class=ResetDatabaseSeeder --force

echo.
echo ==========================================
echo   Deployment Successful!
echo ==========================================
echo.
echo Application is running at:
echo   http://localhost
echo.
echo WebSocket Server:
echo   ws://localhost:8080
echo.
echo Default Login Credentials:
echo   Admin: admin@absensi.com / password
echo   Karyawan: andi.wijaya@absensi.com / password
echo.
echo Useful Commands:
echo   View logs: docker-compose logs -f
echo   Stop services: docker-compose down
echo   Restart services: docker-compose restart
echo   Check status: docker-compose ps
echo.
pause
