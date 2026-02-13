#!/bin/bash

echo "========================================="
echo "Deploy Docker Permission Fix"
echo "========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running on server
if [ ! -f "docker-compose.yml" ]; then
    echo -e "${RED}Error: docker-compose.yml not found!${NC}"
    echo "Please run this script from the project root directory."
    exit 1
fi

echo -e "${YELLOW}This script will:${NC}"
echo "1. Stop all containers"
echo "2. Rebuild images with new configuration"
echo "3. Start containers with fixed permissions"
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

echo ""
echo "========================================="
echo "Step 1: Stopping containers..."
echo "========================================="
docker-compose down
echo -e "${GREEN}✓ Containers stopped${NC}"
echo ""

echo "========================================="
echo "Step 2: Removing old images (optional)..."
echo "========================================="
docker rmi service-history-new-old_app service-history-new-old_queue 2>/dev/null || true
echo -e "${GREEN}✓ Old images removed${NC}"
echo ""

echo "========================================="
echo "Step 3: Building new images..."
echo "========================================="
echo "This may take a few minutes..."
docker-compose build --no-cache
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Build successful${NC}"
else
    echo -e "${RED}✗ Build failed!${NC}"
    exit 1
fi
echo ""

echo "========================================="
echo "Step 4: Starting containers..."
echo "========================================="
docker-compose up -d
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Containers started${NC}"
else
    echo -e "${RED}✗ Failed to start containers!${NC}"
    exit 1
fi
echo ""

echo "========================================="
echo "Step 5: Waiting for services to be ready..."
echo "========================================="
sleep 10
echo ""

echo "========================================="
echo "Step 6: Verifying setup..."
echo "========================================="

# Check user
echo -n "Checking container user... "
USER_CHECK=$(docker exec -it laravel_app whoami 2>&1 | tr -d '\r\n')
if [[ "$USER_CHECK" == "www-data" ]]; then
    echo -e "${GREEN}✓ Running as www-data${NC}"
else
    echo -e "${YELLOW}⚠ Running as: $USER_CHECK${NC}"
fi

# Check permissions
echo -n "Checking storage permissions... "
PERM_CHECK=$(docker exec -it laravel_app ls -ld /var/www/html/storage 2>&1 | awk '{print $1}')
if [[ "$PERM_CHECK" == drwxrwxr-x* ]]; then
    echo -e "${GREEN}✓ Permissions correct (775)${NC}"
else
    echo -e "${YELLOW}⚠ Permissions: $PERM_CHECK${NC}"
fi

# Test write
echo -n "Testing write access... "
if docker exec -it laravel_app touch /var/www/html/storage/test.txt 2>/dev/null; then
    docker exec -it laravel_app rm /var/www/html/storage/test.txt 2>/dev/null
    echo -e "${GREEN}✓ Write access OK${NC}"
else
    echo -e "${RED}✗ Cannot write to storage${NC}"
fi

# Check supervisord
echo -n "Checking supervisord... "
SUPERVISOR_CHECK=$(docker exec -it laravel_app supervisorctl status 2>&1 | grep -c "RUNNING")
if [ "$SUPERVISOR_CHECK" -gt 0 ]; then
    echo -e "${GREEN}✓ Supervisord running ($SUPERVISOR_CHECK processes)${NC}"
else
    echo -e "${YELLOW}⚠ Supervisord status unknown${NC}"
fi

echo ""
echo "========================================="
echo "Deployment Complete!"
echo "========================================="
echo ""
echo -e "${GREEN}✓ Docker containers rebuilt with fixed permissions${NC}"
echo ""
echo "Next steps:"
echo "1. Test import Excel in the application"
echo "2. Check logs: docker-compose logs -f app"
echo "3. Monitor queue: docker-compose logs -f queue"
echo ""
echo "If you encounter any issues:"
echo "- Check logs: docker-compose logs app"
echo "- Restart: docker-compose restart app queue"
echo "- See documentation: DOCKER_PERMISSION_FIX_PERMANENT.md"
echo ""
