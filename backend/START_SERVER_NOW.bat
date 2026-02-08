@echo off
title Meesho Admin Server - DO NOT CLOSE THIS WINDOW
color 0A
cls
echo.
echo ================================================
echo   MESSHO ADMIN PANEL SERVER
echo ================================================
echo.
echo   Server is starting...
echo.
echo   IMPORTANT: Keep this window open!
echo.
echo ================================================
echo.
echo   Admin Panel: http://localhost:8000/admin/index.php
echo   API Test:    http://localhost:8000/api/products.php
echo.
echo   Login: admin / admin123
echo.
echo ================================================
echo.
echo   Press Ctrl+C to stop the server
echo.
echo ================================================
echo.

cd /d "%~dp0"
php -S localhost:8000 -t .

echo.
echo Server stopped.
pause


