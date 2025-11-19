@echo off
echo ========================================
echo Docker Services Logs
echo ========================================
echo.
echo Press Ctrl+C to stop viewing logs
echo.

if "%1"=="" (
    docker-compose logs -f
) else (
    docker-compose logs -f %1
)
