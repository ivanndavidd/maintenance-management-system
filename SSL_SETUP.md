# SSL Certificate Setup for warehouse-maintenance.gdn-app.com

## Certificate Placement

Place your SSL certificate files in the `ssl/` directory:

```
ssl/
├── warehouse-maintenance.gdn-app.com.crt
└── warehouse-maintenance.gdn-app.com.key
```

## File Permissions

Set proper permissions for security:

```bash
chmod 644 ssl/warehouse-maintenance.gdn-app.com.crt
chmod 600 ssl/warehouse-maintenance.gdn-app.com.key
```

## Cloudflare DNS Setup

1. **Add A Record** in Cloudflare DNS:
   - **Type**: A
   - **Name**: warehouse-maintenance
   - **Content**: `<VM_PUBLIC_IP>`
   - **TTL**: Auto
   - **Proxy Status**: DNS only (gray cloud) - important for SSL passthrough

2. **VM Public IP**: Get your VM's external IP:
   ```bash
   gcloud compute instances describe vm-gdn-prod-ase1a-opswhmaintenance-01 \
     --zone=asia-southeast1-a \
     --project=prj-gdn-prod-pir-ops \
     --format="get(networkInterfaces[0].accessConfigs[0].natIP)"
   ```

## Certificate Sources

### Option 1: Cloudflare Origin Certificate (Recommended)
1. Go to **SSL/TLS > Origin Server** in Cloudflare dashboard
2. Click **Create Certificate**
3. Choose **Let Cloudflare generate a private key and CSR**
4. Set **Hostnames**: `warehouse-maintenance.gdn-app.com`
5. Set **Certificate Validity**: 15 years
6. Click **Next** and download both files
7. Rename files to match the expected names above

### Option 2: Let's Encrypt Certificate
```bash
# Install certbot on the VM
sudo apt update && sudo apt install certbot

# Generate certificate
sudo certbot certonly --standalone \
  -d warehouse-maintenance.gdn-app.com \
  --email your-email@domain.com \
  --agree-tos \
  --no-eff-email

# Copy certificates to ssl directory
sudo cp /etc/letsencrypt/live/warehouse-maintenance.gdn-app.com/fullchain.pem ssl/warehouse-maintenance.gdn-app.com.crt
sudo cp /etc/letsencrypt/live/warehouse-maintenance.gdn-app.com/privkey.pem ssl/warehouse-maintenance.gdn-app.com.key
```

### Option 3: Existing Certificate
If you already have certificate files, simply copy them to the `ssl/` directory with the correct names.

## Verification

After deployment, verify SSL is working:

```bash
# Check certificate
openssl s_client -connect warehouse-maintenance.gdn-app.com:443 -servername warehouse-maintenance.gdn-app.com

# Check HTTP to HTTPS redirect
curl -I http://warehouse-maintenance.gdn-app.com

# Check HTTPS response
curl -I https://warehouse-maintenance.gdn-app.com
```

## Firewall Rules

Ensure your GCP firewall allows traffic on ports 80 and 443:

```bash
# Create firewall rule for HTTP/HTTPS
gcloud compute firewall-rules create allow-warehouse-maintenance-web \
  --project=prj-gdn-prod-pir-ops \
  --allow tcp:80,tcp:443 \
  --source-ranges 0.0.0.0/0 \
  --description "Allow HTTP/HTTPS for warehouse maintenance app"
```

## Troubleshooting

### Certificate Issues
- Ensure certificate files exist in `ssl/` directory
- Check file permissions (644 for .crt, 600 for .key)
- Verify certificate matches the domain name

### DNS Issues
- Use `nslookup warehouse-maintenance.gdn-app.com` to verify DNS resolution
- Ensure Cloudflare proxy is disabled (gray cloud, not orange)

### Container Issues
- Check nginx-proxy logs: `docker compose logs nginx-proxy`
- Verify nginx configuration: `docker compose exec nginx-proxy nginx -t`
- Restart services: `docker compose restart nginx-proxy`

## Security Notes

- Never commit certificate files to git
- Use strong SSL ciphers (configured in nginx.conf)
- Enable HSTS headers (already configured)
- Regular certificate renewal (Let's Encrypt expires every 90 days)