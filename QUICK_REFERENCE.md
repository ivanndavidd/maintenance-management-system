# 🚀 Quick Reference - Deployment & Management

Quick commands untuk manage aplikasi Warehouse Maintenance di NUC.

---

## 📦 Deployment Commands

### Auto Deployment (via GitHub Actions)
```bash
# Di komputer lokal - push code ke GitHub
git add .
git commit -m "Your commit message"
git push origin main

# Deployment otomatis akan berjalan!
# Monitor di: GitHub → Actions tab
```

### Manual Deployment di NUC
```bash
# SSH ke NUC
ssh user@NUC_IP

# Navigate to app directory
cd /var/www/warehouse-maintenance

# Run deployment script
./deploy.sh
```

---

## 🐳 Docker Commands

### Check Container Status
```bash
docker compose ps
```

### View Logs
```bash
# All containers
docker compose logs -f

# Specific service
docker compose logs -f app        # Application
docker compose logs -f queue      # Queue worker
docker compose logs -f db         # Database
docker compose logs -f redis      # Redis
docker compose logs -f scheduler  # Scheduler
```

### Restart Containers
```bash
# Restart all
docker compose restart

# Restart specific service
docker compose restart app
docker compose restart queue
```

### Stop/Start Containers
```bash
# Stop all
docker compose down

# Start all
docker compose up -d

# Rebuild and start
docker compose up -d --build
```

---

## 🗄️ Database Commands

### Access Database
```bash
# MySQL shell
docker compose exec db mysql -uroot -p

# Or with specific database
docker compose exec db mysql -uwarehouse_user -p warehouse_maintenance
```

### Backup Database
```bash
# Manual backup
docker compose exec -T db mysqldump -uroot -p warehouse_maintenance > backup-$(date +%Y%m%d).sql

# Restore backup
docker compose exec -T db mysql -uroot -p warehouse_maintenance < backup-20240203.sql
```

### Run Migrations
```bash
docker compose exec app php artisan migrate

# With seed
docker compose exec app php artisan migrate --seed

# Rollback
docker compose exec app php artisan migrate:rollback

# Fresh (drop all tables and migrate)
docker compose exec app php artisan migrate:fresh --seed
```

---

## 🎯 Laravel Artisan Commands

### Clear Caches
```bash
# Clear all caches
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Clear all at once
docker compose exec app php artisan optimize:clear
```

### Cache Configuration
```bash
# Cache config
docker compose exec app php artisan config:cache

# Cache routes
docker compose exec app php artisan route:cache

# Cache views
docker compose exec app php artisan view:cache

# Optimize (all in one)
docker compose exec app php artisan optimize
```

### Queue Commands
```bash
# View queue jobs
docker compose exec app php artisan queue:work --once

# Clear failed jobs
docker compose exec app php artisan queue:flush

# Retry failed jobs
docker compose exec app php artisan queue:retry all

# List failed jobs
docker compose exec app php artisan queue:failed
```

### Create Admin User
```bash
docker compose exec app php artisan make:user:admin
```

---

## 🔧 Maintenance Commands

### Storage Permissions
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Application Down/Up (Maintenance Mode)
```bash
# Put app in maintenance mode
docker compose exec app php artisan down

# Bring app back up
docker compose exec app php artisan up
```

### Check Application Health
```bash
curl http://localhost/health

# Or from outside NUC
curl http://NUC_IP/health
```

---

## 📊 Monitoring Commands

### Check Disk Space
```bash
df -h
```

### Check Docker Disk Usage
```bash
docker system df

# Cleanup unused resources
docker system prune -a
```

### Check Memory Usage
```bash
free -h

# By container
docker stats
```

### Check Running Processes
```bash
htop

# Or
top
```

### Check Network Connections
```bash
netstat -tulpn | grep :80
netstat -tulpn | grep :3306
```

---

## 🔄 Update Commands

### Update System
```bash
sudo apt update
sudo apt upgrade -y
```

### Update Docker Images
```bash
cd /var/www/warehouse-maintenance
docker compose pull
docker compose up -d --build
```

---

## 🐛 Troubleshooting

### Container Won't Start
```bash
# Check logs
docker compose logs app

# Rebuild
docker compose down
docker compose build --no-cache
docker compose up -d
```

### Database Connection Error
```bash
# Check DB container
docker compose exec db mysql -uroot -p

# Check .env
cat .env | grep DB_
```

### Permission Errors
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Queue Not Processing
```bash
# Restart queue worker
docker compose restart queue

# Check queue logs
docker compose logs -f queue

# Manually process queue
docker compose exec app php artisan queue:work --once
```

### Port Already in Use
```bash
# Find process using port 80
sudo lsof -i :80

# Kill process
sudo kill -9 PID

# Or change port in docker-compose.yml
```

---

## 📝 Useful File Locations

```
/var/www/warehouse-maintenance/           # Application root
├── .env                                  # Environment config
├── storage/logs/laravel.log             # Application logs
├── backups/                             # Database backups
└── docker-compose.yml                   # Docker config
```

---

## 🔐 Security Commands

### Update Firewall Rules
```bash
# Allow new port
sudo ufw allow 443/tcp

# Check status
sudo ufw status

# Reload firewall
sudo ufw reload
```

### SSL Certificate (Let's Encrypt)
```bash
# Install certbot
sudo apt install certbot python3-certbot-nginx -y

# Get certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal test
sudo certbot renew --dry-run
```

---

## 📧 Email Testing

### Send Test Email
```bash
docker compose exec app php artisan tinker

# In tinker:
Mail::raw('Test email', function($msg) {
    $msg->to('your@email.com')->subject('Test');
});
```

### Check Queue for Failed Emails
```bash
docker compose exec app php artisan queue:failed

# Retry
docker compose exec app php artisan queue:retry all
```

---

## 🔄 Rollback

### Rollback to Previous Commit
```bash
# Check commits
git log --oneline -5

# Rollback
git reset --hard COMMIT_HASH

# Redeploy
./deploy.sh
```

### Rollback Database Migration
```bash
docker compose exec app php artisan migrate:rollback

# Rollback multiple steps
docker compose exec app php artisan migrate:rollback --step=2
```

---

## 💾 Backup & Restore

### Full Backup
```bash
# Database
docker compose exec -T db mysqldump -uroot -p warehouse_maintenance > db-backup.sql

# Application files (storage, uploads)
tar -czf storage-backup.tar.gz storage/app

# .env file
cp .env env-backup
```

### Full Restore
```bash
# Database
docker compose exec -T db mysql -uroot -p warehouse_maintenance < db-backup.sql

# Storage
tar -xzf storage-backup.tar.gz

# .env
cp env-backup .env
```

---

## 📱 Access Information

```
Application URL:  http://NUC_IP
Health Check:     http://NUC_IP/health
Database:         NUC_IP:3306
Redis:            NUC_IP:6379
```

---

## 🆘 Emergency Commands

### Force Stop All Containers
```bash
docker compose kill
docker compose down -v  # Also removes volumes (DANGER!)
```

### Reset Everything (DANGER - Will lose data!)
```bash
docker compose down -v
docker system prune -a --volumes -f
rm -rf storage/app/*
./setup-nuc.sh
```

### Emergency Rollback
```bash
# Stop containers
docker compose down

# Rollback code
git reset --hard LAST_WORKING_COMMIT

# Restore database backup
docker compose up -d db
sleep 10
docker compose exec -T db mysql -uroot -p warehouse_maintenance < backups/latest-backup.sql

# Start containers
docker compose up -d
```

---

## 📞 Support Checklist

When reporting issues, provide:

1. Container status: `docker compose ps`
2. Container logs: `docker compose logs app`
3. System info: `uname -a`
4. Disk space: `df -h`
5. Memory: `free -h`
6. Recent commits: `git log --oneline -5`

---

**Save this file for quick reference! 📚**
