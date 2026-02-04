# Hospital Payment System

Sistem pembayaran rumah sakit berbasis web menggunakan Laravel 11 dengan fitur manajemen asuransi, transaksi pembayaran, dashboard marketing, dan laporan otomatis via Telegram.

## 📁 Struktur Project

```
hospitalpay/
├── app/              # Source code aplikasi (Controllers, Models, Services)
├── config/           # File konfigurasi Laravel
├── database/         # Migrations, seeders, factories
├── docs/            # 📚 Dokumentasi lengkap (API, Setup, Troubleshooting)
├── public/          # Assets publik (CSS, JS, images)
├── resources/       # Views, CSS, JS source files
├── routes/          # Route definitions
├── scripts/         # 🔧 Testing & utility scripts
├── storage/         # Logs, uploads, cache
├── tests/           # Unit & feature tests
└── vendor/          # Dependencies
```

**Folder Khusus:**
- **[docs/](docs/)** - Semua dokumentasi tambahan (API, Telegram, Scheduler, Insurance, dll)
- **[scripts/](scripts/)** - Script testing, checker, dan scheduler utilities

## 📋 Deskripsi Sistem

Hospital Payment System adalah aplikasi web terintegrasi untuk mengelola transaksi pembayaran di rumah sakit dengan dua role utama:

- **Kasir**: Menangani transaksi pembayaran pasien dengan pemilihan layanan medis dan penerapan diskon asuransi
- **Marketing**: Mengelola data asuransi, pengaturan diskon, dan monitoring dashboard

### 🏗️ Arsitektur Sistem

```
┌──────────────────────────────────────────────────────────────┐
│                   Hospital Payment System                     │
├──────────────────────────────────────────────────────────────┤
│                                                                │
│  ┌──────────────┐        ┌──────────────┐                    │
│  │    Kasir     │        │   Marketing  │                    │
│  │  Interface   │        │  Dashboard   │                    │
│  └──────┬───────┘        └──────┬───────┘                    │
│         │                       │                             │
│         └───────────┬───────────┘                             │
│                     │                                          │
│         ┌───────────▼───────────┐                             │
│         │  Laravel Application  │                             │
│         │   (Business Logic)    │                             │
│         └───────────┬───────────┘                             │
│                     │                                          │
│         ┌───────────┼───────────┐                             │
│         │           │           │                             │
│    ┌────▼────┐ ┌───▼────┐ ┌───▼────────┐                    │
│    │ Database│ │External│ │  Telegram  │                    │
│    │PostgreSQL│ │  API   │ │    Bot     │                    │
│    └─────────┘ └────────┘ └────────────┘                    │
│                                                                │
│  ┌────────────────────────────────────────────────────────┐  │
│  │      Task Scheduler (Windows Cron Job)                 │  │
│  │   Daily Report @ 01:00 WIB → Telegram (Excel + Pesan) │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

### 🔄 Flow Transaksi Pembayaran

1. **Kasir** membuka halaman transaksi baru
2. Input nama pasien
3. **Pilih asuransi** (WAJIB) - Setiap transaksi harus menggunakan asuransi
4. Pilih layanan medis (bisa multiple layanan, masing-masing 1 unit)
5. Sistem otomatis menghitung diskon berdasarkan:
   - Persentase diskon asuransi
   - Maksimal nominal diskon (jika ada)
   - Coverage limit asuransi
6. Sistem menghitung total pembayaran setelah diskon
7. Simpan transaksi → generate kode transaksi otomatis
8. Cetak struk PDF

**Catatan Penting**: 
- Asuransi adalah **WAJIB** untuk setiap transaksi
- Setiap layanan medis = 1 unit (tidak ada quantity)
- Jika perlu layanan yang sama 2x, tambahkan 2 kali
- Diskon dihitung proporsional jika melebihi maksimal nominal

### 📊 Flow Laporan Harian

1. **Task Scheduler** berjalan otomatis setiap hari pukul **01:00 WIB**
2. Sistem mengumpulkan semua transaksi hari kemarin
3. Generate laporan Excel dengan detail transaksi
4. Kirim laporan ke **Telegram** dengan:
   - File Excel (attachment)
   - Pesan singkat informatif

## ✨ Fitur Utama

### 1. **Role Management**
- **Kasir**: Mengelola transaksi pembayaran pasien, input data, apply diskon asuransi
- **Marketing**: Mengelola data asuransi, pengaturan diskon, dan melihat dashboard analitik

### 2. **Insurance Discount System** 🆕
Marketing dapat mengelola pengaturan diskon asuransi:
- **Persentase Diskon**: 0-100% diskon dari total tagihan
- **Maksimal Nominal Diskon**: Batas maksimal diskon dalam rupiah (opsional)
  - Contoh: Allianz diskon 30% dengan maksimal Rp 300.000
  - Jika total Rp 2.000.000 → diskon 30% = Rp 600.000 → **dibatasi jadi Rp 300.000**
- **Coverage Limit**: Batas tanggungan tahunan asuransi
- **Terms & Conditions**: Syarat dan ketentuan penggunaan

**Fitur Unggulan:**
- ✅ Edit diskon langsung dari dashboard marketing
- ✅ Perhitungan diskon proporsional otomatis
- ✅ Real-time preview perhitungan di form transaksi
- ✅ Konsistensi diskon antara frontend dan backend

### 3. **Dashboard Marketing**
Menampilkan informasi:
- Statistik kunjungan per asuransi
- Total pembayaran per asuransi
- Transaksi hari ini dan revenue
- Total diskon yang diberikan
- Daftar transaksi terbaru
- Chart pemakaian per bulan

### 4. **Transaksi Pembayaran (Kasir)**
- Input nama pasien
- Pilih asuransi (WAJIB)
- Pilih multiple layanan medis (+ Tambah Layanan)
- Setiap layanan = 1 unit (no quantity field)
- Auto-calculate diskon dengan max_discount_amount
- Edit dan hapus transaksi (hanya yang belum dibayar)
- Proses pembayaran
- Cetak bukti pembayaran PDF
- Generate kode transaksi otomatis (format: TRXYYYYMMDDxxxxx)

### 5. **Telegram Notifications** 🔔
- Notifikasi laporan harian transaksi otomatis
- Format laporan yang rapi dan informatif
- Test notification dari dashboard
- Kirim laporan via Telegram Bot

### 6. **Cron Job Laporan Harian** ⏰
- Otomatis kirim laporan transaksi harian via Telegram
- Excel file dengan detail transaksi
- Pesan singkat informatif
- Scheduled otomatis setiap hari pukul **01:00 WIB**
- Menggunakan Windows Task Scheduler
- PC harus dalam keadaan aktif (tidak sleep/hibernate)

### 7. **API Integration** 🔌
- External API: RS Delta Surya untuk data asuransi dan harga layanan
- Local API endpoints untuk mobile/external apps
- Dokumentasi lengkap: [API_ENDPOINTS.md](docs/API_ENDPOINTS.md)
- Testing endpoints tersedia

## 🛠️ Tech Stack

- **Framework**: Laravel 11
- **Language**: PHP >= 8.2
- **Frontend**: Blade Templates + Tailwind CSS (Laravel Breeze)
- **Database**: PostgreSQL / MySQL
- **PDF Generation**: barryvdh/laravel-dompdf
- **Excel Export**: maatwebsite/excel
- **Telegram Bot**: irazasyed/telegram-bot-sdk
- **Authentication**: Laravel Breeze
- **Task Scheduling**: Windows Task Scheduler / Linux Cron

## 📦 Instalasi

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & NPM
- PostgreSQL atau MySQL
- Telegram Bot Token (untuk notifikasi laporan)

### Langkah Instalasi

#### 1. **Clone Repository**
```bash
git clone <repository-url>
cd hospitalpay
```

#### 2. **Install Dependencies**
```bash
composer install
npm install
```

#### 3. **Setup Environment**
```bash
cp .env.example .env
php artisan key:generate
```

#### 4. **Konfigurasi Database**
Edit file `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hospitalpay
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

#### 5. **Konfigurasi External API**
Edit file `.env` untuk integrasi RS Delta Surya:
```env
EXTERNAL_API_URL=https://rsapi.deltasurya.co.id
EXTERNAL_API_TOKEN=your_api_token
```

#### 6. **Konfigurasi Telegram** 
Edit file `.env`:
```env
# Telegram Bot (untuk notifikasi laporan)
TELEGRAM_BOT_TOKEN=your_bot_token_from_botfather
TELEGRAM_CHAT_ID=your_chat_id
```

**Quick Setup Telegram:**
1. Cari **@BotFather** di Telegram → `/newbot` → copy token
2. Cari **@userinfobot** di Telegram → copy chat ID
3. Paste ke `.env`
4. Test: Login → Dashboard → klik **"🔔 Test Telegram"**

📖 Panduan lengkap: [TELEGRAM_QUICKSTART.md](docs/TELEGRAM_QUICKSTART.md)

#### 7. **Run Migration & Seeder**
```bash
php artisan migrate
php artisan db:seed
```

Seeder akan membuat:
- 2 user (kasir & marketing) - password: `password`
- 3 asuransi dengan voucher masing-masing
- 10 layanan medis

#### 8. **Build Assets**
```bash
npm run build
# atau untuk development:
npm run dev
```

#### 9. **Run Application**
```bash
php artisan serve
```

Aplikasi akan berjalan di: http://localhost:8000

## 👥 User Credentials

Setelah seeder dijalankan, gunakan kredensial berikut:

### Kasir
- Email: `kasir@hospital.com`
- Password: `password`
- Akses: Transaksi pembayaran, input data pasien, apply diskon asuransi

### Marketing
- Email: `marketing@hospital.com`
- Password: `password`
- Akses: Dashboard analytics, kelola asuransi & diskon, statistik penggunaan

## ⚙️ Setup Scheduler (Laporan Harian)

Sistem ini memiliki fitur laporan harian otomatis yang dikirim setiap pukul **01:00 WIB** via Telegram dengan file Excel dan pesan singkat.

### Windows (Recommended untuk Development)

#### Setup Task Scheduler (One-Time Setup)

Jalankan PowerShell sebagai **Administrator** dan eksekusi:

```powershell
# Otomatis membuka PowerShell admin dan setup scheduler
Start-Process powershell -Verb RunAs -ArgumentList "-NoExit", "-Command", "cd '$PWD'; .\scripts\setup-scheduler.ps1"
```

Script akan:
1. ✅ Membuat scheduled task di Windows Task Scheduler
2. ✅ Set jadwal harian pukul 01:00 WIB
3. ✅ Berjalan otomatis di background setiap hari
4. ✅ Tidak perlu menjalankan ulang setup

**Catatan Penting:**
- Setup hanya perlu dilakukan **SEKALI**
- Task akan berjalan otomatis setiap hari tanpa perlu IDE/command prompt terbuka
- PC harus dalam keadaan **aktif** (tidak sleep/hibernate) saat scheduler berjalan
- Tidak perlu menjalankan `php artisan serve` untuk scheduler
- Laravel menggunakan port internal untuk scheduler, bukan web server

#### Verifikasi Setup

```bash
# Cek jadwal yang sudah terdaftar
php artisan schedule:list

# Output:
# 0 1 * * * report:send-daily-transactions ... Next Due: X hours from now
```

#### Testing Manual

Jika ingin test kirim laporan sekarang:

```bash
# Jalankan scheduler sekarang (test)
php artisan schedule:run

# Atau langsung jalankan command report
php artisan report:send-daily-transactions
```

#### Alternative: Manual Run (Tanpa Task Scheduler)

Jika tidak ingin setup Task Scheduler, bisa jalankan manual setiap hari:

```powershell
# Jalankan sekali
.\scripts\run-scheduler.ps1

# Atau untuk loop terus-menerus (biarkan terminal terbuka)
.\scripts\scheduler-loop.ps1
```

### Linux/Mac (Production)

Tambahkan ke crontab:

```bash
# Edit crontab
crontab -e

# Tambahkan baris ini:
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Atau untuk development:
```bash
php artisan schedule:work
```

### Troubleshooting Scheduler

1. **Task tidak berjalan otomatis**
   - Pastikan PC tidak dalam mode sleep/hibernate saat jadwal berjalan
   - Cek Windows Task Scheduler: Task Scheduler Library → HospitalPay
   - Pastikan status task "Ready" bukan "Disabled"

2. **Laporan tidak terkirim ke Telegram**
   - Test manual: `php artisan report:send-daily-transactions`
   - Cek konfigurasi Telegram di `.env` (TELEGRAM_BOT_TOKEN dan TELEGRAM_CHAT_ID)
   - Cek koneksi internet
   - Lihat log error: `storage/logs/laravel.log`

3. **Permission denied saat setup**
   - Jalankan PowerShell sebagai Administrator
   - Atau gunakan command bypass: `Start-Process powershell -Verb RunAs...`

## 📊 Testing Laporan Transaksi

### Testing via UI (Recommended)

Tersedia button testing di dashboard:
1. Login sebagai **Kasir** atau **Marketing**
2. Di bagian atas dashboard, terdapat panel **"Testing Laporan Transaksi"**
3. Pilih tanggal yang ingin dilaporkan (default: kemarin)
4. Klik **"📥 Download Excel"** untuk download langsung

### Testing via Command Line

```bash
php artisan report:send-daily-transactions
```

### Testing Routes

- **Download Excel**: `GET /reports/download-daily` (dengan parameter `date`)
- Route dapat diakses oleh **Kasir** dan **Marketing**

## 📱 Cara Penggunaan

### Untuk Kasir

#### 1. Login
- Buka http://localhost:8000
- Email: `kasir@hospital.com`
- Password: `password`

#### 2. Membuat Transaksi Baru
1. Klik **"Transaksi"** di sidebar
2. Klik tombol **"+ Transaksi Baru"**
3. Input data pasien:
   - Nama Pasien (required)
4. Pilih asuransi (REQUIRED):
   - Pilih dari dropdown asuransi
   - Lihat info diskon yang akan diterapkan
5. Pilih layanan medis:
   - Klik **"+ Tambah Layanan"**
   - Pilih layanan dari dropdown
   - Setiap layanan otomatis quantity = 1
   - Untuk layanan yang sama, tambahkan multiple kali
   - Sistem otomatis kalkulasi subtotal dan diskon

#### 3. Review & Simpan
1. Review total pembayaran setelah diskon
   - Subtotal Layanan
   - Diskon Asuransi (proporsional jika ada max_discount_amount)
   - Total Pembayaran
2. Klik **"Proses Transaksi"**
3. Sistem generate kode transaksi otomatis (format: TRX20260204xxxxx)
4. Redirect ke halaman detail transaksi

#### 4. Cetak Struk
- Di halaman detail transaksi, klik **"Cetak Struk"**
- PDF struk akan otomatis di-download
- Struk berisi detail lengkap transaksi dan diskon

### Untuk Marketing

#### 1. Login
- Email: `marketing@hospital.com`
- Password: `password`

#### 2. Dashboard Analytics
- Lihat statistik kunjungan per asuransi
- Monitor total pembayaran dan diskon
- Chart pemakaian per bulan
- Filter berdasarkan periode tanggal

#### 3. Kelola Asuransi
1. Klik **"Data Asuransi"** di sidebar
2. Pilih asuransi yang ingin diedit
3. Klik tombol **"Edit Diskon"**
4. Atur pengaturan diskon:
   - **Nama Asuransi** (read-only, tidak bisa diubah)
   - **Persentase Diskon** (0-100%)
   - **Maksimal Nominal Diskon** (opsional, dalam rupiah)
     - Contoh: 300000 untuk maksimal diskon Rp 300.000
     - Kosongkan jika tidak ada batas
   - **Ketentuan** (syarat & ketentuan penggunaan)
   - **Limit Tanggungan** (read-only, hubungi admin untuk ubah)
5. Lihat contoh perhitungan real-time
6. Klik **"💾 Simpan Perubahan"**

#### 4. View Detail Asuransi
- Klik **"Lihat Detail"** pada card asuransi
- Lihat pengaturan lengkap:
  - Persentase diskon
  - Maksimal nominal diskon
  - Limit tanggungan
  - Contoh perhitungan untuk berbagai skenario
  - List voucher terkait (jika ada)

#### 5. Testing Laporan
- Di dashboard, gunakan panel **"Testing Laporan Transaksi"**
- Pilih tanggal dan klik **"📥 Download Excel"**
- Laporan akan di-download langsung

## 🗄️ Struktur Database

### Tables
- `users` - User dengan role kasir/marketing
- `insurances` - Master data asuransi dengan pengaturan diskon
- `vouchers` - Voucher tambahan per asuransi (legacy, untuk backward compatibility)
- `medical_services` - Layanan/tindakan medis
- `transactions` - Header transaksi pembayaran
- `transaction_details` - Detail item transaksi

### Relationships
- `User` → has role (kasir/marketing)
- `Insurance` → has many `Vouchers` (legacy)
- `Transaction` → belongs to `User`, `Insurance`
- `Transaction` → has many `TransactionDetails`
- `TransactionDetail` → belongs to `Transaction`, `MedicalService`

### New Fields (Insurance Table)
- `discount_percentage` - Persentase diskon (0-100)
- `max_discount_amount` - Maksimal nominal diskon dalam rupiah (nullable)
- `coverage_limit` - Batas tanggungan tahunan (nullable)
- `terms` - Syarat dan ketentuan (nullable)

## 🎯 Fitur Unggulan

1. **Insurance Required**: Setiap transaksi WAJIB menggunakan asuransi
2. **No Quantity Field**: Setiap layanan medis = 1 unit, tambahkan multiple kali jika perlu
3. **Max Discount Amount**: Pembatasan nominal diskon maksimal
4. **Proportional Discount**: Diskon per item dihitung proporsional jika melebihi max_discount_amount
5. **Real-time Calculation**: Perhitungan diskon real-time di form transaksi
6. **Transaction Code**: Generate kode transaksi otomatis (format: TRXYYYYMMDDxxxxx)
7. **PDF Receipt**: Generate struk pembayaran profesional
8. **Excel Report**: Export laporan dengan formatting
9. **Telegram Integration**: Laporan otomatis via Telegram
10. **Marketing Control**: Marketing bisa edit pengaturan diskon asuransi

## 📚 Dokumentasi Tambahan

- [API_ENDPOINTS.md](docs/API_ENDPOINTS.md) - Dokumentasi API endpoints
- [API_INTEGRATION.md](docs/API_INTEGRATION.md) - Panduan integrasi External API
- [TELEGRAM_QUICKSTART.md](docs/TELEGRAM_QUICKSTART.md) - Quick start Telegram Bot
- [TELEGRAM_SETUP.md](docs/TELEGRAM_SETUP.md) - Setup lengkap Telegram
- [TELEGRAM_TROUBLESHOOTING.md](docs/TELEGRAM_TROUBLESHOOTING.md) - Troubleshooting Telegram
- [SCHEDULER_SETUP.md](docs/SCHEDULER_SETUP.md) - Setup scheduler lengkap
- [SCHEDULER_QUICKSTART.md](docs/SCHEDULER_QUICKSTART.md) - Quick start scheduler
- [INSURANCE_DISCOUNT_GUIDE.md](docs/INSURANCE_DISCOUNT_GUIDE.md) - Panduan insurance discount
- [ERROR_HANDLING.md](docs/ERROR_HANDLING.md) - Error handling guide

## 🐛 Troubleshooting

### Masalah Umum

#### 1. Button tidak berfungsi / perlu klik 2x
- **Penyebab**: Cache browser atau asset tidak ter-rebuild
- **Solusi**: 
  - Clear cache: `php artisan optimize:clear`
  - Rebuild assets: `npm run build`
  - Hard refresh browser: `Ctrl + Shift + R` atau `Ctrl + F5`

#### 2. Diskon tidak konsisten antara detail dan total
- **Penyebab**: max_discount_amount membatasi diskon tapi per-item belum disesuaikan
- **Solusi**: Sudah diperbaiki dengan proportional discount calculation

#### 3. Task Scheduler tidak berjalan
- **Penyebab**: PC dalam mode sleep/hibernate
- **Solusi**: Pastikan PC aktif saat jadwal berjalan (01:00 WIB)

#### 4. Laporan tidak terkirim ke Telegram
- **Penyebab**: Token Telegram salah atau koneksi internet bermasalah
- **Solusi**: 
  - Cek `.env` untuk TELEGRAM_BOT_TOKEN dan TELEGRAM_CHAT_ID
  - Test notifikasi dari dashboard: **"🔔 Test Telegram"**
  - Test: `php artisan report:send-daily-transactions`

#### 5. External API tidak respond
- **Penyebab**: Token expired atau koneksi internet bermasalah
- **Solusi**: 
  - Cek `EXTERNAL_API_TOKEN` di `.env`
  - Test API: jalankan `scripts/test-api-auth.php`
  - Lihat log: `storage/logs/laravel.log`

#### 6. Error "Call to a member function format() on null"
- **Penyebab**: Field date (valid_from/valid_until) pada voucher bernilai null
- **Solusi**: Sudah diperbaiki dengan pengecekan null di view

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` di `.env`
- [ ] Set `APP_DEBUG=false` di `.env`
- [ ] Generate production key: `php artisan key:generate`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Build assets: `npm run build`
- [ ] Setup cron job (Linux) atau Task Scheduler (Windows)
- [ ] Setup Telegram bot untuk production
- [ ] Configure HTTPS/SSL
- [ ] Setup database backup
- [ ] Configure log rotation
- [ ] Clear & optimize cache: `php artisan optimize`

### Server Requirements

- PHP >= 8.2
- PostgreSQL >= 13 atau MySQL >= 8.0
- Composer
- Node.js >= 18
- Web server (Nginx/Apache)
- SSL Certificate (untuk production)

## 📄 License

Aplikasi ini dibuat untuk keperluan sistem pembayaran rumah sakit.

---

## Laravel Framework

This project is built on Laravel 11. Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling.

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks.

## Contributing

Thank you for considering contributing to this project!

## Code of Conduct

Please review and abide by the [Laravel Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to the project maintainer.

## Laravel License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
