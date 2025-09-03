@echo off
REM Emergency Laravel Auth Clear Script for Windows
REM วิธีใช้: emergency-clear.bat

echo 🚨 Laravel Emergency Clear Script
echo ==================================

REM Clear all Laravel caches
echo 📝 Clearing application caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

REM Run emergency logout
echo 🔒 Running emergency logout...
php artisan auth:emergency-logout --force

echo ✅ Emergency clear completed!
echo 🔄 Please restart your web server for best results.
pause
