@echo off
echo ========================================
echo   Moving Backend to XAMPP htdocs
echo ========================================
echo.

REM Check if XAMPP htdocs exists
set XAMPP_PATH=C:\xampp\htdocs
set BACKEND_SOURCE=%~dp0
set BACKEND_TARGET=%XAMPP_PATH%\backend

echo Checking XAMPP installation...
if not exist "%XAMPP_PATH%" (
    echo.
    echo ERROR: XAMPP not found at %XAMPP_PATH%
    echo.
    echo Please check:
    echo 1. XAMPP is installed
    echo 2. Installation path is C:\xampp
    echo.
    echo If XAMPP is in a different location, edit this script.
    echo.
    pause
    exit /b 1
)

echo XAMPP found at: %XAMPP_PATH%
echo.

REM Check if backend already exists in htdocs
if exist "%BACKEND_TARGET%" (
    echo WARNING: Backend folder already exists in XAMPP htdocs!
    echo.
    set /p OVERWRITE="Do you want to overwrite it? (Y/N): "
    if /i not "%OVERWRITE%"=="Y" (
        echo Operation cancelled.
        pause
        exit /b 0
    )
    echo.
    echo Removing old backend folder...
    rmdir /s /q "%BACKEND_TARGET%"
)

echo.
echo Copying backend files to XAMPP...
echo From: %BACKEND_SOURCE%
echo To: %BACKEND_TARGET%
echo.

REM Create backend directory in htdocs
mkdir "%BACKEND_TARGET%" 2>nul

REM Copy all files
xcopy "%BACKEND_SOURCE%*" "%BACKEND_TARGET%\" /E /I /Y /Q

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo   SUCCESS! Backend copied to XAMPP
    echo ========================================
    echo.
    echo Backend location: %BACKEND_TARGET%
    echo.
    echo Next steps:
    echo 1. Make sure Apache is running in XAMPP Control Panel
    echo 2. Open: http://localhost/backend/admin/index.php
    echo 3. Or setup database: http://localhost/backend/database/setup_database.php
    echo.
) else (
    echo.
    echo ERROR: Failed to copy files!
    echo.
    echo Please check:
    echo 1. You have write permissions to C:\xampp\htdocs
    echo 2. Close any programs using the backend folder
    echo 3. Try running as Administrator
    echo.
)

pause

