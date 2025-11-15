#!/bin/bash

set -e

echo "=========================================="
echo "  Sistem Absensi - Docker Deployment"
echo "=========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${RED}ERROR: Docker is not installed${NC}"
    echo "Please install Docker first: https://docs.docker.com/get-docker/"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}ERROR: Docker Compose is not installed${NC}"
    echo "Please install Docker Compose first: https://docs.docker.com/compose/install/"
    exit 1
fi

echo -e "${GREEN}✓ Docker and Docker Compose are installed${NC}"
echo ""

# Check if docker/.env.docker exists, if not copy from example
if [ ! -f docker/.env.docker ]; then
    echo -e "${YELLOW}⚠ docker/.env.docker not found, copying from example${NC}"
    cp docker/.env.docker.example docker/.env.docker
    echo -e "${RED}ERROR: Please configure docker/.env.docker with your credentials${NC}"
    echo "Required settings:"
    echo "  - APP_KEY (run: php artisan key:generate)"
    echo "  - DB_PASSWORD"
    echo "  - MYSQL_ROOT_PASSWORD"
    echo "  - REVERB_APP_KEY"
    echo "  - REVERB_APP_SECRET"
    exit 1
else
    echo -e "${GREEN}✓ docker/.env.docker exists${NC}"
fi

# Check if APP_KEY is set
if ! grep -q "APP_KEY=base64:" docker/.env.docker; then
    echo -e "${RED}ERROR: APP_KEY is not set in docker/.env.docker${NC}"
    echo "Run: php artisan key:generate"
    echo "Then copy the generated key to docker/.env.docker"
    exit 1
fi

echo ""
echo "Building Docker images..."
echo "This may take several minutes on first run..."
echo ""

# Build and start containers
docker-compose up -d --build

echo ""
echo "Waiting for services to be ready..."
sleep 10

# Check if MySQL is ready
echo "Checking MySQL connection..."
max_attempts=30
attempt=0

until docker-compose exec -T mysql mysqladmin ping -h localhost -u root --silent &> /dev/null; do
    attempt=$((attempt + 1))
    if [ $attempt -ge $max_attempts ]; then
        echo -e "${RED}ERROR: MySQL failed to start${NC}"
        echo "Check logs with: docker-compose logs mysql"
        exit 1
    fi
    echo "Waiting for MySQL... (${attempt}/${max_attempts})"
    sleep 2
done

echo -e "${GREEN}✓ MySQL is ready${NC}"

# Run migrations and seeders
echo ""
echo "Running database migrations and seeders..."
docker-compose exec -T app-node-1 php artisan migrate --force
docker-compose exec -T app-node-1 php artisan db:seed --class=ResetDatabaseSeeder --force

echo ""
echo -e "${GREEN}=========================================="
echo "  Deployment Successful!"
echo "==========================================${NC}"
echo ""
echo "Application is running at:"
echo -e "  ${GREEN}http://localhost${NC}"
echo ""
echo "WebSocket Server:"
echo -e "  ${GREEN}ws://localhost:8080${NC}"
echo ""
echo "Default Login Credentials:"
echo -e "  Admin: ${YELLOW}admin@absensi.com${NC} / ${YELLOW}password${NC}"
echo -e "  Karyawan: ${YELLOW}andi.wijaya@absensi.com${NC} / ${YELLOW}password${NC}"
echo ""
echo "Useful Commands:"
echo "  View logs: docker-compose logs -f"
echo "  Stop services: docker-compose down"
echo "  Restart services: docker-compose restart"
echo "  Check status: docker-compose ps"
echo ""
