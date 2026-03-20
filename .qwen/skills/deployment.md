# Deployment & Production Skill

Handles deployment, production configuration, and environment management.

## Environment Files

### Environment Configuration
```bash
# Copy example files
cp .env.example .env
cp .env.production.example .env.production

# Generate application key
php artisan key:generate
```

### Environment Variables
Key files to configure:
- `.env` - Local environment
- `.env.production` - Production environment
- `.env.testing` - Testing environment

## Production Deployment

### Pre-deployment Checklist
1. Update `.env.production` with production values
2. Set `APP_DEBUG=false`
3. Configure database credentials
4. Set up Azure token refresh (see AZURE_TOKEN_REFRESH_IMPLEMENTATION.md)
5. Configure CSP settings (see CSP_CUSTOMIZATION_GUIDE.md)

### Deployment Commands
```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run migrations
php artisan migrate --force

# Clear optimization (when making changes)
php artisan optimize:clear
```

### Docker Production
```bash
# Build production image
docker-compose -f docker-compose.yml build

# Deploy with production settings
docker-compose -f docker-compose.prod.yml up -d
```

## Database Pooling

For production database performance:
- See DATABASE_POOLING.md for configuration
- See DATABASE_POOLING_QUICKSTART.md for quick setup
- Adjust pool sizes based on expected load

## Monitoring

### Health Checks
```bash
# Check application health
curl http://<your-domain>/health

# Check database connection
php artisan db:show
```

### Logs
```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# View Docker logs
docker-compose logs -f app
```

## Rollback Procedures

### Code Rollback
```bash
git revert <commit-hash>
git push origin main
```

### Database Rollback
```bash
php artisan migrate:rollback --step=<number>
```

## Security Considerations

1. Never commit `.env` files
2. Use strong database passwords
3. Enable HTTPS in production
4. Configure proper CORS settings
5. Keep dependencies updated
