# Aplikasi Kasir Rumah Sakit

Aplikasi kasir rumah sakit berbasis web menggunakan Laravel 11 dengan fitur manajemen voucher diskon, transaksi pembayaran, dashboard marketing, dan laporan otomatis.

## Fitur Utama

### 1. **Role Management**
- **Kasir**: Mengelola transaksi pembayaran pasien
- **Marketing**: Mengelola voucher diskon dan melihat dashboard analitik

### 2. **Master Data Voucher Diskon**
Marketing dapat mengelola voucher dengan berbagai tipe:
- **Diskon Persentase**: dengan atau tanpa batas maksimal
  - Contoh: Asuransi Reliance 5% maks Rp 35.000 periode Januari 2026
  - Contoh: Asuransi Allianz 1% tanpa limit
- **Diskon Fixed**: nominal tetap per tindakan
  - Contoh: Asuransi Prudential Rp 15.000 per tindakan

### 3. **Dashboard Marketing**
Menampilkan informasi:
- Statistik kunjungan per asuransi
- Total pembayaran per asuransi
- Transaksi hari ini dan revenue
- Total diskon yang diberikan
- Daftar transaksi terbaru

### 4. **Transaksi Pembayaran (Kasir)**
- Input daftar tindakan/jasa medis
- Multiple layanan dalam satu transaksi
- Auto-calculate diskon berdasarkan voucher asuransi
- Edit dan hapus transaksi (hanya yang belum dibayar)
- Proses pembayaran
- Cetak bukti pembayaran PDF

### 5. **Insurance Discount System** 🆕
- ✅ **Diskon Asuransi Otomatis** - Diskon langsung dari asuransi (60-100%)
- ✅ **Voucher Code Manual** - Prioritas tertinggi saat diinput manual
- ✅ **Auto Voucher** - Otomatis pilih voucher terbaik jika ada
- ✅ **Coverage Limit** - Batas maksimal tanggungan asuransi
- ✅ **Preserve Discount** - Diskon tidak hilang saat sync API

**Priority Diskon:**
1. Manual Voucher Code (jika diinput kasir)
2. Insurance Discount Percentage (diskon langsung dari asuransi)
3. Auto Voucher dari Insurance (jika tidak ada diskon percentage)

**Contoh Diskon Aktif:**
- BPJS Kesehatan: 100% (gratis total)
- Mandiri Inhealth: 90%
- Allianz Indonesia: 85%
- Prudential: 75%

**Set/Update Diskon:**
```bash
php artisan insurance:update-discounts
```

### 6. **Telegram Notifications** 🆕
- ✅ **Laporan Harian Otomatis** via Telegram
- ✅ **Manual Report** - Kirim kapan saja ke Telegram
- ✅ **Test Connection** - Verifikasi bot setup
- ✅ File Excel attachment di Telegram
- ⚡ Setup **10x lebih mudah** dari email (no SMTP config!)
- 📱 Real-time push notifications

**Setup hanya 5 menit:**
1. Buat bot via @BotFather
2. Dapatkan Chat ID via @userinfobot
3. Update `.env` dengan token + chat ID
4. Done! 🎉

📖 Lihat: [TELEGRAM_QUICKSTART.md](TELEGRAM_QUICKSTART.md) untuk panduan lengkap

### 6. **Cron Job Laporan Harian**
- Otomatis berjalan setiap jam 01:00 dini hari
- Mengirim laporan transaksi kemarin dalam format Excel ke **Telegram**
- Notifikasi: https://t.me/masohdir

### 7. **API Integration - RS Delta Surya** 🆕
- ✅ **Sync Insurance** - Sinkronisasi data asuransi dari API eksternal
- ✅ **Sync Medical Services** - Sinkronisasi layanan & harga terbaru
- ✅ **Price Cache** - Harga tersimpan di database (40-100x lebih cepat)
- ✅ **Smart Preservation** - Diskon asuransi **TIDAK tertimpa** saat sync API
- ✅ **Auto Fallback** - Gunakan harga database jika API gagal

**PENTING:** Sejak update terbaru, `discount_percentage` pada insurance akan **dipertahankan** saat sync API. Hanya `name` dan `description` yang diupdate dari API eksternal. Ini mencegah diskon yang sudah diset manual hilang saat sync.

**Manual Sync:**
```bash
# Sync semua data
php artisan api:sync-all

# Atau dari dashboard Marketing
Dashboard → "🔄 Sync Data dari API"
```

## Tech Stack

- **Framework**: Laravel 11
- **Frontend**: Blade Templates + Tailwind CSS (Laravel Breeze)
- **Database**: PostgreSQL / MySQL
- **PDF Generation**: barryvdh/laravel-dompdf
- **Excel Export**: maatwebsite/excel
- **Telegram Bot**: irazasyed/telegram-bot-sdk 🆕
- **Authentication**: Laravel Breeze

## Instalasi

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & NPM
- PostgreSQL atau MySQL
- Mail server (untuk cron job email)

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone <repository-url>
cd hospitalpay
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Setup Environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Konfigurasi Database**
Edit file `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hospitalpay
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

5. **Konfigurasi Telegram** 🆕
Edit file `.env`:
```env
# Telegram Bot (untuk notifikasi laporan)
TELEGRAM_BOT_TOKEN=your_bot_token_from_botfather
TELEGRAM_CHAT_ID=your_chat_id
```

**Quick Setup:**
- Cari @BotFather di Telegram → `/newbot` → copy token
- Cari @userinfobot di Telegram → copy chat ID
- Paste ke `.env`
- Test: Dashboard → "🔔 Test Telegram"

📖 Panduan lengkap: [TELEGRAM_QUICKSTART.md](TELEGRAM_QUICKSTART.md)

6. **Run Migration & Seeder**
```bash
php artisan migrate
php artisan db:seed
```

Seeder akan membuat:
- 2 user (kasir & marketing) - password: `password`
- 3 asuransi dengan voucher masing-masing
- 10 layanan medis

7. **Build Assets**
```bash
npm run build
```

8. **Run Application**
```bash
php artisan serve
```

## User Credentials

### Kasir
- Email: `kasir@hospital.com`
- Password: `password`

### Marketing
- Email: `marketing@hospital.com`
- Password: `password`

## Setup Cron Job

Untuk menjalankan scheduler Laravel (termasuk laporan harian), tambahkan ke crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Atau untuk development, jalankan:
```bash
php artisan schedule:work
```

## Testing Cron Job / Laporan Transaksi

### Testing via UI (Recommended untuk Testing)
Tersedia button testing di dashboard untuk kemudahan testing:
1. Login sebagai **Kasir** atau **Marketing**
2. Di bagian atas dashboard, terdapat panel **"Testing Laporan Transaksi"**
3. Pilih tanggal yang ingin dilaporkan (default: kemarin)
4. Klik **"📧 Kirim Email"** untuk mengirim laporan ke email
5. Atau klik **"📥 Download Excel"** untuk download langsung

### Testing via Command Line
Untuk testing manual via artisan command:
```bash
php artisan report:send-daily-transactions
```

### Akses Testing
- **Route Kirim Email**: `POST /reports/send-daily` (dengan parameter `date`)
- **Route Download**: `GET /reports/download-daily` (dengan parameter `date`)
- Kedua route dapat diakses oleh **Kasir** dan **Marketing**

## Struktur Database

### Tables
- `users` - User dengan role kasir/marketing
- `insurances` - Master data asuransi
- `vouchers` - Voucher diskon per asuransi
- `medical_services` - Layanan/tindakan medis
- `transactions` - Header transaksi pembayaran
- `transaction_details` - Detail item transaksi

## Fitur Unggulan

1. **Auto-calculate Discount**: Sistem otomatis menghitung diskon per item berdasarkan voucher asuransi yang aktif dan valid
2. **Transaction Code**: Generate kode transaksi otomatis (format: TRXYYYYMMDDxxxxx)
3. **Soft Validation**: Voucher dengan periode berlaku dan status aktif
4. **Responsive Design**: UI responsive menggunakan Tailwind CSS
5. **PDF Receipt**: Generate struk pembayaran profesional
6. **Excel Report**: Export laporan dengan formatting

## License

Aplikasi ini dibuat untuk keperluan technical test.


Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
