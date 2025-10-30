# 🚀 DEPLOYMENT CHECKLIST - WEBSHOP

## ✅ Trạng thái: 90% SẴN SÀNG

---

## 1️⃣ CRITICAL TASKS (BẮT BUỘC)

### A. Environment Configuration

- [ ] **Tạo file .env cho production**
  ```bash
  cp .env.example .env.production
  ```

- [ ] **Cập nhật APP_ENV và APP_DEBUG**
  ```bash
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://yourdomain.com
  ```

- [ ] **Generate APP_KEY mới**
  ```bash
  php artisan key:generate
  ```

### B. Database Configuration

- [ ] **Setup production database**
  - Chọn hosting provider (MySQL/PostgreSQL)
  - Tạo database và user
  - Cập nhật DB_* trong .env
  ```bash
  DB_CONNECTION=mysql
  DB_HOST=your-db-host.com
  DB_PORT=3306
  DB_DATABASE=your_database_name
  DB_USERNAME=your_db_user
  DB_PASSWORD=strong_password_here
  ```

- [ ] **Run migrations**
  ```bash
  php artisan migrate --force
  ```

- [ ] **Seed initial data** (nếu cần)
  ```bash
  php artisan db:seed --force
  ```

### C. SSL/HTTPS Setup

- [ ] **Cài đặt SSL certificate**
  - Sử dụng Let's Encrypt (miễn phí): https://letsencrypt.org/
  - Hoặc mua SSL từ provider
  
- [ ] **Cấu hình web server**
  - Force HTTPS redirect
  - Set HSTS headers

- [ ] **Cập nhật .env**
  ```bash
  SESSION_SECURE_COOKIE=true
  SESSION_SAME_SITE=strict
  ```

### D. Google OAuth

- [ ] **Cập nhật Google OAuth credentials**
  - Vào: https://console.cloud.google.com/
  - Update Authorized redirect URIs:
    ```
    https://yourdomain.com/auth/google/callback
    ```
  
- [ ] **Cập nhật .env**
  ```bash
  GOOGLE_CLIENT_ID=your_production_client_id
  GOOGLE_CLIENT_SECRET=your_production_secret
  GOOGLE_REDIRECT_URL=https://yourdomain.com/auth/google/callback
  ```

### E. VNPay Configuration

- [ ] **Đăng ký VNPay production account**
  - Website: https://vnpay.vn/
  - Cần GPKD (Giấy phép kinh doanh)
  
- [ ] **Cập nhật VNPay credentials**
  ```bash
  VNPAY_TMN_CODE=your_production_tmn_code
  VNPAY_HASH_SECRET=your_production_hash_secret
  VNPAY_URL=https://pay.vnpay.vn/vpcpay.html
  VNPAY_RETURN_URL=https://yourdomain.com/payment/vnpay-return
  VNPAY_IPN_URL=https://yourdomain.com/payment/vnpay-ipn
  ```

### F. Email Configuration

- [ ] **Setup email service**
  - Option 1: Gmail SMTP
  - Option 2: SendGrid
  - Option 3: Mailgun
  - Option 4: AWS SES

- [ ] **Cập nhật .env**
  ```bash
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.gmail.com
  MAIL_PORT=587
  MAIL_USERNAME=your-email@gmail.com
  MAIL_PASSWORD=your-app-password
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS="noreply@yourdomain.com"
  MAIL_FROM_NAME="YourShop"
  ```

---

## 2️⃣ IMPORTANT TASKS (QUAN TRỌNG)

### A. CORS Configuration

- [ ] **Update allowed origins**
  ```bash
  CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://www.yourdomain.com
  ```

### B. Session Storage

- [ ] **Tạo sessions table**
  ```bash
  php artisan session:table
  php artisan migrate
  ```

- [ ] **Setup session cleanup**
  ```bash
  # Add to crontab
  0 2 * * * cd /path/to/project && php artisan session:gc
  ```

### C. Cache Configuration

- [ ] **Chọn cache driver**
  ```bash
  # Option 1: Redis (Recommended)
  CACHE_STORE=redis
  REDIS_HOST=your-redis-host
  REDIS_PASSWORD=your-redis-password
  
  # Option 2: Memcached
  CACHE_STORE=memcached
  
  # Option 3: Database (OK for small sites)
  CACHE_STORE=database
  ```

- [ ] **Cache configuration**
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

### D. Queue Configuration

- [ ] **Setup queue worker**
  ```bash
  QUEUE_CONNECTION=redis  # or database
  ```

- [ ] **Run queue worker**
  ```bash
  # Sử dụng supervisor để keep worker alive
  php artisan queue:work --tries=3
  ```

### E. Storage & File Uploads

- [ ] **Link storage**
  ```bash
  php artisan storage:link
  ```

- [ ] **Set permissions**
  ```bash
  chmod -R 775 storage bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache
  ```

- [ ] **Consider cloud storage** (Optional)
  ```bash
  # AWS S3
  FILESYSTEM_DISK=s3
  AWS_ACCESS_KEY_ID=
  AWS_SECRET_ACCESS_KEY=
  AWS_DEFAULT_REGION=
  AWS_BUCKET=
  ```

---

## 3️⃣ OPTIMIZATION TASKS (TỐI ƯU HÓA)

### A. Performance

- [ ] **Enable OPcache**
  ```ini
  # php.ini
  opcache.enable=1
  opcache.memory_consumption=256
  opcache.max_accelerated_files=20000
  ```

- [ ] **Optimize Composer**
  ```bash
  composer install --optimize-autoloader --no-dev
  ```

- [ ] **Asset compilation**
  ```bash
  npm run build
  ```

### B. Security Hardening

- [ ] **Remove development packages**
  ```bash
  composer install --no-dev
  ```

- [ ] **Disable directory listing**
  ```apache
  # .htaccess
  Options -Indexes
  ```

- [ ] **Hide Laravel version**
  - Remove X-Powered-By header
  - Custom error pages

### C. Monitoring & Logging

- [ ] **Setup error tracking**
  - Sentry: https://sentry.io/
  - Bugsnag: https://www.bugsnag.com/
  - Rollbar: https://rollbar.com/

- [ ] **Setup log rotation**
  ```bash
  # /etc/logrotate.d/laravel
  /path/to/storage/logs/*.log {
      daily
      rotate 14
      compress
      delaycompress
      notifempty
      create 0640 www-data www-data
  }
  ```

- [ ] **Setup monitoring**
  - New Relic
  - DataDog
  - Laravel Telescope (dev only)

---

## 4️⃣ SERVER SETUP

### A. Chọn Hosting Provider

**Recommended Options:**

1. **VPS Hosting (Full Control)**
   - DigitalOcean: $6/month (1GB RAM)
   - Linode: $5/month
   - Vultr: $6/month
   - AWS Lightsail: $5/month

2. **Laravel-Optimized Hosting**
   - Laravel Forge + DigitalOcean: $12/month + $6/month
   - Ploi.io: $10/month
   - CloudWays: ~$11/month

3. **Shared Hosting (Budget)**
   - Hostinger: ~$3/month
   - Namecheap: ~$3/month
   - ⚠️ Cần support PHP 8.2+, Composer, SSH

### B. Server Requirements

- ✅ PHP >= 8.2
- ✅ Composer
- ✅ MySQL 8.0+ hoặc PostgreSQL 13+
- ✅ Nginx hoặc Apache
- ✅ SSL Certificate
- ✅ SSH Access
- ✅ Cron Jobs support

### C. Web Server Configuration

**Nginx (Recommended):**
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/webshop/public;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Apache:**
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/webshop/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem

    <Directory /var/www/webshop/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## 5️⃣ DEPLOYMENT STEPS

### Step-by-Step Deployment

```bash
# 1. Clone repository
cd /var/www
git clone https://github.com/vinhhoang04cp/webshop.git
cd webshop

# 2. Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 3. Setup environment
cp .env.example .env
nano .env  # Edit configuration

# 4. Generate key
php artisan key:generate

# 5. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 6. Run migrations
php artisan migrate --force

# 7. Link storage
php artisan storage:link

# 8. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Setup cron
crontab -e
# Add: * * * * * cd /var/www/webshop && php artisan schedule:run >> /dev/null 2>&1

# 10. Setup queue worker (if using queues)
# Create supervisor config
```

---

## 6️⃣ POST-DEPLOYMENT CHECKS

### A. Functionality Tests

- [ ] **Homepage loads**
  ```bash
  curl https://yourdomain.com
  ```

- [ ] **Login/Register works**
- [ ] **Google OAuth works**
- [ ] **Product listing works**
- [ ] **Cart functionality works**
- [ ] **Checkout process works**
- [ ] **VNPay payment works**
- [ ] **Email sending works**
- [ ] **Admin panel accessible**

### B. Security Tests

- [ ] **HTTPS redirect works**
  ```bash
  curl -I http://yourdomain.com
  # Should return 301 redirect to https://
  ```

- [ ] **Security headers present**
  ```bash
  curl -I https://yourdomain.com
  # Check for X-Frame-Options, CSP, HSTS
  ```

- [ ] **Run security scan**
  - https://observatory.mozilla.org/
  - https://securityheaders.com/

- [ ] **Test rate limiting**
- [ ] **Test session security**

### C. Performance Tests

- [ ] **Page load speed**
  - Target: < 3 seconds
  - Tool: https://pagespeed.web.dev/

- [ ] **Database query optimization**
  ```bash
  php artisan telescope:install  # Only for debugging
  ```

- [ ] **Check response times**
  ```bash
  ab -n 100 -c 10 https://yourdomain.com/
  ```

---

## 7️⃣ BACKUP & RECOVERY

### A. Database Backup

```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u user -p database > /backups/db_$DATE.sql
# Keep only last 7 days
find /backups -name "db_*.sql" -mtime +7 -delete
```

### B. File Backup

```bash
# Backup uploads
tar -czf /backups/storage_$DATE.tar.gz /var/www/webshop/storage/app/public
```

### C. Automated Backup

```bash
# Add to crontab
0 2 * * * /path/to/backup-script.sh
```

---

## 8️⃣ MONITORING & MAINTENANCE

### A. Setup Monitoring

- [ ] **Uptime monitoring**
  - UptimeRobot: https://uptimerobot.com/ (Free)
  - Pingdom
  - StatusCake

- [ ] **Error tracking**
  - Sentry (Free tier available)
  - Bugsnag
  - Rollbar

- [ ] **Log monitoring**
  ```bash
  tail -f storage/logs/laravel.log
  tail -f storage/logs/security.log
  ```

### B. Regular Tasks

- [ ] **Daily:**
  - Check error logs
  - Monitor uptime
  - Review security logs

- [ ] **Weekly:**
  - Update dependencies
  - Review performance
  - Check backups

- [ ] **Monthly:**
  - Security audit
  - Performance optimization
  - Update documentation

---

## 9️⃣ BUDGET ESTIMATE

### Minimum Setup (Budget)
- **Hosting:** $5-6/month (VPS)
- **Domain:** $10-15/year
- **SSL:** Free (Let's Encrypt)
- **Email:** Free (Gmail SMTP với app password)
- **Total:** ~$6/month + $15/year

### Recommended Setup
- **Hosting:** Laravel Forge + DigitalOcean: $18/month
- **Domain:** $15/year
- **SSL:** Free
- **Email:** SendGrid: $15/month (or free tier)
- **CDN:** Cloudflare: Free
- **Monitoring:** Sentry: Free tier
- **Total:** ~$33/month + $15/year

### Production Setup
- **Hosting:** $50-100/month
- **Database:** AWS RDS: $15-30/month
- **Email:** SendGrid Pro: $90/month
- **CDN:** Cloudflare Pro: $20/month
- **Storage:** AWS S3: $5-10/month
- **Monitoring:** New Relic: $99/month
- **Total:** ~$279-349/month

---

## 🎯 RECOMMENDED DEPLOYMENT PATH

### For Learning/Testing (Cheap):
1. **Hostinger Shared Hosting** ($3/month)
2. Free domain (from Hostinger)
3. Free SSL (Let's Encrypt)
4. Gmail SMTP for email

### For Small Business (Best Value):
1. **DigitalOcean Droplet** ($6/month)
2. **Laravel Forge** ($12/month) - Auto deployment
3. Domain from Namecheap ($10/year)
4. Free SSL (Let's Encrypt)
5. SendGrid free tier (100 emails/day)

### For Growing Business (Recommended):
1. **Laravel Forge + DigitalOcean** ($18/month)
2. **Redis cache** (included in Forge)
3. **Queue workers** (automated)
4. **Auto-deployments from Git**
5. Professional monitoring

---

## ✅ FINAL CHECKLIST

Before going live:

- [ ] All environment variables configured
- [ ] Database migrated successfully
- [ ] SSL certificate installed
- [ ] HTTPS redirect working
- [ ] Google OAuth configured
- [ ] VNPay credentials updated (production)
- [ ] Email sending tested
- [ ] All tests passing
- [ ] Security headers verified
- [ ] Backups configured
- [ ] Monitoring setup
- [ ] Domain pointing to server
- [ ] Performance optimized
- [ ] Error tracking enabled

---

## 📞 SUPPORT RESOURCES

- Laravel Deployment: https://laravel.com/docs/deployment
- Laravel Forge: https://forge.laravel.com/
- DigitalOcean Tutorials: https://www.digitalocean.com/community/tags/laravel
- Server Management: https://serverpilot.io/
- Let's Encrypt: https://letsencrypt.org/

---

**Last Updated:** 30/10/2025  
**Status:** READY FOR DEPLOYMENT (90%)  
**Estimated Time to Deploy:** 2-4 hours (first time)
