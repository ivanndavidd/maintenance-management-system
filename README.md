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

## 📄 License

This project is proprietary software for GDN Commerce.

---

**Made with ❤️ for efficient warehouse operations**
