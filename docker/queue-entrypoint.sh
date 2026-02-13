#!/bin/sh

echo "========================================="
echo "Laravel Queue Worker Starting"
echo "========================================="
echo ""

# Fix storage permissions
echo "Fixing storage permissions..."
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
mkdir -p /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/storage/logs /var/www/html/storage/app/public 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
echo "✓ Storage permissions fixed!"
echo ""

# Wait a bit for app container to finish migrations
echo "Waiting for app container to complete setup..."
sleep 15

echo "Starting queue worker..."
exec php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --verbose
