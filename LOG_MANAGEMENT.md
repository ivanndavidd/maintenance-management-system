# Log Management Setup

## Overview
This setup ensures that logs never exceed **200MB total** to prevent disk space issues.

## Log Size Limits by Container

| Container | Max Size | Max Files | Total Limit |
|-----------|----------|-----------|-------------|
| nginx-proxy | 50MB | 3 files | ~150MB |
| app (Laravel) | 50MB | 3 files | ~150MB |
| queue | 30MB | 2 files | ~60MB |
| scheduler | 20MB | 2 files | ~40MB |
| db (MySQL) | 20MB | 2 files | ~40MB |
| redis | 10MB | 2 files | ~20MB |

**Total Docker Logs: ~460MB maximum** (with automatic rotation)

## Additional Log Management

### Nginx Logs (in volumes)
- **Access Log**: Truncated at 50MB, keeps 5000 recent entries
- **Error Log**: Truncated at 20MB, keeps 2000 recent entries
- **Retention**: 7 days for old rotated logs

### Laravel Application Logs
- **Application Log**: Truncated at 50MB, keeps 3000 recent entries
- **Retention**: 30 days for old logs
- **Location**: `/var/www/html/storage/logs/`

## Automated Cleanup

### Cron Jobs (runs every 6 hours)
```bash
# Every 6 hours - cleanup logs
0 */6 * * * /opt/ops-warehouse-maintenance/scripts/cleanup-logs.sh

# Weekly - Docker system prune
0 2 * * 0 docker system prune -af --volumes

# Daily - disk usage monitoring
0 8 * * * df -h / | awk 'NR==2 {if(substr($5,1,length($5)-1) > 80) print "WARNING: Disk usage is " $5}'
```

### Manual Cleanup
```bash
# Run manual cleanup
./scripts/cleanup-logs.sh

# Check current log sizes
docker system df

# View container log sizes
docker ps --format "table {{.Names}}\t{{.Size}}"
```

## Monitoring

### Check Log Status
```bash
# Overall Docker usage
docker system df

# Individual container logs
docker logs --tail 50 warehouse-nginx-proxy
docker logs --tail 50 warehouse-app
docker logs --tail 50 warehouse-queue

# Volume usage
docker volume ls
docker system df -v
```

### Disk Usage Monitoring
```bash
# Check disk space
df -h /

# Check Docker directory size
du -sh /var/lib/docker

# Check specific log directories
du -sh /var/lib/docker/containers/*/
```

## Emergency Log Cleanup

If logs grow unexpectedly large:

```bash
# Immediate cleanup - truncate all container logs
for container in $(docker ps -q); do
    docker exec $container sh -c "truncate -s 0 /proc/1/fd/1 /proc/1/fd/2" 2>/dev/null || true
done

# Nuclear option - remove all stopped containers and unused data
docker system prune -af --volumes

# Restart logging services
docker compose restart nginx-proxy app
```

## Log Locations

### Docker Container Logs
- **Location**: `/var/lib/docker/containers/[container-id]/[container-id]-json.log`
- **Managed by**: Docker logging driver with rotation

### Volume-based Logs
- **Nginx Logs**: `nginx-logs` volume → `/var/log/nginx/`
- **Laravel Logs**: `app-logs` volume → `/var/www/html/storage/logs/`

### System Logs
- **Cleanup Log**: `/var/log/cleanup.log`
- **Disk Usage Log**: `/var/log/disk-usage.log`
- **Docker Prune Log**: `/var/log/docker-prune.log`

## Configuration Files

### Docker Compose Logging
```yaml
logging:
  driver: "json-file"
  options:
    max-size: "50m"
    max-file: "3"
```

### Docker Daemon (Global)
```json
{
  "log-driver": "json-file",
  "log-opts": {
    "max-size": "50m",
    "max-file": "3"
  }
}
```

## Best Practices

1. **Monitor Regularly**: Check `docker system df` weekly
2. **Alert Setup**: Configure alerts for >80% disk usage
3. **Backup Important Logs**: Archive critical logs before rotation
4. **Test Cleanup**: Run cleanup script manually during low traffic
5. **Performance Impact**: Log rotation happens during low-load periods

## Troubleshooting

### High Log Growth
- Check for error loops in application logs
- Reduce nginx access log verbosity for static files
- Increase cleanup frequency if needed

### Disk Space Issues
- Run immediate cleanup: `./scripts/cleanup-logs.sh`
- Check for large files: `du -sh /var/lib/docker/*`
- Consider reducing max-size limits temporarily

### Failed Cleanup
- Check cron service: `systemctl status cron`
- Verify script permissions: `ls -la scripts/cleanup-logs.sh`
- Run manually: `./scripts/cleanup-logs.sh`