#!/bin/bash

# waBlast Docker Control Script
# Usage: ./docker-control.sh [command]

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Commands
case "${1}" in
    "up")
        echo -e "${BLUE}📦 Starting waBlast Docker containers...${NC}"
        docker-compose up -d
        echo -e "${GREEN}✅ Services started!${NC}"
        echo ""
        echo "Access points:"
        echo "  🌐 Frontend:      http://localhost"
        echo "  📧 Mailhog UI:    http://localhost:8025"
        echo "  💬 Baileys API:   http://localhost:3000"
        ;;

    "down")
        echo -e "${BLUE}🛑 Stopping waBlast Docker containers...${NC}"
        docker-compose down
        echo -e "${GREEN}✅ Services stopped!${NC}"
        ;;

    "restart")
        echo -e "${BLUE}🔄 Restarting waBlast Docker containers...${NC}"
        docker-compose restart
        echo -e "${GREEN}✅ Services restarted!${NC}"
        ;;

    "logs")
        service="${2:-.}"
        echo -e "${BLUE}📋 Showing logs for $service${NC}"
        docker-compose logs -f "$service"
        ;;

    "bash")
        service="${2:-laravel}"
        echo -e "${BLUE}🔧 Opening bash shell in $service${NC}"
        docker-compose exec "$service" bash
        ;;

    "mysql")
        echo -e "${BLUE}🐘 Connecting to MySQL...${NC}"
        docker-compose exec mysql mysql -u client -p
        ;;

    "artisan")
        command="${2:-artisan}"
        echo -e "${BLUE}⚙️  Running artisan command: $command${NC}"
        docker-compose exec laravel php artisan "$@"
        ;;

    "migrate")
        echo -e "${BLUE}📊 Running migrations...${NC}"
        docker-compose exec laravel php artisan migrate --force
        echo -e "${GREEN}✅ Migrations complete!${NC}"
        ;;

    "seed")
        echo -e "${BLUE}🌱 Running seeders...${NC}"
        docker-compose exec laravel php artisan db:seed --force
        echo -e "${GREEN}✅ Seeding complete!${NC}"
        ;;

    "cache-clear")
        echo -e "${BLUE}🧹 Clearing caches...${NC}"
        docker-compose exec laravel php artisan cache:clear
        docker-compose exec laravel php artisan config:clear
        docker-compose exec laravel php artisan view:clear
        echo -e "${GREEN}✅ Caches cleared!${NC}"
        ;;

    "ps"|"status")
        echo -e "${BLUE}📊 Service Status:${NC}"
        docker-compose ps
        ;;

    "build")
        service="${2:-.}"
        echo -e "${BLUE}🔨 Building $service${NC}"
        docker-compose build "$service"
        echo -e "${GREEN}✅ Build complete!${NC}"
        ;;

    "clean")
        echo -e "${RED}⚠️  This will remove all Docker volumes and data!${NC}"
        read -p "Are you sure? (yes/no) " -n 3 -r
        echo
        if [[ $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
            docker-compose down -v
            echo -e "${GREEN}✅ Clean complete!${NC}"
        else
            echo "Cancelled."
        fi
        ;;

    "backup")
        timestamp=$(date +%Y%m%d_%H%M%S)
        echo -e "${BLUE}💾 Backing up database...${NC}"
        docker-compose exec mysql mysqldump -u root -proot --all-databases > "backup-$timestamp.sql"
        echo -e "${GREEN}✅ Backup saved: backup-$timestamp.sql${NC}"
        ;;

    "stats")
        echo -e "${BLUE}📈 Resource Usage:${NC}"
        docker stats --no-stream
        ;;

    "help"|"--help"|"-h"|"")
        cat << EOF
${BLUE}waBlast Docker Control Script${NC}

Usage: ./docker-control.sh [command] [options]

Commands:
    up                  Start all containers
    down                Stop all containers
    restart             Restart all containers
    ps|status           Show service status
    
    logs [service]      Show logs (default: all)
    bash [service]      Open bash shell (default: laravel)
    
    artisan [cmd]       Run artisan command
    migrate             Run database migrations
    seed                Run database seeders
    cache-clear         Clear all caches
    
    mysql               Connect to MySQL CLI
    
    build [service]     Build Docker image
    clean               Remove containers and volumes (⚠️ deletes data!)
    backup              Backup database
    stats               Show resource usage
    
    help                Show this help message

Examples:
    ./docker-control.sh up
    ./docker-control.sh logs laravel
    ./docker-control.sh artisan migrate
    ./docker-control.sh bash baileys
    ./docker-control.sh mysql

${GREEN}For more information, see DOCKER_SETUP.md${NC}
EOF
        ;;

    *)
        echo -e "${RED}❌ Unknown command: $1${NC}"
        echo "Run './docker-control.sh help' for available commands"
        exit 1
        ;;
esac
