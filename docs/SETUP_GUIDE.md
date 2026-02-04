# Aplikasi Kasir Rumah Sakit - Setup Guide

## Database Configuration

Aplikasi ini sudah dikonfigurasi untuk menggunakan PostgreSQL. Pastikan database sudah dibuat sebelum menjalankan migration.

### PostgreSQL (Default)
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hospitalpay
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### MySQL (Alternative)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hospitalpay
DB_USERNAME=root
DB_PASSWORD=your_password
```

## Email Configuration

Untuk fitur cron job laporan harian, konfigurasi email diperlukan.

### Gmail SMTP
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="Hospital Cashier System"
```

**Note untuk Gmail:**
- Aktifkan "2-Step Verification" di Google Account
- Generate "App Password" di Google Account Security
- Gunakan App Password sebagai MAIL_PASSWORD

### Mailtrap (Development)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@hospital.com
MAIL_FROM_NAME="Hospital Cashier System"
```

## Application Setup

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Generate Key
```bash
php artisan key:generate
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Run Seeders
```bash
php artisan db:seed
```

Data yang akan di-seed:
- **Users:**
  - Kasir: kasir@hospital.com / password
  - Marketing: marketing@hospital.com / password
  
- **Insurances:**
  - Asuransi Reliance (dengan voucher 5%, max Rp 35.000, Jan 2026)
  - Asuransi Allianz (dengan voucher 1%, no limit)
  - Asuransi Prudential (dengan voucher fixed Rp 15.000)
  
- **Medical Services:**
  - 10 layanan medis dengan harga bervariasi

### 5. Build Assets
```bash
npm run build
# atau untuk development
npm run dev
```

### 6. Run Application
```bash
php artisan serve
```

Aplikasi akan berjalan di: http://localhost:8000

## Cron Job Setup

### Windows (Task Scheduler)
1. Buka Task Scheduler
2. Create Basic Task
3. Set Trigger: Daily at 01:00
4. Action: Start a Program
5. Program: `php`
6. Arguments: `artisan schedule:run`
7. Start in: `D:\R\hospitalpay`

### Linux/Mac (Crontab)
```bash
crontab -e
```

Tambahkan:
```
* * * * * cd /path/to/hospitalpay && php artisan schedule:run >> /dev/null 2>&1
```

### Development Testing
Untuk testing tanpa menunggu cron:
```bash
# Test scheduler
php artisan schedule:work

# Test manual command
php artisan report:send-daily-transactions
```

## Testing the Application

### Login as Kasir
1. Go to http://localhost:8000/login
2. Email: kasir@hospital.com
3. Password: password
4. Test creating transactions

### Login as Marketing
1. Go to http://localhost:8000/login
2. Email: marketing@hospital.com
3. Password: password
4. Test creating vouchers
5. View dashboard analytics

## Troubleshooting

### Error: "could not find driver"
Install PHP PostgreSQL extension:
```bash
# Windows
Enable extension in php.ini: extension=pdo_pgsql, extension=pgsql

# Ubuntu/Debian
sudo apt-get install php-pgsql

# Mac
brew install php-pgsql
```

### Error: Email not sending
- Check MAIL configuration in .env
- Check firewall/antivirus blocking port 587
- Test email with: `php artisan tinker` then `Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });`

### Error: PDF not generating
```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### Error: Assets not loading
```bash
npm run build
php artisan storage:link
```

## Production Deployment

### 1. Optimize Application
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Set Environment
```env
APP_ENV=production
APP_DEBUG=false
```

### 3. Queue Configuration (Optional but Recommended)
```env
QUEUE_CONNECTION=database
```

Run queue worker:
```bash
php artisan queue:work
```

### 4. Setup Supervisor (Linux)
Create file: `/etc/supervisor/conf.d/hospitalpay-worker.conf`
```ini
[program:hospitalpay-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/hospitalpay/artisan queue:work
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/hospitalpay/storage/logs/worker.log
```

## Security Checklist

- [ ] Change default user passwords
- [ ] Set strong APP_KEY
- [ ] Enable HTTPS in production
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Disable directory listing
- [ ] Keep dependencies updated
- [ ] Regular database backups

## Support

Untuk pertanyaan teknis atau bug report, silakan hubungi tim development.
