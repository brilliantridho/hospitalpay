# Scripts & Testing Tools

Folder ini berisi script utilitas dan file testing untuk Hospital Payment System.

## 📂 Kategori File

### 🔧 Scheduler Scripts (Laporan Harian)

Script untuk setup dan menjalankan Task Scheduler Windows:

- **`setup-scheduler.ps1`** ⭐ - Setup utama Task Scheduler (run as admin)
- **`setup-scheduler-simple.ps1`** - Setup scheduler versi sederhana
- **`run-scheduler.ps1`** - Jalankan scheduler sekali
- **`scheduler-loop.ps1`** - Jalankan scheduler dalam loop continuous
- **`fix-scheduler.ps1`** - Fix scheduler jika ada masalah
- **`fix-scheduler.bat`** - Batch version untuk fix scheduler
- **`run-scheduler.bat`** - Batch version untuk run scheduler

**Cara Menggunakan:**
```powershell
# Setup Task Scheduler (sekali saja, butuh admin)
Start-Process powershell -Verb RunAs -ArgumentList "-NoExit", "-Command", "cd '$PWD'; .\scripts\setup-scheduler.ps1"

# Manual run (testing)
.\scripts\run-scheduler.ps1

# Continuous loop (biarkan terminal terbuka)
.\scripts\scheduler-loop.ps1
```

### 🧪 Testing Scripts

#### Database Testing
- **`check-db.php`** - Cek koneksi database
- **`check-insurances.php`** - Cek data asuransi
- **`check-prices.php`** - Cek data harga layanan

#### API Testing
- **`test-api-auth.php`** - Test autentikasi External API
- **`test-all-endpoints.php`** - Test semua API endpoints
- **`test-prices.php`** - Test API harga layanan
- **`test-price-validation.php`** - Test validasi harga

#### Transaction Testing
- **`test-transaction.php`** - Test transaksi pembayaran
- **`test-transaction-page.ps1`** - Test halaman transaksi
- **`test-voucher-endpoint.php`** - Test endpoint voucher

#### General Testing
- **`simple-test.php`** - Simple test general
- **`test-detailed.php`** - Detailed testing
- **`test-scheduler-independence.ps1`** - Test scheduler independence

## 🚀 Cara Menjalankan

### PHP Scripts

Jalankan dari root project:

```bash
# Test koneksi database
php scripts/check-db.php

# Test API authentication
php scripts/test-api-auth.php

# Test semua endpoints
php scripts/test-all-endpoints.php

# Test transaksi
php scripts/test-transaction.php
```

### PowerShell Scripts

```powershell
# Dari root project
.\scripts\nama-script.ps1

# Atau masuk ke folder scripts
cd scripts
.\nama-script.ps1
```

## ⚠️ Catatan Penting

### Scheduler Scripts
- `setup-scheduler.ps1` hanya perlu dijalankan **SEKALI**
- Butuh **admin privileges** untuk setup
- Setelah setup, Task Scheduler akan berjalan otomatis setiap hari pukul 01:00 WIB
- PC harus dalam keadaan aktif (tidak sleep/hibernate)

### Testing Scripts
- Pastikan file `.env` sudah dikonfigurasi dengan benar
- Beberapa test membutuhkan koneksi internet (untuk External API)
- Test script tidak akan mengubah data production (menggunakan transaction rollback jika tersedia)

## 📝 Menambah Script Baru

Jika membuat script testing atau utility baru, simpan di folder ini dengan naming convention:

- **Testing**: `test-*.php` atau `test-*.ps1`
- **Checking**: `check-*.php` atau `check-*.ps1`
- **Utility**: `nama-utility.ps1` atau `nama-utility.php`

---

Kembali ke [README utama](../README.md)
