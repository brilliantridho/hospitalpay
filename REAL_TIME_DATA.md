# Integrasi Data Real-time RS Delta Surya

## Overview

Sistem HospitalPay kini terintegrasi penuh dengan API RS Delta Surya untuk mendapatkan data real-time:
- ✅ **Data Asuransi** - Daftar asuransi yang bekerjasama
- ✅ **Data Tindakan Medis** - Daftar prosedur/tindakan yang tersedia
- ✅ **Harga Tindakan** - Harga real-time berdasarkan tanggal

## Fitur untuk Kasir

### 1. Dashboard Transaksi
**Lokasi:** `http://localhost:8000/kasir/transactions`

**Fitur Sync Data:**
- 🔄 **Sync Data** - Button untuk sinkronisasi manual semua data
- ⚙️ **Test API** - Link ke halaman testing API
- 📊 **Indicator** - Menampilkan jumlah asuransi dan tindakan yang tersedia

### 2. Form Transaksi Baru
**Lokasi:** `http://localhost:8000/kasir/transactions/create`

**Fitur:**
- ✅ Pilihan asuransi diambil dari data RS Delta Surya
- ✅ Pilihan tindakan medis diambil dari data RS Delta Surya
- ✅ **Harga otomatis** diambil dari API berdasarkan tanggal hari ini
- ✅ Indicator menunjukkan data real-time

**Cara Kerja Harga:**
```
1. Kasir memilih tindakan medis
2. System secara otomatis:
   - Mengecek cache (valid 1 jam)
   - Jika tidak ada di cache, fetch dari API
   - Mencari harga yang valid untuk tanggal hari ini
   - Fallback ke harga database jika API gagal
3. Harga ditampilkan dan digunakan dalam transaksi
```

## Fitur untuk Marketing

### 1. Dashboard Voucher
**Lokasi:** `http://localhost:8000/marketing/vouchers`

**Fitur Sync Data:**
- 🔄 **Sync Asuransi** - Button untuk sinkronisasi data asuransi
- ⚙️ **Test API** - Link ke halaman testing API
- 📊 **Indicator** - Menampilkan jumlah asuransi terdaftar

### 2. Form Voucher Baru
**Lokasi:** `http://localhost:8000/marketing/vouchers/create`

**Fitur:**
- ✅ Pilihan asuransi diambil dari data RS Delta Surya yang ter-sync
- ✅ Voucher otomatis tersedia untuk transaksi kasir

## Metode Sinkronisasi Data

### 1. Via Web Interface (Manual)

#### Untuk Kasir:
```
1. Login sebagai kasir
2. Buka Dashboard Transaksi
3. Klik button "🔄 Sync Data"
4. System akan sync:
   - Daftar Asuransi (10 items)
   - Daftar Tindakan Medis (11 items)
```

#### Untuk Marketing:
```
1. Login sebagai marketing
2. Buka Dashboard Voucher
3. Klik button "🔄 Sync Asuransi"
4. System akan sync daftar asuransi
```

### 2. Via Artisan Command (Otomatis)

#### Sync Semua Data:
```bash
php artisan external:sync
```

Output:
```
🔄 Starting external data synchronization...
🔐 Testing authentication...
✅ Authentication successful!

📋 Syncing insurances...
✅ Insurances: 0 new, 10 updated

💉 Syncing medical procedures...
✅ Procedures: 11 new, 0 updated
```

#### Sync Hanya Asuransi:
```bash
php artisan external:sync --insurances
```

#### Sync Hanya Tindakan:
```bash
php artisan external:sync --procedures
```

### 3. Scheduled Sync (Cron Job)

Tambahkan ke `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Sync setiap hari jam 1 pagi
    $schedule->command('external:sync')
             ->dailyAt('01:00')
             ->timezone('Asia/Jakarta');
    
    // Atau sync setiap 6 jam
    $schedule->command('external:sync')
             ->everySixHours();
}
```

Setup cron di server:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Data yang Ter-sync

### 1. Asuransi (10 items)
| ID | Nama Asuransi |
|----|---------------|
| 1 | AdMedika |
| 2 | Allianz Indonesia |
| 3 | BCA Life |
| 4 | BPJS Kesehatan |
| 5 | Mandiri Inhealth |
| 6 | Manulife Indonesia |
| 7 | Prudential |
| 8 | Reliance Indonesia |
| 9 | Sinarmas MSIG |
| 10 | Umum / Biaya Pribadi |

### 2. Tindakan Medis (11 items)
| ID | Nama Tindakan |
|----|---------------|
| 1 | CT Scan abdomen / perut |
| 2 | CT Scan kepala (tanpa kontras) |
| 3 | Elektrokardiogram (EKG) |
| 4 | Jahit luka (besar) |
| 5 | Jahit luka (kecil) |
| 6 | Konsultasi dokter spesialis |
| 7 | Konsultasi dokter umum |
| 8 | Operasi usus buntu |
| 9 | Persalinan sectio caesarea |
| 10 | Scaling / pembersihan karang gigi |
| 11 | USG (ultrasonografi) |

### 3. Harga Tindakan
Harga bervariasi berdasarkan tanggal:
- Setiap tindakan memiliki multiple harga berdasarkan periode
- System otomatis memilih harga yang sesuai dengan tanggal transaksi
- Harga di-cache selama 1 jam untuk performa

Contoh harga CT Scan abdomen:
```
1-4 Feb 2026: Rp 1.309.491
5-8 Feb 2026: Rp 1.358.471
9-13 Feb 2026: Rp 1.391.587
```

## Implementasi Teknis

### Model: MedicalService

**Method `getCurrentPrice($date = null)`**
```php
// Get harga real-time dari API
$medicalService = MedicalService::find($id);
$price = $medicalService->getCurrentPrice(); // Today
$price = $medicalService->getCurrentPrice('2026-02-10'); // Specific date
```

**Fitur:**
- ✅ Cache 1 jam untuk performa
- ✅ Auto fallback ke harga database jika API error
- ✅ Support custom date

**Method `getAllPrices()`**
```php
// Get semua available prices
$prices = $medicalService->getAllPrices();
// Returns array of prices with start_date and end_date
```

### Controller: TransactionController

**Penggunaan di `store()` method:**
```php
// OLD: Menggunakan harga dari database
$price = $medicalService->price;

// NEW: Menggunakan harga real-time dari API
$price = $medicalService->getCurrentPrice();
```

### Cache Strategy

**Token Authentication:**
- Cache duration: expires_in - 5 menit (buffer)
- Default: ~23 jam 55 menit
- Auto refresh jika expired (401)

**Procedure Prices:**
- Cache duration: 1 jam
- Cache key: `medical_service_price_{code}_{date}`
- Cache per procedure per date

## Error Handling

### Scenario 1: API Down
```
Kasir membuat transaksi → 
System coba get harga dari API → 
API error → 
Fallback ke harga database → 
Transaksi tetap berjalan ✅
```

### Scenario 2: Token Expired
```
Request data → 
Token expired (401) → 
Auto re-authenticate → 
Retry request → 
Success ✅
```

### Scenario 3: Network Timeout
```
Request timeout → 
Log error → 
Use database price → 
Continue ✅
```

## Monitoring & Logging

Semua aktivitas sync dicatat di `storage/logs/laravel.log`:

```
[2026-02-03] INFO: External API authentication successful
[2026-02-03] INFO: Insurance sync completed via command
[2026-02-03] INFO: Procedures sync completed via command
```

## Testing

### 1. Test Page
**URL:** `http://localhost:8000/api-sync/test-page`

Fitur:
- Test authentication
- Test sync insurances
- Test sync medical services
- Test sync all

### 2. Via PHP Scripts
```bash
# Test authentication dan semua endpoint
php test-detailed.php

# Test procedure prices
php test-prices.php
```

## Best Practices

### Untuk Kasir:
1. ✅ Sync data setiap pagi sebelum mulai kerja
2. ✅ Gunakan tanggal transaksi yang benar
3. ✅ Cek indicator jumlah data sebelum buat transaksi

### Untuk Marketing:
1. ✅ Sync data asuransi saat ada perubahan
2. ✅ Pastikan asuransi tersedia sebelum buat voucher
3. ✅ Update voucher jika ada perubahan asuransi

### Untuk Administrator:
1. ✅ Setup scheduled sync untuk otomatis
2. ✅ Monitor log untuk error
3. ✅ Backup data sebelum major sync
4. ✅ Verify kredensial API di `.env`

## FAQ

**Q: Berapa lama cache berlaku?**
A: Token: ~24 jam, Prices: 1 jam

**Q: Apa yang terjadi jika API down?**
A: System fallback ke harga database, transaksi tetap berjalan

**Q: Apakah harga otomatis update?**
A: Ya, harga diambil real-time saat membuat transaksi

**Q: Bagaimana cara manual sync?**
A: Klik button "Sync Data" di dashboard atau jalankan `php artisan external:sync`

**Q: Data di-sync kemana?**
A: Data disimpan di database lokal tables: `insurances` dan `medical_services`

**Q: Apakah bisa sync sebagian data?**
A: Ya, gunakan flag `--insurances` atau `--procedures`

## Troubleshooting

**Error: "Authentication failed"**
- Periksa kredensial di `.env`
- Verify email dan password valid

**Error: "Failed to sync"**
- Check koneksi internet
- Verify API endpoint available
- Check log di `storage/logs/laravel.log`

**Harga tidak berubah**
- Clear cache: `php artisan cache:clear`
- Re-sync data: `php artisan external:sync`
