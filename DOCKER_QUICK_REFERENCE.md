# 🐳 Docker Implementation - Quick Reference

## ✅ Files Created

### 🔧 Core Docker Files
- `Dockerfile.laravel` - PHP 8.1 FPM untuk Laravel
- `Dockerfile.baileys` - Node.js 18 untuk Baileys
- `docker-compose.yml` - Main orchestration file
- `docker-compose.override.yml` - Development overrides

### 📁 Docker Configuration Files
```
docker/
├── php/
│   ├── conf.d/laravel.ini          # PHP configuration
│   └── entrypoint.sh               # Laravel startup script
├── nginx/
│   ├── conf.d/default.conf         # Nginx virtual host
│   └── ssl/                        # SSL certificates (jika perlu)
└── mysql/
    └── init.sql                    # Database initialization
```

### ⚙️ Configuration Files
- `.env.docker` - Environment variables template
- `.dockerignore` - Files to exclude from build

### 📚 Documentation
- `DOCKER_SETUP.md` - Panduan lengkap (90+ halaman)
- `DOCKER_QUICK_REFERENCE.md` - File ini

### 🛠️ Helper Scripts
- `docker-control.sh` - Docker control script (Linux/Mac)
- `docker-control.ps1` - Docker control script (Windows)

---

## 🚀 Quick Start Commands

### Windows (PowerShell)
```powershell
# Start
.\docker-control.ps1 up

# View logs
.\docker-control.ps1 logs laravel

# Run migrations
.\docker-control.ps1 migrate

# Stop
.\docker-control.ps1 down
```

### Linux/Mac (Bash)
```bash
# Start
chmod +x docker-control.sh
./docker-control.sh up

# View logs
./docker-control.sh logs laravel

# Run migrations
./docker-control.sh migrate

# Stop
./docker-control.sh down
```

### Manual Docker Commands
```bash
# Build and start
docker-compose build
docker-compose up -d

# View status
docker-compose ps

# View logs
docker-compose logs -f laravel

# Run artisan command
docker-compose exec laravel php artisan migrate

# Stop
docker-compose down
```

---

## 🌐 Access Points

| Service | URL | Port |
|---------|-----|------|
| Frontend | http://localhost | 80 |
| Mailhog UI | http://localhost:8025 | 8025 |
| Baileys API | http://localhost:3000 | 3000 |
| MySQL | localhost | 3306 |
| Redis | localhost | 6379 |

---

## 📚 Services Architecture

```
Internet (:80)
    ↓
Nginx (Reverse Proxy)
    ↓
┌───────────────────────────────┐
│ Laravel PHP-FPM (:9000)       │
│ - Handles HTTP requests       │
│ - Business logic              │
│ - Database queries            │
└───────────────────────────────┘
    ↓
┌───────────────────────────────┐
│ MySQL (:3306)                 │
│ - Application data            │
│ - WhatsApp settings           │
└───────────────────────────────┘
    ↓
┌───────────────────────────────┐
│ Baileys Server (:3000)        │
│ - WhatsApp integration        │
│ - Message handling            │
└───────────────────────────────┘
    ↓
┌───────────────────────────────┐
│ Redis (:6379)                 │
│ - Session cache               │
│ - Application cache           │
└───────────────────────────────┘
```

---

## 🔐 Default Credentials

```
Database:
  Host:     mysql (docker) / localhost (host)
  Port:     3306
  Database: sikmasyita
  User:     client
  Password: Masyita@123
  Root:     root / root

Redis:
  Host:     redis (docker) / localhost (host)
  Port:     6379
  Password: (none)

Mailhog:
  SMTP: mailhog:1025
  UI:   http://localhost:8025
```

---

## 🔄 Common Operations

### Database Operations
```bash
# Run migrations
docker-compose exec laravel php artisan migrate

# Run fresh migrations (DANGER: deletes all data)
docker-compose exec laravel php artisan migrate:fresh

# Run seeding
docker-compose exec laravel php artisan db:seed

# Backup database
docker-compose exec mysql mysqldump -u client -p sikmasyita > backup.sql

# Restore database
cat backup.sql | docker-compose exec -T mysql mysql -u client -p sikmasyita
```

### Cache Operations
```bash
# Clear all caches
docker-compose exec laravel php artisan cache:clear
docker-compose exec laravel php artisan config:clear
docker-compose exec laravel php artisan view:clear

# Clear specific cache
docker-compose exec laravel php artisan cache:forget key_name
```

### WhatsApp/Baileys
```bash
# View Baileys logs
docker-compose logs -f baileys

# Check sessions
docker-compose exec baileys ls -la ./sessions

# Clear sessions (requires QR generation)
docker-compose exec baileys rm -rf ./sessions/*

# Restart Baileys
docker-compose restart baileys
```

### Development
```bash
# Open Laravel container shell
docker-compose exec laravel bash

# Run Artisan tinker
docker-compose exec laravel php artisan tinker

# Run tests
docker-compose exec laravel php artisan test

# Open Baileys shell
docker-compose exec baileys sh

# View Node logs
docker-compose logs -f baileys
```

---

## 📊 Monitoring

### Real-time Resource Usage
```bash
docker stats
```

### Container Status
```bash
docker-compose ps
```

### Application Logs
```bash
# All services
docker-compose logs

# Specific service
docker-compose logs laravel
docker-compose logs baileys
docker-compose logs mysql

# Follow logs
docker-compose logs -f laravel

# Last 100 lines
docker-compose logs --tail 100 laravel
```

---

## 🛑 Stopping & Cleanup

### Graceful Shutdown
```bash
# Stop containers (keeps data)
docker-compose stop

# Resume stopped containers
docker-compose start

# Stop and remove containers
docker-compose down
```

### Full Cleanup (⚠️ Deletes All Data!)
```bash
# Remove containers, networks, and volumes
docker-compose down -v

# Then rebuild and restart
docker-compose build
docker-compose up -d
```

---

## 🚨 Troubleshooting

### Port Already in Use
```bash
# Change port in docker-compose.yml or use override
# Example: Change port 80 to 8080
ports:
  - "8080:80"
```

### Database Connection Failed
```bash
# Check MySQL status
docker-compose ps mysql

# View MySQL logs
docker-compose logs mysql

# Restart MySQL
docker-compose restart mysql
```

### Out of Memory
```bash
# For Docker Desktop, increase memory:
# Settings > Resources > Memory slider (min. 4GB)

# Check current usage
docker stats

# Cleanup unused resources
docker system prune -a
```

### Application not starting
```bash
# Check logs
docker-compose logs laravel

# View error details
docker-compose logs --tail 50 laravel

# Rebuild images
docker-compose build --no-cache
docker-compose up -d
```

---

## 📚 Full Documentation

Untuk dokumentasi lengkap, lihat: [DOCKER_SETUP.md](DOCKER_SETUP.md)

---

## 🎯 Next Steps

1. **Update `.env`** if using custom configurations
2. **Build and start** with `docker-compose up -d`
3. **Run migrations** with appropriate artisan commands
4. **Access application** at http://localhost
5. **Monitor logs** with `docker-compose logs -f`

---

**✅ Docker Setup Complete!**

Your application is ready for containerized deployment.
