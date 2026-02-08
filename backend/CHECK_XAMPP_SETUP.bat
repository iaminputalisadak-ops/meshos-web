@echo off
echo ========================================
echo   XAMPP Setup Checker
echo ========================================
echo.

set XAMPP_PATH=C:\xampp
set HTDOCS_PATH=%XAMPP_PATH%\htdocs
set BACKEND_PATH=%HTDOCS_PATH%\backend

echo [1] Checking XAMPP installation...
if exist "%XAMPP_PATH%" (
    echo     ✓ XAMPP found at: %XAMPP_PATH%
) else (
    echo     ✗ XAMPP NOT FOUND at: %XAMPP_PATH%
    echo     Please install XAMPP or update the path in this script
    goto :end
)

echo.
echo [2] Checking htdocs folder...
if exist "%HTDOCS_PATH%" (
    echo     ✓ htdocs folder exists
) else (
    echo     ✗ htdocs folder NOT FOUND
    goto :end
)

echo.
echo [3] Checking backend folder in htdocs...
if exist "%BACKEND_PATH%" (
    echo     ✓ Backend folder exists in htdocs
    echo     Location: %BACKEND_PATH%
) else (
    echo     ✗ Backend folder NOT FOUND in htdocs
    echo     Location should be: %BACKEND_PATH%
    echo.
    echo     SOLUTION: Run MOVE_TO_XAMPP.bat to copy backend to htdocs
    goto :end
)

echo.
echo [4] Checking Apache service...
sc query Apache2.4 >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo     ✓ Apache service found
) else (
    echo     ⚠ Apache service not found (may be running as application)
)

echo.
echo [5] Testing Apache connection...
powershell -Command "try { $response = Invoke-WebRequest -Uri 'http://localhost' -TimeoutSec 2 -UseBasicParsing; echo     ✓ Apache is RUNNING (Status: $($response.StatusCode)) } catch { echo     ✗ Apache is NOT RUNNING or not accessible }" 2>nul

echo.
echo [6] Testing backend API...
powershell -Command "try { $response = Invoke-WebRequest -Uri 'http://localhost/backend/api/products.php' -TimeoutSec 2 -UseBasicParsing; echo     ✓ Backend API is ACCESSIBLE (Status: $($response.StatusCode)) } catch { echo     ✗ Backend API is NOT ACCESSIBLE }" 2>nul

echo.
echo ========================================
echo   Summary
echo ========================================
echo.
echo If all checks passed:
echo   → Open: http://localhost/backend/admin/index.php
echo   → Setup DB: http://localhost/backend/database/setup_database.php
echo.
echo If backend folder is missing:
echo   → Run: MOVE_TO_XAMPP.bat
echo.
echo If Apache is not running:
echo   → Open XAMPP Control Panel
echo   → Click "Start" next to Apache
echo.

:end
pause

