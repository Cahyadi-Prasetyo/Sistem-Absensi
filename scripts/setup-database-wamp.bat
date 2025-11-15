@echo off
REM Setup Database untuk WAMP Server

echo ============================================
echo   Setup Database MySQL di WAMP Server
echo ============================================
echo.

REM Cek apakah MySQL running
echo [1/5] Checking MySQL service...
netstat -an | find "3306" >nul
if errorlevel 1 (
    echo ERROR: MySQL tidak running!
    echo Pastikan WAMP Server sudah running (icon hijau)
    echo.
    pause
    exit /b 1
)
echo MySQL is running on port 3306
echo.

REM Set MySQL path (sesuaikan dengan versi WAMP Anda)
set MYSQL_PATH=C:\wamp64\bin\mysql\mysql8.0.31\bin
set MYSQL_BIN=%MYSQL_PATH%\mysql.exe

REM Cek apakah mysql.exe ada
if not exist "%MYSQL_BIN%" (
    echo ERROR: mysql.exe tidak ditemukan di %MYSQL_PATH%
    echo.
    echo Silakan sesuaikan path MySQL di script ini.
    echo Cek folder: C:\wamp64\bin\mysql\
    echo.
    pause
    exit /b 1
)

echo [2/5] Creating database...
echo.
echo Masukkan password MySQL root (default: kosong, tekan Enter):
"%MYSQL_BIN%" -u root -p < database\setup-wamp.sql

if errorlevel 1 (
    echo.
    echo ERROR: Gagal membuat database!
    echo Pastikan password MySQL benar.
    echo.
    pause
    exit /b 1
)

echo.
echo Database "absensi" berhasil dibuat!
echo.

echo [3/5] Running migrations...
php artisan migrate:fresh
if errorlevel 1 (
    echo.
    echo ERROR: Migration gagal!
    echo.
    pause
    exit /b 1
)
echo.

echo [4/5] Seeding database...
php artisan db:seed --class=UserSeeder
if errorlevel 1 (
    echo.
    echo ERROR: Seeding gagal!
    echo.
    pause
    exit /b 1
)
echo.

echo [5/5] Clearing cache...
php artisan optimize:clear
echo.

echo [6/6] Verifying database...
php scripts\check-database.php

echo ============================================
echo   Setup Database Selesai!
echo ============================================
echo.
echo Database: absensi
echo Host: 127.0.0.1:3306
echo Username: root
echo Password: (kosong)
echo.
echo Default Users:
echo - admin@test.com / password (Admin)
echo - teacher@test.com / password (Teacher)
echo - student1@test.com / password (Student)
echo.
echo Akses phpMyAdmin: http://localhost/phpmyadmin
echo.
pause
