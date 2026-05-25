# 🏭 Warehouse Maintenance System

A comprehensive warehouse maintenance management system built with Laravel, featuring automated deployment to Intel NUC via CI/CD pipeline.

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.3-blue?logo=php)
![Docker](https://img.shields.io/badge/Docker-Ready-blue?logo=docker)
![CI/CD](https://img.shields.io/badge/CI%2FCD-GitHub%20Actions-green?logo=github)

---

## ✨ Features

### 🔧 Maintenance Management

- **Corrective Maintenance (CM)** - Track and manage breakdown maintenance requests
- **Preventive Maintenance (PM)** - Schedule and execute preventive maintenance tasks
    - Cleaning groups management
    - SPR (Spare Part Replacement) groups
    - Calendar-based task scheduling
    - Shift assignment integration

### 📦 Inventory Management

- **Spareparts** - Track spare parts inventory with import/export capabilities
- **Tools** - Manage tool inventory
- **Assets** - Asset management and tracking
- **Stock Opname** - Regular stock counting and reconciliation
- **Stock Adjustments** - Approval-based stock adjustment workflow

### 📊 Reporting & Analytics

- KPI Dashboard
- Work Reports
- Maintenance Analytics
- Stock Reports

### 👥 User Management

- **Role-based Access Control**
    - Admin
    - Supervisor Maintenance
    - Staff Maintenance
- Shift-based task assignment
- Real-time notifications

### 📧 Email Notifications

- Automated email notifications for:
    - Maintenance request assignments
    - Task completions
    - Approval requests
    - Stock adjustments
    - PM task assignments

### 🎯 Additional Features

- Purchase Order Management
- Help Articles & Documentation
- Multi-shift Support (24/7 operations)
- Queue-based background job processing
- Redis caching for performance

---

## 🚀 Quick Start

### Prerequisites

- Docker & Docker Compose
- Git
- Ubuntu/Debian Linux (for NUC deployment)

### Docker Deployment

```bash
# Build and start containers
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate --seed

# Access application
http://localhost
```

---

## 📖 Documentation

- **[CI/CD Deployment Guide](CICD_DEPLOYMENT_GUIDE.md)** - Complete guide for automated deployment to NUC
- **[Quick Reference](QUICK_REFERENCE.md)** - Cheat sheet for common operations
- **[Docker Deployment Guide](DOCKER_DEPLOYMENT_GUIDE.md)** - Detailed Docker setup instructions

---

## 🏗️ Architecture

### Tech Stack

- **Backend:** Laravel 10.x (PHP 8.3)
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis 7
- **Frontend:** Blade Templates, Bootstrap 5
- **Containerization:** Docker & Docker Compose
- **CI/CD:** GitHub Actions
- **Web Server:** Nginx (in Docker)

### Docker Services

- `app` - Laravel application (Nginx + PHP-FPM)
- `db` - MySQL database
- `redis` - Redis cache and queue
- `queue` - Laravel queue worker
- `scheduler` - Laravel task scheduler

---

## 🔄 CI/CD Pipeline

This project uses GitHub Actions for automated deployment to Intel NUC.

### Deployment Flow

```
Push to main → GitHub Actions → SSH to NUC → Pull code →
Build containers → Run migrations → Optimize → Health check → ✅ Done!
```

### Setup CI/CD

1. Follow the **[CI/CD Deployment Guide](CICD_DEPLOYMENT_GUIDE.md)**
2. Configure GitHub Secrets
3. Push code to `main` branch
4. Deployment runs automatically!

---

---

## ☁️ Deployment ke Google Cloud Platform (Compute Engine + Cloud SQL)

### Prasyarat

- Akun GCP dengan billing aktif
- [Google Cloud SDK](https://cloud.google.com/sdk/docs/install) terinstall di lokal
- Docker terinstall di lokal

---

### Langkah 1 — Buat Project & Aktifkan API

```bash
gcloud projects create warehouse-maintenance --name="Warehouse Maintenance"
gcloud config set project warehouse-maintenance

gcloud services enable \
  compute.googleapis.com \
  sqladmin.googleapis.com \
  artifactregistry.googleapis.com \
  secretmanager.googleapis.com
```

---

### Langkah 2 — Buat Cloud SQL (MySQL 8.0)

```bash
gcloud sql instances create warehouse-db \
  --database-version=MYSQL_8_0 \
  --tier=db-f1-micro \
  --region=asia-southeast2 \
  --root-password=YOUR_ROOT_PASSWORD \
  --storage-type=SSD \
  --storage-size=20GB \
  --backup-start-time=02:00

# Buat database
gcloud sql databases create warehouse_maintenance --instance=warehouse-db
gcloud sql databases create warehouse_central --instance=warehouse-db

# Buat user
gcloud sql users create warehouse_user \
  --instance=warehouse-db \
  --password=YOUR_DB_PASSWORD
```

Catat **Connection Name** Cloud SQL (dibutuhkan di langkah 9):

```bash
gcloud sql instances describe warehouse-db --format="value(connectionName)"
# Output contoh: warehouse-maintenance:asia-southeast2:warehouse-db
```

---

### Langkah 3 — Buat Artifact Registry

```bash
gcloud artifacts repositories create warehouse-repo \
  --repository-format=docker \
  --location=asia-southeast2
```

---

### Langkah 4 — Build & Push Docker Image

```bash
# Authenticate Docker ke Artifact Registry
gcloud auth configure-docker asia-southeast2-docker.pkg.dev

# Build image
docker build -t warehouse-maintenance .

# Tag image
docker tag warehouse-maintenance \
  asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/warehouse-repo/app:latest

# Push image
docker push \
  asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/warehouse-repo/app:latest
```

---

### Langkah 5 — Buat Compute Engine VM

```bash
gcloud compute instances create warehouse-vm \
  --zone=asia-southeast2-a \
  --machine-type=e2-medium \
  --image-family=debian-12 \
  --image-project=debian-cloud \
  --boot-disk-size=30GB \
  --tags=http-server,https-server \
  --scopes=cloud-platform
```

Buka port HTTP/HTTPS:

```bash
gcloud compute firewall-rules create allow-http \
  --allow=tcp:80,tcp:443 \
  --target-tags=http-server,https-server
```

---

### Langkah 6 — Setup Docker di VM

SSH ke VM:

```bash
gcloud compute ssh warehouse-vm --zone=asia-southeast2-a
```

Di dalam VM, install Docker dan Cloud SQL Auth Proxy:

```bash
# Install Docker
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
newgrp docker

# Install Cloud SQL Auth Proxy
curl -o cloud-sql-proxy \
  https://storage.googleapis.com/cloud-sql-connectors/cloud-sql-proxy/v2.11.0/cloud-sql-proxy.linux.amd64
chmod +x cloud-sql-proxy
sudo mv cloud-sql-proxy /usr/local/bin/

# Authenticate Docker ke Artifact Registry
gcloud auth configure-docker asia-southeast2-docker.pkg.dev
```

---

### Langkah 7 — Generate APP_KEY

Jalankan di lokal:

```bash
docker run --rm \
  asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/warehouse-repo/app:latest \
  php artisan key:generate --show
```

Simpan output-nya (format: `base64:xxxxx...`).

---

### Langkah 8 — Buat file .env di VM

```bash
cat > ~/warehouse.env << 'EOF'
APP_NAME="Warehouse Maintenance"
APP_ENV=production
APP_KEY=base64:PASTE_KEY_DARI_LANGKAH_7
APP_DEBUG=false
APP_URL=http://YOUR_VM_EXTERNAL_IP
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=warehouse_maintenance
DB_USERNAME=warehouse_user
DB_PASSWORD=YOUR_DB_PASSWORD

CENTRAL_DB_DATABASE=warehouse_central

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=pir.maintenance@gdn-commerce.com
MAIL_PASSWORD=YOUR_MAIL_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=pir.maintenance@gdn-commerce.com
MAIL_FROM_NAME="Warehouse Maintenance"
EOF
```

---

### Langkah 9 — Jalankan Cloud SQL Proxy sebagai Service

```bash
# Ganti PROJECT:asia-southeast2:warehouse-db dengan Connection Name dari Langkah 2
sudo tee /etc/systemd/system/cloud-sql-proxy.service > /dev/null << 'EOF'
[Unit]
Description=Cloud SQL Auth Proxy
After=network.target

[Service]
ExecStart=/usr/local/bin/cloud-sql-proxy YOUR_PROJECT_ID:asia-southeast2:warehouse-db --port=3306
Restart=always
User=nobody

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl enable cloud-sql-proxy
sudo systemctl start cloud-sql-proxy
```

---

### Langkah 10 — Jalankan Container

```bash
# Jalankan Redis
docker run -d \
  --name redis \
  --restart unless-stopped \
  --network host \
  redis:7-alpine \
  redis-server --appendonly yes --maxmemory 256mb --maxmemory-policy allkeys-lru

# Jalankan App
docker run -d \
  --name warehouse-app \
  --restart unless-stopped \
  --network host \
  --env-file ~/warehouse.env \
  asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/warehouse-repo/app:latest

# Jalankan Queue Worker
docker run -d \
  --name warehouse-queue \
  --restart unless-stopped \
  --network host \
  --env-file ~/warehouse.env \
  asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/warehouse-repo/app:latest \
  php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

---

### Langkah 11 — Jalankan Migrasi Database

```bash
docker exec warehouse-app php artisan migrate --force
docker exec warehouse-app php artisan db:seed --force
```

---

### Langkah 12 — Verifikasi

```bash
# Cek container berjalan
docker ps

# Cek log aplikasi
docker logs warehouse-app

# Test akses
curl http://localhost
```

Buka browser: `http://YOUR_VM_EXTERNAL_IP`

---

### Update Aplikasi (Deploy Ulang)

Setiap ada perubahan code, jalankan di lokal lalu SSH ke VM:

```bash
# Di lokal — build dan push image baru
docker build -t warehouse-maintenance .
docker tag warehouse-maintenance \
  asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/warehouse-repo/app:latest
docker push \
  asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/warehouse-repo/app:latest

# Di VM — pull dan restart container
gcloud compute ssh warehouse-vm --zone=asia-southeast2-a -- "
  docker pull asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/warehouse-repo/app:latest && \
  docker stop warehouse-app warehouse-queue && \
  docker rm warehouse-app warehouse-queue && \
  docker run -d --name warehouse-app --restart unless-stopped --network host \
    --env-file ~/warehouse.env \
    asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/warehouse-repo/app:latest && \
  docker run -d --name warehouse-queue --restart unless-stopped --network host \
    --env-file ~/warehouse.env \
    asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/warehouse-repo/app:latest \
    php artisan queue:work --sleep=3 --tries=3 --max-time=3600 && \
  docker exec warehouse-app php artisan migrate --force
"
```

---

### Catatan Penting GCP

| Variable | Nilai |
|---|---|
| Region | `asia-southeast2` (Jakarta) |
| DB Connection Name | `YOUR_PROJECT_ID:asia-southeast2:warehouse-db` |
| DB Host di container | `127.0.0.1` (via Cloud SQL Proxy) |
| Redis Host di container | `127.0.0.1` (--network host) |

---

## 📄 License

This project is proprietary software for GDN Commerce.
