@echo off
title Meesho Admin Panel Server
color 0A
echo.
echo ========================================
echo   Meesho E-commerce Admin Panel Server
echo ========================================
echo.
echo Starting PHP development server...
echo.
echo Server will run on: http://localhost:8000
echo.
echo Admin Panel: http://localhost:8000/admin/index.php
echo API Endpoint: http://localhost:8000/api/products.php
echo.
echo ========================================
echo.
echo Press Ctrl+C to stop the server
echo.
echo ========================================
echo.

cd /d "%~dp0"
php -S localhost:8000

pause


