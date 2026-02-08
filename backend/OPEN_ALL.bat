@echo off
echo ========================================
echo   Opening All Backend Pages
echo ========================================
echo.

echo Opening Database Setup...
start http://localhost/backend/database/setup_database.php

timeout /t 2 /nobreak >nul

echo Opening Admin Panel...
start http://localhost/backend/admin/index.php

timeout /t 2 /nobreak >nul

echo Opening API Test...
start http://localhost/backend/api/products.php

echo.
echo ========================================
echo   All pages opened in browser!
echo ========================================
echo.
echo 1. First, run the Database Setup page
echo 2. Then login to Admin Panel: admin / admin123
echo 3. Check API for JSON response
echo.
pause

