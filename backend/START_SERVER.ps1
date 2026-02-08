# PowerShell script to start PHP server
Write-Host "========================================" -ForegroundColor Green
Write-Host "  Starting Meesho Admin Panel Server" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Check if PHP is installed
$phpCheck = Get-Command php -ErrorAction SilentlyContinue

if (-not $phpCheck) {
    Write-Host "ERROR: PHP is not installed or not in PATH!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install one of the following:" -ForegroundColor Yellow
    Write-Host "1. XAMPP: https://www.apachefriends.org/" -ForegroundColor Cyan
    Write-Host "2. WAMP: https://www.wampserver.com/" -ForegroundColor Cyan
    Write-Host "3. Laragon: https://laragon.org/" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "After installing, add PHP to your PATH or use XAMPP's PHP:" -ForegroundColor Yellow
    Write-Host "  C:\xampp\php\php.exe -S localhost:8000" -ForegroundColor White
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit
}

Write-Host "PHP found: $($phpCheck.Source)" -ForegroundColor Green
Write-Host ""
Write-Host "Starting server on http://localhost:8000" -ForegroundColor Green
Write-Host ""
Write-Host "Admin Panel: http://localhost:8000/admin/index.php" -ForegroundColor Cyan
Write-Host "API Test: http://localhost:8000/api/products.php" -ForegroundColor Cyan
Write-Host ""
Write-Host "Login: admin / admin123" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Change to backend directory
Set-Location $PSScriptRoot

# Start PHP server
php -S localhost:8000


