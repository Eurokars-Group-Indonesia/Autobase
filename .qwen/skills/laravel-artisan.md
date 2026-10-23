# Laravel Artisan Commands

Provides common Laravel Artisan command shortcuts for this project.

## Available Commands

### Run Artisan Commands
```bash
php artisan <command>
```

### Common Operations
- `php artisan serve` - Start local development server
- `php artisan migrate` - Run database migrations
- `php artisan migrate:status` - Check migration status
- `php artisan db:seed` - Seed the database
- `php artisan make:controller <name>` - Create a new controller
- `php artisan make:model <name>` - Create a new model
- `php artisan make:middleware <name>` - Create middleware
- `php artisan make:request <name>` - Create form request
- `php artisan route:list` - List all routes
- `php artisan config:clear` - Clear config cache
- `php artisan cache:clear` - Clear application cache
- `php artisan view:clear` - Clear compiled views
- `php artisan optimize:clear` - Clear all caches

### Docker-aware Commands
If using Docker:
```bash
docker-compose exec app php artisan <command>
```

## Project Context
- Laravel project with Docker support
- Database pooling configured (see DATABASE_POOLING*.md files)
- Azure token refresh implementation present
- CSP customization configured
