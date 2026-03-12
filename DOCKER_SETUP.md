# 🐳 waBlast Docker Implementation Guide

## 📋 Daftar Isi
1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Quick Start](#quick-start)
4. [Struktur Services](#struktur-services)
5. [Environment Configuration](#environment-configuration)
6. [Volume & Persistence](#volume--persistence)
7. [Network Architecture](#network-architecture)
8. [Production Deployment](#production-deployment)
9. [Troubleshooting](#troubleshooting)
10. [Monitoring & Logs](#monitoring--logs)

---

## Overview

Implementasi Docker untuk waBlast menggunakan **multi-container architecture** dengan koordinasi via `docker-compose`:

```
┌─────────────────────────────────────────────────────────────┐
│                    Internet (Port 80/443)                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                    ┌────▼─────┐
                    │   Nginx   │ (Reverse Proxy + Static)
                    └────┬─────┘
                         │
      ┌──────────────────┼──────────────────┐
      │                  │                  │
  ┌───▼────┐        ┌────▼─────┐      ┌────▼────┐
  │ Laravel │        │ Baileys  │      │ Mailhog │
  │ PHP-FPM │        │ Node.js  │      │ (Emails)│
  └───┬────┘        └────┬─────┘      └─────────┘
      │                  │
      └──────────┬───────┘
                 │
      ┌──────────┼──────────┐
      │          │          │
  ┌───▼────┐ ┌──▼───┐ ┌────▼──┐
  │ MySQL  │ │Redis │ │ Vol.  │
  │(Data)  │ │Cache │ │Storage│
  └────────┘ └──────┘ └───────┘
```

---

## Prerequisites

### System Requirements
- **Docker**: v20.10+
- **Docker Compose**: v1.29+
- **RAM**: Minimum 4GB
- **Disk**: Minimum 5GB free space

### Installation

**Windows (WSL2):**
```powershell
# Download Docker Desktop for Windows
# https://www.docker.com/products/docker-desktop

# After installation, enable WSL2
# Check: Open PowerShell as Administrator
docker --version
docker-compose --version
```

**Linux/Mac:**
```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

---

## Quick Start

### 1️⃣ Setup Environment

```bash
# Copy environment file
cp .env.docker .env

# Update .env dengan konfigurasi Anda (jika perlu)
# Minimal setup sudah lengkap
```

### 2️⃣ Build Images

```bash
# Build all Docker images
docker-compose build

# Build specific service
docker-compose build laravel
docker-compose build baileys
```

### 3️⃣ Start Services

```bash
# Start all services (background)
docker-compose up -d

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f laravel
docker-compose logs -f baileys
docker-compose logs -f mysql
```

### 4️⃣ Initialize Database

```bash
# Migrations sudah otomatis saat startup
# Cek status
docker-compose exec laravel php artisan migrate:status

# Manual migration (jika perlu)
docker-compose exec laravel php artisan migrate
docker-compose exec laravel php artisan db:seed
```

### 5️⃣ Akses Aplikasi

```
🌐 Frontend:        http://localhost
📧 Mailhog UI:      http://localhost:8025
💬 Baileys Server:  http://localhost:3000
📊 MySQL:           localhost:3306
💾 Redis:           localhost:6379
```

### 6️⃣ Stop Services

```bash
# Stop all services
docker-compose down

# Stop and remove volumes (WARNING: hapus semua data!)
docker-compose down -v

# Stop tanpa shutdown
docker-compose stop

# Resume stopped services
docker-compose start
```

---

## Struktur Services

### 🐘 MySQL (Database)
```yaml
Service:    mysql
Image:      mysql:8.0
Port:       3306
Username:   client
Password:   Masyita@123
Database:   sikmasyita, baileys_api
Volume:     mysql_data (persistent)
Healthcheck: Enabled ✅
```

**Useful Commands:**
```bash
# Access MySQL CLI
docker-compose exec mysql mysql -u client -p

# Backup database
docker-compose exec mysql mysqldump -u client -p sikmasyita > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u client -p sikmasyita < backup.sql
```

### 🚀 Laravel (PHP-FPM)
```yaml
Service:      laravel
Image:        Custom (Dockerfile.laravel)
Port:         9000 (FPM)
Framework:    Laravel 8.x
PHP Version:  8.1
Depends On:   mysql, redis
Volumes:      Source code, storage, bootstrap/cache
Entrypoint:   Auto-migration on startup
```

**Useful Commands:**
```bash
# Artisan commands
docker-compose exec laravel php artisan tinker
docker-compose exec laravel php artisan make:model MyModel
docker-compose exec laravel php artisan cache:clear

# Composer commands
docker-compose exec laravel composer require vendor/package

# Run tests
docker-compose exec laravel php artisan test
```

### 💬 Baileys (Node.js WhatsApp)
```yaml
Service:    baileys
Image:      Custom (Dockerfile.baileys)
Port:       3000
Framework:  Express.js + Baileys
Depends On: mysql
Volumes:    sessions/ (persistent)
Healthcheck: Enabled ✅
```

**Useful Commands:**
```bash
# View Baileys logs
docker-compose logs -f baileys

# Check session files
docker-compose exec baileys ls -la ./sessions

# Restart Baileys
docker-compose restart baileys
```

### 🌐 Nginx (Web Server)
```yaml
Service:    nginx
Image:      nginx:alpine
Ports:      80, 443
Config:     docker/nginx/conf.d/default.conf
Upstream:   laravel:9000 (PHP-FPM)
```

**Useful Commands:**
```bash
# Test Nginx configuration
docker-compose exec nginx nginx -t

# View Nginx logs
docker-compose logs -f nginx

# Reload Nginx
docker-compose exec nginx nginx -s reload
```

### 💾 Redis (Cache)
```yaml
Service:    redis
Image:      redis:7-alpine
Port:       6379
Volume:     redis_data (persistent)
Healthcheck: Enabled ✅
```

### 📧 Mailhog (Email Testing)
```yaml
Service:    mailhog
Image:      mailhog/mailhog
Ports:      1025 (SMTP), 8025 (UI)
Browser:    http://localhost:8025
```

---

## Environment Configuration

### 📝 .env.docker (Template)

File `.env.docker` sudah disediakan dengan konfigurasi default. Untuk production, ubah:

```bash
# ⚠️ PRODUCTION CRITICAL

# 1. Change APP_DEBUG to false
APP_DEBUG=false

# 2. Set strong passwords
DB_PASSWORD=your_strong_db_password
DB_ROOT_PASSWORD=your_strong_root_password

# 3. Set strong APP_KEY (sudah di-generate, ubah jika perlu)
APP_KEY=base64:your_generated_key

# 4. Update APP_URL
APP_URL=https://yourdomain.com

# 5. Set Redis password
REDIS_PASSWORD=your_redis_password

# 6. Set API_KEY untuk Baileys
API_KEY=your_secure_api_key
```

### Restart untuk Apply Changes:
```bash
docker-compose down
docker-compose up -d
```

---

## Volume & Persistence

### 📦 Docker Volumes

```yaml
mysql_data:       # Database files
redis_data:       # Redis persistence
```

### 📁 Bind Mounts

```yaml
./               # Full source code
./storage        # Laravel storage files
./sessions       # Baileys WhatsApp sessions
```

### 🔄 Backup Strategy

```bash
# Backup MySQL
docker-compose exec mysql mysqldump -u root -p --all-databases > backup-full.sql

# Backup Redis
docker-compose exec redis redis-cli BGSAVE

# Backup application files
tar -czf backup-app.tar.gz --exclude=vendor --exclude=node_modules .

# Restore MySQL
cat backup-full.sql | docker-compose exec -T mysql mysql -u root -p
```

---

## Network Architecture

### 🌐 Internal Network: `wablast_network`

Semua services terhubung dalam bridge network yang sama:

```
Service Name    Internal DNS          External
─────────────────────────────────────────────
mysql          mysql:3306            localhost:3306
laravel        laravel:9000          (internal only)
baileys        baileys:3000          localhost:3000
nginx          nginx:80              localhost:80
redis          redis:6379            localhost:6379
mailhog        mailhog:1025          localhost:1025
```

### Inter-Service Communication

```php
// Dari Laravel ke MySQL
DB_HOST=mysql

// Dari Laravel ke Baileys
BAILEYS_API_URL=http://baileys:3000

// Dari Baileys ke Laravel
LARAVEL_SERVER_URL=http://nginx:80
```

---

## Production Deployment

### ☁️ Best Practices untuk Production

#### 1. Security
```yaml
# Generate strong passwords
# Update .env dengan values production
# Gunakan environment variables terenkripsi
```

#### 2. SSL/HTTPS
```bash
# Enable SSL di Nginx - uncomment bagian HTTPS di docker/nginx/conf.d/default.conf
# Copy certificate files ke docker/nginx/ssl/
```

#### 3. Scaling
```bash
# Scale services jika traffic tinggi
docker-compose up -d --scale laravel=3  # Multiple PHP containers
```

#### 4. Monitoring
```bash
# Install monitoring tools
docker run -d --name prometheus prom/prometheus
docker run -d --name grafana grafana/grafana
```

### 🚀 Deployment ke Cloud

#### AWS ECS/Fargate
```bash
# Create ECR repositories
aws ecr create-repository --repository-name wablast-laravel
aws ecr create-repository --repository-name wablast-baileys

# Build and push
docker build -f Dockerfile.laravel -t wablast-laravel .
docker tag wablast-laravel:latest AWS_ACCOUNT.dkr.ecr.REGION.amazonaws.com/wablast-laravel:latest
docker push AWS_ACCOUNT.dkr.ecr.REGION.amazonaws.com/wablast-laravel:latest
```

#### Google Cloud Run
```bash
# Build and push to GCR
docker build -f Dockerfile.laravel -t gcr.io/PROJECT_ID/wablast-laravel .
docker push gcr.io/PROJECT_ID/wablast-laravel
gcloud run deploy wablast --image gcr.io/PROJECT_ID/wablast-laravel
```

#### Kubernetes (K8s)
```bash
# Create namespace
kubectl create namespace wablast

# Apply deployments
kubectl apply -f k8s-deployment.yaml

# Scale replicas
kubectl scale deployment laravel --replicas=3 -n wablast
```

---

## Troubleshooting

### 🔴 Common Issues

#### 1. Port Already in Use
```bash
# Port 80 sudah digunakan
# Ganti di docker-compose.yml
ports:
  - "8080:80"  # Nginx di port 8080

# Atau stop yang memakainya
docker ps
docker stop CONTAINER_ID
```

#### 2. MySQL Connection Failed
```bash
# Check MySQL logs
docker-compose logs mysql

# Wait for MySQL ready
docker-compose exec mysql mysqladmin ping -u root -p

# Restart MySQL
docker-compose restart mysql

# Reset MySQL data (⚠️ deletes data!)
docker-compose down -v
docker-compose up -d
```

#### 3. Laravel Migration Error
```bash
# Check detailed error
docker-compose logs laravel

# Manual migration
docker-compose exec laravel php artisan migrate --force

# Rollback jika perlu
docker-compose exec laravel php artisan migrate:rollback
```

#### 4. Baileys Session Error
```bash
# Check sessions folder
docker-compose exec baileys ls -la ./sessions

# Clear sessions
docker-compose exec baileys rm -rf ./sessions/*

# Restart Baileys untuk generate QR baru
docker-compose restart baileys

# Check logs
docker-compose logs -f baileys
```

#### 5. Out of Memory
```bash
# Increase Docker desktop memory
# Docker Desktop > Settings > Resources > Memory slider

# Or check current usage
docker stats

# Clean up unused resources
docker-compose down -v
docker system prune -a
```

### 🐛 Debug Mode

```bash
# Enable debug logging
docker-compose exec laravel php artisan log:tail

# Run commands dengan verbose
docker-compose exec laravel php artisan migrate -v

# Inspect container processes
docker top wablast_laravel
docker stats wablast_laravel
```

---

## Monitoring & Logs

### 📊 View Logs

```bash
# All services
docker-compose logs

# Specific service
docker-compose logs laravel

# Follow logs (tail -f)
docker-compose logs -f baileys

# Last 100 lines
docker-compose logs -n 100 mysql

# Timestamps
docker-compose logs -t nginx
```

### 🔍 Container Health

```bash
# Check service status
docker-compose ps

# Detailed inspect
docker-compose exec laravel php artisan health

# MySQL health
docker-compose exec mysql mysqladmin ping -u client -p

# Redis health
docker-compose exec redis redis-cli ping
```

### 💾 Resource Usage

```bash
# Real-time stats
docker stats

# Disk space
docker system df

# Clean up unused resources
docker system prune
docker container prune
docker image prune
docker volume prune
```

### 📈 Application Monitoring

```bash
# Laravel logs (application layer)
docker-compose exec laravel cat storage/logs/laravel-*.log

# Nginx logs
docker-compose exec nginx tail -f /var/log/nginx/access.log

# PHP-FPM logs
docker-compose exec laravel cat /var/log/php-error.log
```

---

## Command Reference

```bash
# ===== BUILD & STARTUP =====
docker-compose build                    # Build images
docker-compose build laravel            # Build specific service
docker-compose up -d                    # Start all (background)
docker-compose up                       # Start all (foreground)

# ===== STOP & CLEANUP =====
docker-compose down                     # Stop all services
docker-compose down -v                  # Stop and remove volumes
docker-compose stop                     # Stop (keep containers)
docker-compose start                    # Resume stopped services
docker-compose restart laravel          # Restart specific service

# ===== EXECUTE COMMANDS =====
docker-compose exec laravel php artisan migrate
docker-compose exec mysql mysql -u client -p
docker-compose exec baileys npm logs
docker-compose exec -T service command  # Disable TTY mode

# ===== LOGS & MONITORING =====
docker-compose logs                     # View all logs
docker-compose logs -f laravel          # Follow logs
docker-compose ps                       # Service status
docker-compose stats                    # Resource usage

# ===== CLEANUP =====
docker system prune -a                  # Clean all unused
docker volume prune                     # Remove unused volumes
docker image prune                      # Remove unused images
```

---

## 📞 Support & Contact

Untuk issues atau pertanyaan:
1. Check logs: `docker-compose logs -f [service]`
2. Check .env configuration
3. Verify database connectivity
4. Review troubleshooting section above

---

**✅ Setup Complete!**

Aplikasi waBlast Anda sudah siap di Docker. Akses di:
- 🌐 http://localhost
- 📧 http://localhost:8025 (Mailhog)
- 💬 http://localhost:3000 (Baileys API)
