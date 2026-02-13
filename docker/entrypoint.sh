#!/bin/sh

echo "========================================="
echo "Laravel Docker Entrypoint"
echo "========================================="
echo ""

# Check if running as root
if [ "$(id -u)" = "0" ]; then
    echo "Running as root - fixing permissions..."
    
    # Fix git ownership issue
    git config --global --add safe.directory /var/www/html 2>/dev/null || true
    
    # Ensure storage directories exist
    mkdir -p /var/www/html/storage/framework/cache \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/framework/testing \
             /var/www/html/storage/logs \
             /var/www/html/storage/app/public \
             /var/www/html/bootstrap/cache 2>/dev/null || true
    
    # Create log files if they don't exist
    touch /var/www/html/storage/logs/laravel.log 2>/dev/null || true
    touch /var/www/html/storage/logs/supervisord.log 2>/dev/null || true
    touch /var/www/html/storage/logs/worker.log 2>/dev/null || true
    
    # Fix ownership and permissions
    chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true
    chown -R www-data:www-data /var/www/html/bootstrap/cache 2>/dev/null || true
    chmod -R 775 /var/www/html/storage 2>/dev/null || true
    chmod -R 775 /var/www/html/bootstrap/cache 2>/dev/null || true
    
    echo "✓ Permissions fixed!"
    echo ""
else
    echo "Running as $(whoami) - limited permission fixes..."
    
    # Fix git ownership issue
    git config --global --add safe.directory /var/www/html 2>/dev/null || true
    
    # Try to fix permissions (will work if user has access)
    chmod -R 775 /var/www/html/storage 2>/dev/null || true
    chmod -R 775 /var/www/html/bootstrap/cache 2>/dev/null || true
    
    echo "✓ Permission check complete!"
    echo ""
fi

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
    echo "✓ Composer dependencies installed!"
    echo ""
fi

# Install node dependencies and build assets if needed
if [ ! -d "node_modules" ]; then
    echo "Installing NPM dependencies..."
    npm install
    echo "✓ NPM dependencies installed!"
    echo ""
fi

if [ ! -d "public/build" ]; then
    echo "Building frontend assets..."
    npm run build
    echo "✓ Assets built successfully!"
    echo ""
fi

# Check if we should skip database operations
if [ "$SKIP_DB_SETUP" = "true" ]; then
    echo "SKIP_DB_SETUP=true, skipping database operations..."
    echo "You can run migrations manually with:"
    echo "  docker-compose exec app php artisan migrate --force"
    echo ""
    exec "$@"
fi

# Wait for database to be ready
echo "Waiting for database connection..."
echo "Database: $DB_HOST:$DB_PORT/$DB_DATABASE"
max_attempts=30
attempt=0

while [ $attempt -lt $max_attempts ]; do
    # Try to connect using PHP PDO directly (more reliable than artisan)
    if php -r "
        try {
            \$pdo = new PDO(
                'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD'),
                [PDO::ATTR_TIMEOUT => 3, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
            );
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null; then
        echo "✓ Database connection successful!"
        break
    fi
    
    attempt=$((attempt + 1))
    if [ $attempt -lt $max_attempts ]; then
        echo "Attempt $attempt/$max_attempts - Waiting for database..."
        sleep 2
    fi
done

if [ $attempt -eq $max_attempts ]; then
    echo ""
    echo "⚠ Warning: Could not connect to database after $max_attempts attempts"
    echo ""
    echo "Please check:"
    echo "  - DB_HOST=$DB_HOST"
    echo "  - DB_PORT=$DB_PORT"
    echo "  - DB_DATABASE=$DB_DATABASE"
    echo "  - DB_USERNAME=$DB_USERNAME"
    echo "  - MySQL server is running and accessible"
    echo ""
    echo "You can:"
    echo "  1. Run migrations manually: docker exec -it laravel_app php artisan migrate --force"
    echo "  2. Set SKIP_DB_SETUP=true to skip this check"
    echo ""
    echo "Continuing without database setup..."
else
    echo ""
    echo "Running database migrations..."
    if php artisan migrate --force 2>&1; then
        echo "✓ Migrations completed successfully!"
    else
        echo "⚠ Warning: Migrations failed!"
        echo "You can run manually: docker exec -it laravel_app php artisan migrate --force"
    fi
    
    # Run seeders if SEED_DATABASE is set to true
    if [ "$SEED_DATABASE" = "true" ]; then
        echo ""
        echo "Running database seeders..."
        if php artisan db:seed --force; then
            echo "✓ Seeders completed successfully!"
        else
            echo "⚠ Warning: Seeders failed or no seeders available!"
        fi
    fi
    
    echo ""
    echo "Creating storage link..."
    php artisan storage:link 2>/dev/null || echo "Storage link already exists"
    
    echo ""
    echo "Optimizing application..."
    php artisan config:cache 2>&1 || echo "⚠ Config cache failed"
    php artisan route:cache 2>&1 || echo "⚠ Route cache failed"
    php artisan view:cache 2>&1 || echo "✓ View cache successful"
fi

echo ""
echo "========================================="
echo "Laravel is ready!"
echo "========================================="
echo ""

# Execute the main command (supervisord or queue worker)
exec "$@"
