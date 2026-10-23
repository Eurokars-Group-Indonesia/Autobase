# Database Migration Skill

Handles database migrations, seeding, and schema operations for this Laravel project.

## Migration Commands

### Create New Migration
```bash
php artisan make:migration create_<table_name>_table
php artisan make:migration add_<column>_to_<table>_table
```

### Run Migrations
```bash
# Run all pending migrations
php artisan migrate

# Run with seeding
php artisan migrate --seed

# Run specific batch
php artisan migrate --step=<number>
```

### Rollback Migrations
```bash
# Rollback last batch
php artisan migrate:rollback

# Rollback specific number of batches
php artisan migrate:rollback --step=<number>

# Rollback all migrations
php artisan migrate:reset
```

### Migration Status
```bash
# Check migration status
php artisan migrate:status

# Show migration table structure
php artisan migrate:table
```

## Database Seeding

### Run Seeders
```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=<SeederClass>

# Migrate and seed
php artisan migrate:fresh --seed
```

## Best Practices

1. **Always test migrations** on a development database first
2. **Use transaction-safe migrations** when possible
3. **Add proper indexes** for frequently queried columns
4. **Consider database pooling** settings (see DATABASE_POOLING.md)
5. **Backup before production migrations**

## Docker Usage
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```
