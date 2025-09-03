#!/bin/bash
# Emergency Laravel Auth Clear Script
# วิธีใช้: ./emergency-clear.sh

echo "🚨 Laravel Emergency Clear Script"
echo "=================================="

# Clear all Laravel caches
echo "📝 Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run emergency logout
echo "🔒 Running emergency logout..."
php artisan auth:emergency-logout --force

echo "✅ Emergency clear completed!"
echo "🔄 Please restart your web server for best results."
