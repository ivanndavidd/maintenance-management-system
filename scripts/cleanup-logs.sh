#!/bin/bash

# Log cleanup script for warehouse maintenance application
# Keeps total log size under 200MB across all containers

LOG_DIR="/var/lib/docker/containers"
MAX_TOTAL_SIZE="200M"
MAX_TOTAL_SIZE_BYTES=209715200  # 200MB in bytes

echo "=== Docker Log Cleanup Started $(date) ==="

# Function to convert size to bytes
size_to_bytes() {
    local size=$1
    case $size in
        *K) echo $(( ${size%K} * 1024 )) ;;
        *M) echo $(( ${size%M} * 1024 * 1024 )) ;;
        *G) echo $(( ${size%G} * 1024 * 1024 * 1024 )) ;;
        *) echo $size ;;
    esac
}

# Get current total log size
get_log_size() {
    docker system df --format "table {{.Type}}\t{{.Size}}" | grep "Local Volumes" | awk '{print $3}' || echo "0M"
}

# Cleanup old container logs
cleanup_container_logs() {
    echo "Cleaning up container logs..."
    
    # Get all warehouse-related containers
    containers=$(docker ps -a --filter "name=warehouse-" --format "{{.ID}}")
    
    for container in $containers; do
        if [ -f "/var/lib/docker/containers/$container/$container-json.log" ]; then
            log_size=$(stat -c%s "/var/lib/docker/containers/$container/$container-json.log" 2>/dev/null || echo 0)
            if [ "$log_size" -gt 52428800 ]; then  # 50MB
                echo "Truncating log for container $container ($(($log_size/1024/1024))MB)"
                # Keep only last 1000 lines
                tail -1000 "/var/lib/docker/containers/$container/$container-json.log" > "/tmp/$container.log"
                cat "/tmp/$container.log" > "/var/lib/docker/containers/$container/$container-json.log"
                rm -f "/tmp/$container.log"
            fi
        fi
    done
}

# Cleanup nginx logs
cleanup_nginx_logs() {
    echo "Cleaning up nginx logs..."
    
    # Find nginx log volume
    nginx_log_volume=$(docker volume ls --filter "name=nginx-logs" --format "{{.Name}}")
    
    if [ ! -z "$nginx_log_volume" ]; then
        # Get volume mount point
        volume_path=$(docker volume inspect $nginx_log_volume --format "{{.Mountpoint}}" 2>/dev/null)
        
        if [ -d "$volume_path" ]; then
            # Clean access logs older than 7 days
            find "$volume_path" -name "access.log*" -type f -mtime +7 -delete 2>/dev/null
            
            # Truncate current access log if too large
            access_log="$volume_path/access.log"
            if [ -f "$access_log" ]; then
                log_size=$(stat -c%s "$access_log" 2>/dev/null || echo 0)
                if [ "$log_size" -gt 52428800 ]; then  # 50MB
                    echo "Truncating nginx access log ($(($log_size/1024/1024))MB)"
                    tail -5000 "$access_log" > "$access_log.tmp"
                    mv "$access_log.tmp" "$access_log"
                fi
            fi
            
            # Truncate error log if too large
            error_log="$volume_path/error.log"
            if [ -f "$error_log" ]; then
                log_size=$(stat -c%s "$error_log" 2>/dev/null || echo 0)
                if [ "$log_size" -gt 20971520 ]; then  # 20MB
                    echo "Truncating nginx error log ($(($log_size/1024/1024))MB)"
                    tail -2000 "$error_log" > "$error_log.tmp"
                    mv "$error_log.tmp" "$error_log"
                fi
            fi
        fi
    fi
}

# Cleanup Laravel logs
cleanup_laravel_logs() {
    echo "Cleaning up Laravel logs..."
    
    # Find app log volume
    app_log_volume=$(docker volume ls --filter "name=app-logs" --format "{{.Name}}")
    
    if [ ! -z "$app_log_volume" ]; then
        volume_path=$(docker volume inspect $app_log_volume --format "{{.Mountpoint}}" 2>/dev/null)
        
        if [ -d "$volume_path" ]; then
            # Remove logs older than 30 days
            find "$volume_path" -name "*.log" -type f -mtime +30 -delete 2>/dev/null
            
            # Truncate current day log if too large
            current_log="$volume_path/laravel.log"
            if [ -f "$current_log" ]; then
                log_size=$(stat -c%s "$current_log" 2>/dev/null || echo 0)
                if [ "$log_size" -gt 52428800 ]; then  # 50MB
                    echo "Truncating Laravel log ($(($log_size/1024/1024))MB)"
                    tail -3000 "$current_log" > "$current_log.tmp"
                    mv "$current_log.tmp" "$current_log"
                fi
            fi
        fi
    fi
}

# Main cleanup process
main() {
    echo "Current log status:"
    docker system df
    
    cleanup_container_logs
    cleanup_nginx_logs
    cleanup_laravel_logs
    
    # Prune unused Docker data
    echo "Pruning unused Docker data..."
    docker system prune -f --volumes >/dev/null 2>&1
    
    echo "Post-cleanup log status:"
    docker system df
    
    echo "=== Docker Log Cleanup Completed $(date) ==="
}

# Run main function
main