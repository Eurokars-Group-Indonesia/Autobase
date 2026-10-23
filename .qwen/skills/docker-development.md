# Docker Development Skill

Handles Docker container operations for this Laravel project.

## Container Management

### Start/Stop Containers
```bash
# Start all containers
docker-compose up -d

# Start with rebuild
docker-compose up -d --build

# Stop all containers
docker-compose down

# Stop and remove volumes
docker-compose down -v
```

### Container Access
```bash
# Access app container shell
docker-compose exec app bash

# Access database container
docker-compose exec db bash

# Run artisan command in container
docker-compose exec app php artisan <command>

# Run composer in container
docker-compose exec app composer <command>
```

### View Logs
```bash
# View all logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f db
docker-compose logs -f nginx
```

### Container Status
```bash
# List running containers
docker-compose ps

# View resource usage
docker stats

# Inspect container details
docker inspect <container_id>
```

## Build Operations

### Rebuild Containers
```bash
# Rebuild specific service
docker-compose build app

# Rebuild all services
docker-compose build

# Rebuild with no cache
docker-compose build --no-cache
```

### Windows Monitoring Scripts
This project includes Windows batch scripts:
- `docker-monitor.bat` - Monitor container status
- `docker-rebuild.bat` - Quick rebuild containers

## Common Development Tasks

### Install Dependencies
```bash
docker-compose exec app composer install
docker-compose exec app npm install
docker-compose exec app npm run dev
```

### Database Operations
```bash
# Run migrations in container
docker-compose exec app php artisan migrate

# Seed database
docker-compose exec app php artisan db:seed

# Access MySQL CLI
docker-compose exec db mysql -u<user> -p<password> <database>
```

### Clear Caches
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan optimize:clear
```

## Troubleshooting

### Restart Services
```bash
docker-compose restart app
docker-compose restart nginx
```

### Remove and Recreate
```bash
docker-compose down
docker-compose up -d --build --force-recreate
```

### Check Docker Resources
```bash
docker system df
docker volume ls
docker network ls
```

## Project-Specific Notes
- See DOCKER_OPTIMIZATION.md for optimization details
- See DEPLOY_DOCKER_FIX.md for deployment fixes
- Database pooling configured for better performance
