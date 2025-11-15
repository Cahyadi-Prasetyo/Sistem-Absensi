@echo off
REM Build script untuk WAMP Server (tanpa hash)

echo ========================================
echo   Building Assets untuk WAMP Server
echo ========================================
echo.

echo [1/3] Cleaning old build...
if exist "public\build" (
    rmdir /s /q "public\build"
    echo Old build cleaned.
) else (
    echo No old build found.
)
echo.

echo [2/3] Building assets with Vite...
call npm run build
echo.

echo [3/3] Renaming files (remove hash)...
if exist "public\build\assets\app2.js" (
    ren "public\build\assets\app2.js" "app.js"
    echo Renamed app2.js to app.js
)

echo.
echo ========================================
echo   Build Complete!
echo ========================================
echo.
echo Files generated:
echo - public/build/assets/app.css
echo - public/build/assets/app.js
echo.
echo Ready for WAMP Server!
echo.
pause
