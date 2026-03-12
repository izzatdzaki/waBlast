# Makefile for waBlast Docker Commands
# For Linux/Mac users - type 'make' to see available commands

.PHONY: help up down restart logs ps build bash mysql artisan migrate seed cache-clear clean backup stats

help:
	@echo "🐳 waBlast Docker - Available Commands"
	@echo ""
	@echo "Startup & Control:"
	@echo "  make up              - Start all containers"
	@echo "  make down            - Stop all containers"
	@echo "  make restart         - Restart containers"
	@echo "  make ps              - Show service status"
	@echo ""
	@echo "Development:"
	@echo "  make bash [s=service] - Open bash shell (default: laravel)"
	@echo "  make logs [s=service] - View logs (default: all)"
	@echo "  make mysql           - Connect to MySQL CLI"
	@echo ""
	@echo "Database:"
	@echo "  make migrate         - Run database migrations"
	@echo "  make seed            - Run database seeders"
	@echo "  make backup          - Backup database"
	@echo ""
	@echo "Maintenance:"
	@echo "  make cache-clear     - Clear all caches"
	@echo "  make build [s=service] - Build Docker image"
	@echo "  make clean           - Remove containers & volumes (⚠️  deletes data!)"
	@echo "  make stats           - Show resource usage"
	@echo ""
	@echo "Examples:"
	@echo "  make up"
	@echo "  make logs s=baileys"
	@echo "  make bash s=laravel"
	@echo "  make artisan cmd='migrate:fresh'"

up:
	@echo "📦 Starting waBlast Docker containers..."
	docker-compose up -d
	@echo "✅ Services started!"
	@echo ""
	@echo "Access points:"
	@echo "  🌐 Frontend:      http://localhost"
	@echo "  📧 Mailhog UI:    http://localhost:8025"
	@echo "  💬 Baileys API:   http://localhost:3000"

down:
	@echo "🛑 Stopping waBlast Docker containers..."
	docker-compose down
	@echo "✅ Services stopped!"

restart:
	@echo "🔄 Restarting waBlast Docker containers..."
	docker-compose restart
	@echo "✅ Services restarted!"

ps:
	@echo "📊 Service Status:"
	docker-compose ps

logs:
	@docker-compose logs -f $(s)

bash:
	@docker-compose exec $(if $(s),$(s),laravel) bash

mysql:
	@echo "🐘 Connecting to MySQL..."
	docker-compose exec mysql mysql -u client -p

artisan:
	@docker-compose exec laravel php artisan $(cmd)

migrate:
	@echo "📊 Running migrations..."
	docker-compose exec laravel php artisan migrate --force
	@echo "✅ Migrations complete!"

seed:
	@echo "🌱 Running seeders..."
	docker-compose exec laravel php artisan db:seed --force
	@echo "✅ Seeding complete!"

cache-clear:
	@echo "🧹 Clearing caches..."
	docker-compose exec laravel php artisan cache:clear
	docker-compose exec laravel php artisan config:clear
	docker-compose exec laravel php artisan view:clear
	@echo "✅ Caches cleared!"

build:
	@echo "🔨 Building $(if $(s),$(s),.)"
	docker-compose build $(s)
	@echo "✅ Build complete!"

clean:
	@echo "⚠️  This will remove all Docker volumes and data!"
	@read -p "Are you sure? (yes/no) " response; \
	if [ "$$response" = "yes" ]; then \
		docker-compose down -v; \
		echo "✅ Clean complete!"; \
	else \
		echo "Cancelled."; \
	fi

backup:
	@echo "💾 Backing up database..."
	@docker-compose exec mysql mysqldump -u root -proot --all-databases > backup-$$(date +%Y%m%d_%H%M%S).sql
	@echo "✅ Backup saved!"

stats:
	@echo "📈 Resource Usage:"
	docker stats --no-stream
