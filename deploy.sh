#!/bin/bash

###############################################################################
# Warehouse Maintenance - Deployment Script for NUC
# This script should be run on the NUC server
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DEPLOY_PATH="/var/www/warehouse-maintenance"
BACKUP_DIR="$DEPLOY_PATH/backups"
BACKUP_RETENTION=7  # Keep last 7 backups

###############################################################################
# Functions
###############################################################################

print_step() {
    echo -e "${BLUE}==>${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}!${NC} $1"
}

check_docker() {
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed!"
        exit 1
    fi

    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        print_error "Docker Compose is not installed!"
        exit 1
    fi

    print_success "Docker and Docker Compose are installed"
}

backup_database() {
    print_step "Backing up database..."

    # Create backup directory
    mkdir -p "$BACKUP_DIR"

    # Generate backup filename
    BACKUP_FILE="$BACKUP_DIR/db-backup-$(date +%Y%m%d-%H%M%S).sql"

    # Backup database
    if docker compose exec -T db mysqldump -uroot -p"${DB_ROOT_PASSWORD}" warehouse_maintenance > "$BACKUP_FILE"; then
        print_success "Database backed up to $BACKUP_FILE"

        # Cleanup old backups
        ls -t "$BACKUP_DIR"/db-backup-*.sql | tail -n +$((BACKUP_RETENTION + 1)) | xargs -r rm
        print_success "Old backups cleaned up (keeping last $BACKUP_RETENTION)"
    else
        print_warning "Database backup failed (continuing deployment)"
    fi
}

pull_code() {
    print_step "Pulling latest code from GitHub..."

    if git pull origin main; then
        print_success "Code pulled successfully"
    else
        print_error "Failed to pull code from GitHub"
        exit 1
    fi
}

build_containers() {
    print_step "Building Docker containers..."

    if docker compose build --no-cache; then
        print_success "Containers built successfully"
    else
        print_error "Failed to build containers"
        exit 1
    fi
}

start_containers() {
    print_step "Starting containers..."

    # Stop running containers
    docker compose down

    # Start containers
    if docker compose up -d; then
        print_success "Containers started successfully"
    else
        print_error "Failed to start containers"
        exit 1
    fi
}

wait_for_containers() {
    print_step "Waiting for containers to be healthy..."

    local max_attempts=30
    local attempt=0

    while [ $attempt -lt $max_attempts ]; do
        if docker compose ps | grep -q "healthy"; then
            print_success "Containers are healthy"
            return 0
        fi

        attempt=$((attempt + 1))
        echo -n "."
        sleep 2
    done

    print_warning "Containers may not be fully healthy yet"
    return 0
}

run_migrations() {
    print_step "Running database migrations..."

    if docker compose exec -T app php artisan migrate --force; then
        print_success "Migrations completed"
    else
        print_error "Migrations failed"
        exit 1
    fi
}

optimize_app() {
    print_step "Optimizing application..."

    # Clear caches
    docker compose exec -T app php artisan config:clear
    docker compose exec -T app php artisan route:clear
    docker compose exec -T app php artisan view:clear

    # Cache configs
    docker compose exec -T app php artisan config:cache
    docker compose exec -T app php artisan route:cache
    docker compose exec -T app php artisan view:cache

    # Optimize
    docker compose exec -T app php artisan optimize

    print_success "Application optimized"
}

restart_services() {
    print_step "Restarting queue and scheduler..."

    docker compose restart queue scheduler

    print_success "Services restarted"
}

health_check() {
    print_step "Running health check..."

    local max_attempts=10
    local attempt=0

    while [ $attempt -lt $max_attempts ]; do
        response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health || echo "000")

        if [ "$response" = "200" ]; then
            print_success "Application is healthy (HTTP $response)"
            return 0
        fi

        attempt=$((attempt + 1))
        sleep 3
    done

    print_error "Health check failed (HTTP $response)"
    return 1
}

show_status() {
    print_step "Container status:"
    docker compose ps
}

###############################################################################
# Main Deployment Flow
###############################################################################

main() {
    echo ""
    echo "╔══════════════════════════════════════════════════════════╗"
    echo "║   Warehouse Maintenance - Deployment Script             ║"
    echo "╚══════════════════════════════════════════════════════════╝"
    echo ""

    # Navigate to deployment directory
    cd "$DEPLOY_PATH" || exit 1

    # Load environment variables
    if [ -f .env ]; then
        export $(cat .env | grep -v '^#' | xargs)
    fi

    # Check prerequisites
    check_docker

    # Backup database
    backup_database

    # Pull latest code
    pull_code

    # Build and start containers
    build_containers
    start_containers
    wait_for_containers

    # Run migrations and optimize
    run_migrations
    optimize_app
    restart_services

    # Health check
    if health_check; then
        show_status

        echo ""
        echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
        echo -e "${GREEN}║   ✅ Deployment completed successfully!                 ║${NC}"
        echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
        echo ""
    else
        print_error "Deployment completed but health check failed"
        show_status
        exit 1
    fi
}

# Run main function
main "$@"
