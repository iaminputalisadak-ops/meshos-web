@echo off
title Meesho Admin Server - AUTO START
color 0A
cls
echo.
echo ================================================
echo   AUTO-DETECTING PHP AND STARTING SERVER
echo ================================================
echo.

REM Check for XAMPP PHP
if exist "C:\xampp\php\php.exe" (
    echo [OK] XAMPP PHP found!
    echo Starting server with XAMPP PHP...
    echo.
    echo Admin Panel: http://localhost:8000/admin/index.php
    echo Login: admin / admin123
    echo.
    echo ================================================
    echo Keep this window open!
    echo Press Ctrl+C to stop
    echo ================================================
    echo.
    cd /d "%~dp0"
    "C:\xampp\php\php.exe" -S localhost:8000
    goto :end
)

REM Check for WAMP PHP
for /d %%i in ("C:\wamp64\bin\php\php*") do (
    if exist "%%i\php.exe" (
        echo [OK] WAMP PHP found!
        echo Starting server with WAMP PHP...
        echo.
        echo Admin Panel: http://localhost:8000/admin/index.php
        echo Login: admin / admin123
        echo.
        echo ================================================
        echo Keep this window open!
        echo Press Ctrl+C to stop
        echo ================================================
        echo.
        cd /d "%~dp0"
        "%%i\php.exe" -S localhost:8000
        goto :end
    )
)

REM Check for Laragon PHP
if exist "C:\laragon\bin\php\php*\php.exe" (
    for /f "delims=" %%i in ('dir /b /ad "C:\laragon\bin\php"') do (
        if exist "C:\laragon\bin\php\%%i\php.exe" (
            echo [OK] Laragon PHP found!
            echo Starting server with Laragon PHP...
            echo.
            echo Admin Panel: http://localhost:8000/admin/index.php
            echo Login: admin / admin123
            echo.
            echo ================================================
            echo Keep this window open!
            echo Press Ctrl+C to stop
            echo ================================================
            echo.
            cd /d "%~dp0"
            "C:\laragon\bin\php\%%i\php.exe" -S localhost:8000
            goto :end
        )
    )
)

REM Check for system PHP
php -v >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] System PHP found!
    echo Starting server...
    echo.
    echo Admin Panel: http://localhost:8000/admin/index.php
    echo Login: admin / admin123
    echo.
    echo ================================================
    echo Keep this window open!
    echo Press Ctrl+C to stop
    echo ================================================
    echo.
    cd /d "%~dp0"
    php -S localhost:8000
    goto :end
)

REM No PHP found
echo [ERROR] PHP not found!
echo.
echo ================================================
echo   INSTALL XAMPP TO FIX THIS
echo ================================================
echo.
echo 1. Download XAMPP from:
echo    https://www.apachefriends.org/
echo.
echo 2. Install XAMPP (includes PHP)
echo.
echo 3. Run this script again
echo.
echo ================================================
echo.
pause
:end


