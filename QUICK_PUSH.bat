@echo off
echo ========================================
echo   Push to GitHub - Meesho Web
echo ========================================
echo.

cd /d "%~dp0"

echo Current repository: iaminputalisadak-ops/meshos-web
echo.
echo IMPORTANT: You need a Personal Access Token!
echo.
echo Steps:
echo 1. Go to: https://github.com/settings/tokens
echo 2. Generate new token (classic)
echo 3. Select scope: repo
echo 4. Copy the token
echo.
echo Press any key when you have your token ready...
pause

echo.
echo Pushing to GitHub...
echo When prompted:
echo   Username: iaminputalisadak-ops
echo   Password: Paste your TOKEN (not password!)
echo.

git push -u origin main

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo   SUCCESS! Code pushed to GitHub!
    echo ========================================
    echo.
    echo Repository: https://github.com/iaminputalisadak-ops/meshos-web
    echo.
) else (
    echo.
    echo ========================================
    echo   PUSH FAILED
    echo ========================================
    echo.
    echo Possible issues:
    echo 1. Wrong username/password
    echo 2. Need to use Personal Access Token
    echo 3. No write access to repository
    echo.
    echo See PUSH_TO_GITHUB.md for detailed instructions
    echo.
)

pause

