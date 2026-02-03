#!/bin/bash

###############################################################################
# Warehouse Maintenance - NUC Initial Setup Script
# Run this script on your NUC to setup the environment
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_step() { echo -e "${BLUE}==>${NC} $1"; }
print_success() { echo -e "${GREEN}✓${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }
print_warning() { echo -e "${YELLOW}!${NC} $1"; }

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║   Warehouse Maintenance - NUC Setup                      ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    print_error "Please don't run this script as root"
    exit 1
fi

###############################################################################
# Step 1: Update System
###############################################################################
print_step "Updating system packages..."
sudo apt update && sudo apt upgrade -y
print_success "System updated"

###############################################################################
# Step 2: Install Docker
###############################################################################
print_step "Installing Docker..."

if ! command -v docker &> /dev/null; then
    # Install Docker
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    rm get-docker.sh

    # Add user to docker group
    sudo usermod -aG docker $USER

    print_success "Docker installed"
else
    print_success "Docker already installed"
fi

###############################################################################
# Step 3: Install Docker Compose
###############################################################################
print_step "Installing Docker Compose..."

if ! docker compose version &> /dev/null; then
    sudo apt install docker-compose-plugin -y
    print_success "Docker Compose installed"
else
    print_success "Docker Compose already installed"
fi

###############################################################################
# Step 4: Install Git
###############################################################################
print_step "Installing Git..."

if ! command -v git &> /dev/null; then
    sudo apt install git -y
    print_success "Git installed"
else
    print_success "Git already installed"
fi

###############################################################################
# Step 5: Install other utilities
###############################################################################
print_step "Installing utilities..."
sudo apt install -y curl wget vim nano htop net-tools ufw
print_success "Utilities installed"

###############################################################################
# Step 6: Configure Firewall
###############################################################################
print_step "Configuring firewall..."

# Allow SSH, HTTP, HTTPS
sudo ufw allow 22/tcp comment 'SSH'
sudo ufw allow 80/tcp comment 'HTTP'
sudo ufw allow 443/tcp comment 'HTTPS'

# Enable firewall (with confirmation)
echo "y" | sudo ufw enable || true

print_success "Firewall configured"

###############################################################################
# Step 7: Setup Deployment Directory
###############################################################################
print_step "Setting up deployment directory..."

DEPLOY_PATH="/var/www/warehouse-maintenance"

# Create directory
sudo mkdir -p "$DEPLOY_PATH"
sudo chown -R $USER:$USER "$DEPLOY_PATH"

print_success "Deployment directory created: $DEPLOY_PATH"

###############################################################################
# Step 8: Generate SSH Key for GitHub
###############################################################################
print_step "Generating SSH key for GitHub..."

SSH_KEY_PATH="$HOME/.ssh/id_ed25519"

if [ ! -f "$SSH_KEY_PATH" ]; then
    ssh-keygen -t ed25519 -C "nuc-deployment" -f "$SSH_KEY_PATH" -N ""
    print_success "SSH key generated"

    echo ""
    print_warning "Add this public key to your GitHub account:"
    echo "-----------------------------------------------------"
    cat "$SSH_KEY_PATH.pub"
    echo "-----------------------------------------------------"
    echo ""
    echo "Instructions:"
    echo "1. Copy the key above"
    echo "2. Go to GitHub.com → Settings → SSH and GPG keys"
    echo "3. Click 'New SSH key'"
    echo "4. Paste the key and save"
    echo ""
    read -p "Press Enter after adding the key to GitHub..."
else
    print_success "SSH key already exists"
fi

###############################################################################
# Step 9: Clone Repository
###############################################################################
print_step "Cloning repository..."

cd "$DEPLOY_PATH"

# Prompt for GitHub repository URL
read -p "Enter your GitHub repository URL (e.g., git@github.com:username/repo.git): " REPO_URL

if [ -z "$(ls -A $DEPLOY_PATH)" ]; then
    git clone "$REPO_URL" .
    print_success "Repository cloned"
else
    print_warning "Directory not empty, skipping clone"
fi

###############################################################################
# Step 10: Setup Environment File
###############################################################################
print_step "Setting up environment file..."

if [ ! -f .env ]; then
    cp .env.docker .env

    # Generate APP_KEY
    print_step "Generating APP_KEY..."

    # We need composer and PHP for this, so we'll use Docker
    docker run --rm -v $(pwd):/app -w /app composer:2 composer install --no-dev --optimize-autoloader

    APP_KEY=$(docker run --rm -v $(pwd):/app -w /app php:8.3-cli php artisan key:generate --show)

    # Update .env
    sed -i "s/APP_KEY=/APP_KEY=$APP_KEY/" .env

    print_success ".env file created with APP_KEY"

    # Prompt for database password
    echo ""
    read -sp "Enter database password: " DB_PASSWORD
    echo ""
    sed -i "s/DB_PASSWORD=secret/DB_PASSWORD=$DB_PASSWORD/" .env

    # Prompt for database root password
    read -sp "Enter database root password: " DB_ROOT_PASSWORD
    echo ""
    echo "DB_ROOT_PASSWORD=$DB_ROOT_PASSWORD" >> .env

    print_success "Database passwords configured"
else
    print_success ".env file already exists"
fi

###############################################################################
# Step 11: Build and Start Containers
###############################################################################
print_step "Building and starting Docker containers..."

docker compose build
docker compose up -d

print_success "Containers started"

###############################################################################
# Step 12: Wait for Database
###############################################################################
print_step "Waiting for database to be ready..."
sleep 20
print_success "Database should be ready"

###############################################################################
# Step 13: Run Migrations
###############################################################################
print_step "Running database migrations..."

docker compose exec -T app php artisan migrate --force --seed

print_success "Migrations completed"

###############################################################################
# Step 14: Optimize Application
###############################################################################
print_step "Optimizing application..."

docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache
docker compose exec -T app php artisan optimize

print_success "Application optimized"

###############################################################################
# Final Steps
###############################################################################
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅ NUC Setup Completed!                               ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""

# Get NUC IP address
NUC_IP=$(hostname -I | awk '{print $1}')

echo "📋 Next Steps:"
echo ""
echo "1. Access your application:"
echo "   http://$NUC_IP"
echo ""
echo "2. Default login credentials:"
echo "   Check your database seeders for default admin account"
echo ""
echo "3. Setup GitHub Actions Secrets:"
echo "   - NUC_SSH_KEY: Add your deployment SSH private key"
echo "   - NUC_HOST: $NUC_IP"
echo "   - NUC_USER: $USER"
echo "   - DEPLOY_PATH: $DEPLOY_PATH"
echo ""
echo "4. Container status:"
docker compose ps
echo ""
echo "5. To deploy updates in the future:"
echo "   Just push to GitHub main branch, or run: ./deploy.sh"
echo ""

print_warning "IMPORTANT: You may need to log out and back in for Docker group changes to take effect"
echo ""
