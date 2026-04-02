# Hướng Dẫn Deploy Laravel Application lên VPS

## Tổng Quan

Dự án này là một Laravel 12 application với:
- **Backend**: PHP 8.2+, Laravel 12, Filament 3.x
- **Database**: PostgreSQL 15+
- **Cache**: Redis 7+
- **Search**: PostgreSQL Full-Text Search
- **Frontend**: React 18 + Inertia.js + Vite
- **Web Server**: Nginx

---

## 1. Yêu Cầu VPS

### 1.1 Tài Nguyên Tối Thiểu (Production)

- **CPU**: 2-4 vCPU
- **RAM**: 4-8 GB
- **Disk**: 50-100 GB SSD
- **OS**: Ubuntu 22.04 LTS hoặc Ubuntu 24.04 LTS (khuyến nghị)

### 1.2 Tài Nguyên Khuyến Nghị (Production)

- **CPU**: 4+ vCPU
- **RAM**: 8+ GB
- **Disk**: 100+ GB SSD
- **Bandwidth**: Unlimited hoặc ít nhất 1TB/tháng

---

## 2. Software Stack Cần Cài Đặt

### 2.1 Hệ Thống Cơ Bản

```bash
# Cập nhật hệ thống
sudo apt update && sudo apt upgrade -y

# Cài đặt các package cơ bản
sudo apt install -y curl wget git unzip software-properties-common
```

### 2.2 PHP 8.2+ với Extensions

```bash
# Thêm repository PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Cài đặt PHP 8.2 và các extensions cần thiết
sudo apt install -y \
    php8.2 \
    php8.2-fpm \
    php8.2-cli \
    php8.2-common \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-redis \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-opcache \
    php8.2-readline

# Kiểm tra version
php -v
```

### 2.3 PostgreSQL 15+

```bash
# Cài đặt PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Kiểm tra version
psql --version

# Khởi động và enable auto-start
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

### 2.4 Redis

```bash
# Cài đặt Redis
sudo apt install -y redis-server

# Cấu hình Redis (tùy chọn: set password)
sudo nano /etc/redis/redis.conf
# Tìm và uncomment: requirepass your_secure_password

# Khởi động và enable
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Test Redis
redis-cli ping
```

### 2.5 Node.js & npm (Để build assets)

```bash
# Cài đặt Node.js 20.x LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Kiểm tra version
node -v
npm -v

# Cài đặt PNPM (tùy chọn, nhanh hơn npm)
sudo npm install -g pnpm
```

### 2.6 Composer

```bash
# Tải Composer
cd ~
curl -sS https://getcomposer.org/installer | php

# Di chuyển vào PATH
sudo mv composer.phar /usr/local/bin/composer

# Cấp quyền execute
sudo chmod +x /usr/local/bin/composer

# Kiểm tra
composer --version
```

### 2.7 Nginx

```bash
# Cài đặt Nginx
sudo apt install -y nginx

# Khởi động và enable
sudo systemctl start nginx
sudo systemctl enable nginx

# Kiểm tra status
sudo systemctl status nginx
```

### 2.8 Supervisor (Để chạy Laravel Queue)

```bash
# Cài đặt Supervisor
sudo apt install -y supervisor

# Khởi động
sudo systemctl start supervisor
sudo systemctl enable supervisor
```

---

## 3. Cấu Hình Database

### 3.1 Tạo Database và User

```bash
# Chuyển sang user postgres
sudo -u postgres psql

# Tạo database và user
CREATE DATABASE your_database_name;
CREATE USER your_db_user WITH ENCRYPTED PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE your_database_name TO your_db_user;
\q
```

### 3.2 Cấu hình PostgreSQL (Tùy chọn - cho production)

```bash
# Chỉnh sửa config
sudo nano /etc/postgresql/15/main/postgresql.conf

# Tìm và uncomment/modify:
# listen_addresses = 'localhost'

# Cấu hình authentication
sudo nano /etc/postgresql/15/main/pg_hba.conf

# Restart PostgreSQL
sudo systemctl restart postgresql
```

---

## 4. Deploy Application

### 4.1 Clone Repository

```bash
# Tạo thư mục cho ứng dụng
sudo mkdir -p /var/www
cd /var/www

# Clone repository (hoặc upload code)
sudo git clone your_repository_url web-vsen
cd web-vsen

# Hoặc nếu upload code, giải nén vào /var/www/web-vsen
```

### 4.2 Cấp Quyền

```bash
# Đặt owner
sudo chown -R www-data:www-data /var/www/web-vsen

# Cấp quyền cho storage và bootstrap/cache
sudo chmod -R 775 /var/www/web-vsen/storage
sudo chmod -R 775 /var/www/web-vsen/bootstrap/cache
```

### 4.3 Cài Đặt Dependencies

```bash
cd /var/www/web-vsen

# Cài đặt PHP dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev

# Cài đặt Node dependencies
sudo -u www-data npm ci

# Build assets
sudo -u www-data npm run build
```

### 4.4 Cấu Hình Environment

```bash
# Copy .env.example sang .env
sudo -u www-data cp .env.example .env

# Tạo APP_KEY
sudo -u www-data php artisan key:generate

# Chỉnh sửa .env
sudo -u www-data nano .env
```

**Các biến môi trường quan trọng trong `.env`:**

```env
APP_NAME="VSEN Medical Equipment"
APP_ENV=production
APP_KEY=base64:... (đã generate)
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis

CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Mail configuration (SMTP, Mailgun, etc.)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4.5 Chạy Migrations

```bash
# Chạy migrations
sudo -u www-data php artisan migrate --force

# Nếu có seeders (Lưu ý: bắt buộc dùng --force trong production)
sudo -u www-data php artisan db:seed --force
```

### 4.6 Optimize Laravel

```bash
# Cache config
sudo -u www-data php artisan config:cache

# Cache routes
sudo -u www-data php artisan route:cache

# Cache views
sudo -u www-data php artisan view:cache

# Cache events (nếu có)
sudo -u www-data php artisan event:cache
```

---

## 5. Cấu Hình Nginx

### 5.1 Tạo Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/web-vsen
```

**Nội dung file:**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/web-vsen/public;

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
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 5.2 Enable Site

```bash
# Tạo symbolic link
sudo ln -s /etc/nginx/sites-available/web-vsen /etc/nginx/sites-enabled/

# Xóa default site (tùy chọn)
sudo rm /etc/nginx/sites-enabled/default

# Test cấu hình
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

### 5.3 SSL với Let's Encrypt (Khuyến nghị)

```bash
# Cài đặt Certbot
sudo apt install -y certbot python3-certbot-nginx

# Cài đặt SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

---

## 6. Cấu Hình Supervisor (Laravel Queue)

### 6.1 Tạo Supervisor Configuration

```bash
sudo nano /etc/supervisor/conf.d/web-vsen-worker.conf
```

**Nội dung:**

```ini
[program:web-vsen-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/web-vsen/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/web-vsen/storage/logs/worker.log
stopwaitsecs=3600
```

### 6.2 Khởi Động Supervisor

```bash
# Reload config
sudo supervisorctl reread
sudo supervisorctl update

# Khởi động worker
sudo supervisorctl start web-vsen-worker:*

# Kiểm tra status
sudo supervisorctl status
```

---

## 7. Cấu Hình PHP-FPM

### 7.1 Tối Ưu PHP-FPM

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

**Các thông số quan trọng:**

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### 7.2 Tối Ưu PHP.ini

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

**Các thông số quan trọng:**

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
max_input_time = 300
```

### 7.3 Restart PHP-FPM

```bash
sudo systemctl restart php8.2-fpm
```

---

## 8. Bảo Mật

### 8.1 Firewall (UFW)

```bash
# Cài đặt UFW
sudo apt install -y ufw

# Cho phép SSH, HTTP, HTTPS
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Kiểm tra status
sudo ufw status
```

### 8.2 Fail2Ban (Bảo vệ chống brute force)

```bash
# Cài đặt Fail2Ban
sudo apt install -y fail2ban

# Khởi động
sudo systemctl start fail2ban
sudo systemctl enable fail2ban
```

### 8.3 File Permissions

```bash
# Đảm bảo quyền file đúng
cd /var/www/web-vsen
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;
sudo chmod -R 775 storage bootstrap/cache
```

---

## 9. Monitoring & Logs

### 9.1 Log Files

- **Laravel logs**: `/var/www/web-vsen/storage/logs/laravel.log`
- **Nginx access**: `/var/log/nginx/access.log`
- **Nginx error**: `/var/log/nginx/error.log`
- **PHP-FPM**: `/var/log/php8.2-fpm.log`
- **Queue worker**: `/var/www/web-vsen/storage/logs/worker.log`

### 9.2 Kiểm Tra Services

```bash
# Kiểm tra status các services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status postgresql
sudo systemctl status redis-server
sudo systemctl status supervisor
```

---

## 10. Deployment Checklist

### Trước Khi Deploy

- [ ] VPS đã cài đặt đủ software stack
- [ ] Database đã được tạo
- [ ] Code đã được clone/upload
- [ ] File `.env` đã được cấu hình
- [ ] `APP_KEY` đã được generate

### Sau Khi Deploy

- [ ] Dependencies đã được cài đặt (`composer install`, `npm ci`)
- [ ] Assets đã được build (`npm run build`)
- [ ] Migrations đã chạy (`php artisan migrate`)
- [ ] Laravel đã được optimize (`config:cache`, `route:cache`, `view:cache`)
- [ ] Nginx configuration đã được test và restart
- [ ] PHP-FPM đã restart
- [ ] Queue worker đã được khởi động với Supervisor
- [ ] SSL certificate đã được cài đặt (nếu có domain)
- [ ] Firewall đã được cấu hình
- [ ] Test truy cập website
- [ ] Test queue processing

---

## 11. Các Lệnh Hữu Ích

### 11.1 Update Code (Zero-Downtime Deployment)

```bash
cd /var/www/web-vsen

# Pull code mới
sudo -u www-data git pull origin main

# Cài dependencies mới (nếu có)
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm ci
sudo -u www-data npm run build

# Chạy migrations
sudo -u www-data php artisan migrate --force

# Clear và rebuild cache
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan view:cache

# Restart queue workers
sudo supervisorctl restart web-vsen-worker:*

# Restart PHP-FPM (tùy chọn)
sudo systemctl reload php8.2-fpm
```

### 11.2 Xem Logs

```bash
# Laravel logs
tail -f /var/www/web-vsen/storage/logs/laravel.log

# Queue logs
tail -f /var/www/web-vsen/storage/logs/worker.log

# Nginx logs
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log
```

### 11.3 Queue Management

```bash
# Xem queue status
sudo supervisorctl status web-vsen-worker:*

# Restart workers
sudo supervisorctl restart web-vsen-worker:*

# Stop workers
sudo supervisorctl stop web-vsen-worker:*

# Start workers
sudo supervisorctl start web-vsen-worker:*
```

---

## 12. Troubleshooting

### 12.1 502 Bad Gateway

```bash
# Kiểm tra PHP-FPM status
sudo systemctl status php8.2-fpm

# Kiểm tra socket path trong Nginx config
ls -la /var/run/php/php8.2-fpm.sock

# Kiểm tra PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

### 12.2 Permission Denied

```bash
# Kiểm tra và fix permissions
cd /var/www/web-vsen
sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache
```

### 12.3 Database Connection Error

```bash
# Kiểm tra PostgreSQL đang chạy
sudo systemctl status postgresql

# Test connection
psql -U your_db_user -d your_database_name -h 127.0.0.1

# Kiểm tra .env file
cat /var/www/web-vsen/.env | grep DB_
```

### 12.4 Queue Not Processing

```bash
# Kiểm tra Redis
redis-cli ping

# Kiểm tra Supervisor
sudo supervisorctl status

# Xem worker logs
tail -f /var/www/web-vsen/storage/logs/worker.log
```

### 12.5 Lỗi "Class 'Redis' not found"

Lỗi này xảy ra khi PHP Redis extension chưa được cài đặt hoặc enable.

```bash
# Cài đặt PHP Redis extension (Ubuntu/Debian)
sudo apt install php8.2-redis

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Kiểm tra
php -m | grep redis
```

---

## 13. Backup Strategy

### 13.1 Database Backup

```bash
# Tạo script backup
sudo nano /usr/local/bin/backup-database.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/postgresql"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="your_database_name"
DB_USER="your_db_user"

mkdir -p $BACKUP_DIR
pg_dump -U $DB_USER $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Xóa backup cũ hơn 7 ngày
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete
```

```bash
# Cấp quyền execute
sudo chmod +x /usr/local/bin/backup-database.sh

# Thêm vào crontab (chạy mỗi ngày lúc 2 giờ sáng)
sudo crontab -e
# Thêm dòng:
0 2 * * * /usr/local/bin/backup-database.sh
```

### 13.2 Application Files Backup

```bash
# Backup storage và .env
sudo tar -czf /var/backups/web-vsen/files_$(date +%Y%m%d_%H%M%S).tar.gz \
    /var/www/web-vsen/storage \
    /var/www/web-vsen/.env
```

---

## 14. Performance Tuning

### 14.1 OPcache Configuration

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

### 14.2 Redis Configuration

```bash
sudo nano /etc/redis/redis.conf
```

```conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

---

## Kết Luận

Sau khi hoàn thành tất cả các bước trên, ứng dụng Laravel của bạn sẽ được deploy thành công lên VPS và sẵn sàng phục vụ người dùng.

**Lưu ý quan trọng:**
- Luôn backup trước khi deploy code mới
- Monitor logs thường xuyên
- Cập nhật hệ thống và dependencies định kỳ
- Sử dụng SSL/HTTPS cho production
- Cấu hình firewall và security properly
- **Lưu ý đặc biệt cho Filament Admin Panel**: Để truy cập được admin panel trên production, `App\Models\User` phải implement `FilamentUser` interface và method `canAccessPanel` phải trả về `true` (thường check dựa trên `role`).
- **Lưu ý seeding**: Luôn sử dụng flag `--force` khi chạy `php artisan db:seed` trên môi trường production.

