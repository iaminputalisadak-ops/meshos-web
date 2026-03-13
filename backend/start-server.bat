@echo off
cd /d "%~dp0"
echo.
echo ========================================
echo   PHP Backend for Meesho / React app
echo ========================================
echo.

set PHP_EXE=
if exist "C:\xampp\php\php.exe" set PHP_EXE=C:\xampp\php\php.exe
if exist "C:\laragon\bin\php\php-8*\php.exe" for /d %%i in (C:\laragon\bin\php\php-8*) do set PHP_EXE=%%i\php.exe
if "%PHP_EXE%"=="" set PHP_EXE=php

echo Starting server on http://localhost:8888
echo.
echo 1. Keep this window OPEN while using the app.
echo 2. In your browser, open: http://localhost:3000
echo 3. Test backend: http://localhost:8888/api/health.php
echo.
echo Press Ctrl+C to stop.
echo ========================================
echo.

"%PHP_EXE%" -S localhost:8888 -t . 2>&1
if errorlevel 1 (
  echo.
  echo Failed to start. If "port in use", close the app using port 8888 or edit this file to use another port.
  echo If "php not found", install XAMPP or add PHP to PATH.
  pause
)
