@echo off
echo ========================================
echo   Testing Backend Connection
echo ========================================
echo.

echo [1] Testing Apache...
powershell -Command "try { $response = Invoke-WebRequest -Uri 'http://localhost' -TimeoutSec 3 -UseBasicParsing; Write-Host '    ✓ Apache is RUNNING (Status:' $response.StatusCode ')' } catch { Write-Host '    ✗ Apache is NOT RUNNING' }"

echo.
echo [2] Testing Backend API...
powershell -Command "try { $response = Invoke-WebRequest -Uri 'http://localhost/backend/api/products.php' -TimeoutSec 3 -UseBasicParsing; Write-Host '    ✓ Backend API is ACCESSIBLE (Status:' $response.StatusCode ')' } catch { Write-Host '    ✗ Backend API is NOT ACCESSIBLE' }"

echo.
echo [3] Testing Admin Panel...
powershell -Command "try { $response = Invoke-WebRequest -Uri 'http://localhost/backend/admin/index.php' -TimeoutSec 3 -UseBasicParsing; Write-Host '    ✓ Admin Panel is ACCESSIBLE (Status:' $response.StatusCode ')' } catch { Write-Host '    ✗ Admin Panel is NOT ACCESSIBLE' }"

echo.
echo ========================================
echo   Quick Access Links
echo ========================================
echo.
echo Admin Panel: http://localhost/backend/admin/index.php
echo Setup Database: http://localhost/backend/database/setup_database.php
echo API Test: http://localhost/backend/api/products.php
echo.
echo Login: admin / admin123
echo.
pause

