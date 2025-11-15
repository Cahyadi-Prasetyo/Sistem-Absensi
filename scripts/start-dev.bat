@echo off
echo Starting Laravel Absensi Development Server...
echo.

echo [1/3] Starting Laravel Server on port 8000...
start "Laravel Server" cmd /k "php artisan serve"

timeout /t 2 /nobreak >nul

echo [2/3] Starting Laravel Reverb WebSocket Server on port 8080...
start "Reverb Server" cmd /k "php artisan reverb:start"

timeout /t 2 /nobreak >nul

echo [3/3] Starting Queue Worker...
start "Queue Worker" cmd /k "php artisan queue:work"

echo.
echo ========================================
echo All services started!
echo ========================================
echo Laravel App: http://localhost:8000
echo Reverb WebSocket: ws://localhost:8080
echo ========================================
echo.
echo Press any key to stop all services...
pause >nul

taskkill /FI "WindowTitle eq Laravel Server*" /T /F
taskkill /FI "WindowTitle eq Reverb Server*" /T /F
taskkill /FI "WindowTitle eq Queue Worker*" /T /F

echo All services stopped.
