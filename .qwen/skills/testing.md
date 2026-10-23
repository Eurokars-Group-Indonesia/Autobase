# Testing Skill

Handles PHPUnit/Pest testing operations for this Laravel project.

## Run Tests

### Basic Test Commands
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/<TestFile>.php

# Run specific test method
php artisan test --filter=<TestMethod>

# Run tests in a directory
php artisan test tests/Feature/
```

### PHPUnit Options
```bash
# Run with verbose output
./vendor/bin/phpunit -v

# Run with coverage (requires Xdebug)
./vendor/bin/phpunit --coverage-html=coverage

# Run specific test suite
./vendor/bin/phpunit --testsuite=Feature

# Stop on first failure
./vendor/bin/phpunit --stop-on-failure
```

### Pest Options (if using Pest)
```bash
# Run all tests
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage

# Run in parallel
./vendor/bin/pest --parallel
```

## Test Generation

### Create Test Files
```bash
# Create feature test
php artisan make:test <TestName>Test

# Create unit test
php artisan make:test <TestName>Test --unit

# Create model factory
php artisan make:factory <ModelName>Factory

# Create seeder
php artisan make:seeder <ModelName>Seeder
```

## Testing Best Practices

1. **Use RefreshDatabase** trait for database tests
2. **Use factories** for test data generation
3. **Test both success and failure cases**
4. **Mock external services** (APIs, emails, etc.)
5. **Keep tests independent and isolated**
6. **Use meaningful assertions**

## Docker Testing
```bash
docker-compose exec app php artisan test
docker-compose exec app ./vendor/bin/phpunit
```

## Environment Setup
Ensure `.env.testing` is configured properly for test database connections.
