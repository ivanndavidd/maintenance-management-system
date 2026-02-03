# 🚀 CI/CD Deployment Guide for NUC

Complete guide untuk setup automated deployment ke Intel NUC menggunakan GitHub Actions.

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [NUC Setup](#nuc-setup)
3. [GitHub Repository Setup](#github-repository-setup)
4. [GitHub Secrets Configuration](#github-secrets-configuration)
5. [First Deployment](#first-deployment)
6. [Monitoring & Troubleshooting](#monitoring--troubleshooting)
7. [Rollback Strategy](#rollback-strategy)

---

## 1. Prerequisites

### Yang Anda Butuhkan:

- ✅ Intel NUC dengan Ubuntu/Debian Linux
- ✅ Docker & Docker Compose terinstall di NUC
- ✅ SSH access ke NUC
- ✅ GitHub repository
- ✅ Domain/IP address untuk akses NUC

---

## 2. NUC Setup

### Step 1: Install Docker di NUC

Jalankan command berikut di NUC:

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user to docker group
sudo usermod -aG docker $USER

# Install Docker Compose
sudo apt install docker-compose-plugin -y

# Verify installation
docker --version
docker compose version
```

### Step 2: Setup SSH Key Authentication

**Di komputer lokal Anda:**

```bash
# Generate SSH key (jika belum punya)
ssh-keygen -t ed25519 -C "deployment-key" -f ~/.ssh/nuc_deploy_key

# Copy public key ke NUC
ssh-copy-id -i ~/.ssh/nuc_deploy_key.pub user@NUC_IP_ADDRESS
```

**Test SSH connection:**

```bash
ssh -i ~/.ssh/nuc_deploy_key user@NUC_IP_ADDRESS
```

### Step 3: Setup Deployment Directory di NUC

```bash
# SSH ke NUC
ssh user@NUC_IP_ADDRESS

# Create deployment directory
sudo mkdir -p /var/www/warehouse-maintenance
sudo chown -R $USER:$USER /var/www/warehouse-maintenance
cd /var/www/warehouse-maintenance

# Clone repository
git clone https://github.com/YOUR_USERNAME/warehouse-maintenance.git .

# Create .env file
cp .env.docker .env

# Generate APP_KEY
php artisan key:generate --show
# Copy the generated key, kita akan pakai nanti
```

### Step 4: Configure Firewall (Optional tapi recommended)

```bash
# Allow HTTP, HTTPS, and SSH
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS (jika pakai SSL)
sudo ufw enable
```

---

## 3. GitHub Repository Setup

### Step 1: Push Repository ke GitHub

**Di komputer lokal:**

```bash
# Initialize git (jika belum)
cd d:\laragon\www\warehouse-maintenance

# Add all files
git add .

# Commit
git commit -m "Initial commit with CI/CD setup"

# Add remote (ganti dengan URL repo Anda)
git remote add origin https://github.com/YOUR_USERNAME/warehouse-maintenance.git

# Push to GitHub
git push -u origin main
```

### Step 2: Verify GitHub Actions

Setelah push, GitHub Actions akan otomatis terdeteksi dari file `.github/workflows/deploy-to-nuc.yml`.

---

## 4. GitHub Secrets Configuration

### Setup GitHub Secrets

1. **Buka GitHub Repository** → Settings → Secrets and variables → Actions

2. **Klik "New repository secret"** dan tambahkan secrets berikut:

| Secret Name | Description | Example Value |
|------------|-------------|---------------|
| `NUC_SSH_KEY` | Private SSH key untuk akses ke NUC | Content dari `~/.ssh/nuc_deploy_key` |
| `NUC_HOST` | IP address atau hostname NUC | `192.168.1.100` atau `nuc.example.com` |
| `NUC_USER` | Username SSH di NUC | `ubuntu` atau `admin` |
| `DEPLOY_PATH` | Path deployment di NUC | `/var/www/warehouse-maintenance` |
| `APP_KEY` | Laravel APP_KEY | `base64:xxxxx...` (dari `php artisan key:generate --show`) |
| `DB_PASSWORD` | Database password | `your-strong-password` |

### Cara Add Secret `NUC_SSH_KEY`:

**Di komputer lokal:**

```bash
# Windows (PowerShell)
Get-Content ~\.ssh\nuc_deploy_key | Set-Clipboard

# Linux/Mac
cat ~/.ssh/nuc_deploy_key | pbcopy  # Mac
cat ~/.ssh/nuc_deploy_key | xclip   # Linux
```

Kemudian paste di GitHub Secret value.

---

## 5. First Deployment

### Manual Trigger First Deployment

1. **Buka GitHub Repository** → Actions tab
2. **Klik workflow "Deploy to NUC"**
3. **Klik "Run workflow"** → Run workflow

**Atau** push code ke branch `main`:

```bash
git add .
git commit -m "Trigger first deployment"
git push origin main
```

### Monitor Deployment

1. **GitHub** → Actions tab → Klik running workflow
2. **Watch the logs** untuk melihat progress deployment

### Verify Deployment Success

**Di NUC:**

```bash
# Check running containers
docker compose ps

# Check application logs
docker compose logs -f app

# Check health endpoint
curl http://localhost/health
```

**Di browser:**
- Buka `http://NUC_IP_ADDRESS`
- Anda akan melihat aplikasi warehouse maintenance

---

## 6. Monitoring & Troubleshooting

### Check Container Status

```bash
# SSH ke NUC
ssh user@NUC_IP_ADDRESS

# Navigate to deployment directory
cd /var/www/warehouse-maintenance

# Check all containers
docker compose ps

# Check logs
docker compose logs -f app      # Application logs
docker compose logs -f queue    # Queue worker logs
docker compose logs -f db       # Database logs
```

### Common Issues & Solutions

#### Issue 1: Container tidak start

```bash
# Check detailed logs
docker compose logs app

# Rebuild container
docker compose down
docker compose build --no-cache
docker compose up -d
```

#### Issue 2: Database connection error

```bash
# Check database container
docker compose exec db mysql -uroot -p

# Verify .env configuration
cat .env | grep DB_
```

#### Issue 3: Permission errors

```bash
# Fix storage permissions
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

#### Issue 4: Queue not processing

```bash
# Restart queue worker
docker compose restart queue

# Check queue logs
docker compose logs -f queue
```

### Health Check Endpoints

Add this route to `routes/web.php` if not exists:

```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
    ]);
});
```

---

## 7. Rollback Strategy

### Manual Rollback

**Jika deployment gagal dan perlu rollback:**

```bash
# SSH ke NUC
ssh user@NUC_IP_ADDRESS
cd /var/www/warehouse-maintenance

# Check git log
git log --oneline -5

# Rollback to previous commit
git reset --hard COMMIT_HASH

# Rebuild containers
docker compose down
docker compose build --no-cache
docker compose up -d

# Run migrations down (if needed)
docker compose exec app php artisan migrate:rollback
```

### Database Backup Before Deployment

**Modify workflow** untuk auto-backup database sebelum deploy:

Add this step ke `.github/workflows/deploy-to-nuc.yml` sebelum deployment:

```yaml
- name: Backup Database
  env:
    SSH_HOST: ${{ secrets.NUC_HOST }}
    SSH_USER: ${{ secrets.NUC_USER }}
  run: |
    ssh $SSH_USER@$SSH_HOST << 'ENDSSH'
      cd ${{ secrets.DEPLOY_PATH }}

      # Create backup directory
      mkdir -p backups

      # Backup database
      BACKUP_FILE="backups/db-backup-$(date +%Y%m%d-%H%M%S).sql"
      docker compose exec -T db mysqldump -uroot -p${{ secrets.DB_ROOT_PASSWORD }} warehouse_maintenance > $BACKUP_FILE

      echo "✅ Database backed up to $BACKUP_FILE"

      # Keep only last 7 backups
      ls -t backups/db-backup-*.sql | tail -n +8 | xargs -r rm
    ENDSSH
```

---

## 8. Post-Deployment Checklist

Setelah deployment, verify hal-hal berikut:

- [ ] Application accessible via browser
- [ ] Login functionality works
- [ ] Database connections working
- [ ] Email notifications sending (check queue logs)
- [ ] All menu items accessible
- [ ] No errors in application logs
- [ ] Queue workers running
- [ ] Scheduler running (cron jobs)

---

## 9. Automated Deployment Flow

**Setelah setup selesai**, workflow otomatis Anda akan seperti ini:

```
1. You push code to GitHub
   ↓
2. GitHub Actions triggers automatically
   ↓
3. Workflow connects to NUC via SSH
   ↓
4. Pull latest code from GitHub
   ↓
5. Build Docker containers
   ↓
6. Run database migrations
   ↓
7. Clear and optimize caches
   ↓
8. Restart services
   ↓
9. Health check
   ↓
10. ✅ Deployment complete!
```

---

## 10. Advanced Configuration

### Setup SSL Certificate (HTTPS)

```bash
# Install certbot di NUC
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### Setup Nginx Reverse Proxy

Jika Anda ingin akses dari domain custom, setup nginx reverse proxy di NUC:

```nginx
# /etc/nginx/sites-available/warehouse
server {
    listen 80;
    server_name your-domain.com;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Monitoring with Uptime Kuma (Optional)

```bash
# Install Uptime Kuma di NUC
docker run -d --name uptime-kuma \
  -p 3001:3001 \
  -v uptime-kuma:/app/data \
  louislam/uptime-kuma:1

# Access: http://NUC_IP:3001
```

---

## 11. Troubleshooting GitHub Actions

### Check Workflow Logs

1. GitHub → Actions → Click failed workflow
2. Expand each step untuk lihat error details

### Test SSH Connection Locally

```bash
# Test SSH dengan same credentials
ssh -i ~/.ssh/nuc_deploy_key user@NUC_IP_ADDRESS

# If connection fails, check:
# 1. SSH key permissions: chmod 600 ~/.ssh/nuc_deploy_key
# 2. NUC firewall: sudo ufw status
# 3. SSH service: sudo systemctl status ssh
```

### Debug Docker Issues

```bash
# Check Docker daemon
sudo systemctl status docker

# Check Docker logs
sudo journalctl -u docker.service -n 50

# Test Docker compose
docker compose config
```

---

## 12. Support & Contact

**Jika ada masalah:**

1. Check GitHub Actions logs
2. Check Docker container logs: `docker compose logs`
3. Check application logs: `storage/logs/laravel.log`
4. Check database logs

**Documentation:**
- Docker: https://docs.docker.com/
- GitHub Actions: https://docs.github.com/en/actions
- Laravel Deployment: https://laravel.com/docs/deployment

---

## ✅ Setup Selesai!

Setelah mengikuti guide ini, Anda sudah punya:

- ✅ Automated deployment ke NUC
- ✅ Docker containerized application
- ✅ CI/CD pipeline dengan GitHub Actions
- ✅ Health monitoring
- ✅ Rollback strategy

**Next time Anda push code ke GitHub, deployment akan otomatis!** 🎉
