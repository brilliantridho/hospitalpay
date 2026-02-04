# Implementasi Voucher System

## Overview
Sistem voucher telah diimplementasikan dengan lengkap untuk memungkinkan kasir menggunakan kode voucher pada saat transaksi. Voucher dapat dikaitkan dengan asuransi tertentu atau bersifat umum untuk semua asuransi.

## Fitur Voucher

### 1. **Jenis Voucher**
- **Voucher Asuransi**: Terikat dengan asuransi tertentu (insurance_id)
- **Voucher Umum**: Tidak terikat asuransi (insurance_id = NULL), dapat digunakan semua pasien

### 2. **Tipe Diskon**
- **Percentage**: Diskon berdasarkan persentase dengan opsi maksimal diskon
  - Contoh: 20% dengan maksimal Rp 500.000
- **Fixed Amount**: Diskon nominal tetap
  - Contoh: Rp 100.000

### 3. **Validasi Voucher**
Voucher memiliki berbagai validasi:
- **Kode Unik**: Setiap voucher memiliki kode unik (uppercase)
- **Status Aktif**: Voucher harus aktif (is_active = true)
- **Periode Berlaku**: Validasi tanggal valid_from dan valid_until
- **Minimum Transaksi**: Nilai minimum transaksi untuk menggunakan voucher
- **Batas Penggunaan**: Jumlah maksimal voucher dapat digunakan (usage_limit)
- **Tracking Penggunaan**: Mencatat berapa kali voucher sudah dipakai (used_count)

## Struktur Database

### Tabel `vouchers`
```sql
id                  BIGINT PRIMARY KEY
code                VARCHAR(255) UNIQUE NOT NULL  -- Kode voucher (UPPERCASE)
description         TEXT                          -- Deskripsi voucher
insurance_id        BIGINT NULLABLE               -- FK ke insurances (NULL = voucher umum)
discount_type       ENUM('percentage', 'fixed')   -- Tipe diskon
discount_value      DECIMAL(15,2)                 -- Nilai diskon (% atau rupiah)
max_discount        DECIMAL(15,2) NULLABLE        -- Max diskon (untuk percentage)
min_transaction     DECIMAL(15,2) NULLABLE        -- Minimum nilai transaksi
usage_limit         INTEGER NULLABLE              -- Batas maksimal pemakaian
used_count          INTEGER DEFAULT 0             -- Jumlah sudah digunakan
valid_from          DATE NULLABLE                 -- Berlaku dari tanggal
valid_until         DATE NULLABLE                 -- Berlaku sampai tanggal
is_active           BOOLEAN DEFAULT TRUE          -- Status aktif
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

## Flow Penggunaan Voucher

### Di Kasir (Transaction Create)

1. **Input Kode Voucher**
   - Kasir mengetik kode voucher di form transaksi
   - Kode otomatis diubah ke uppercase

2. **Validasi Real-time (AJAX)**
   - Kasir klik tombol "Cek Voucher"
   - Sistem memvalidasi voucher secara real-time via AJAX
   - Response JSON:
     ```json
     {
       "valid": true,
       "message": "Voucher valid!",
       "voucher": {
         "code": "NEWYEAR2026",
         "description": "Diskon Tahun Baru 2026",
         "discount_type": "percentage",
         "discount_value": 20,
         "max_discount": 500000,
         "discount_text": "20% (Maks: Rp 500.000)",
         "min_transaction": 500000,
         "usage_remaining": "45 / 50",
         "insurance_id": null
       }
     }
     ```

3. **Tampilan Info Voucher**
   - Jika valid: Box hijau dengan info diskon, deskripsi, sisa pemakaian
   - Jika invalid: Box merah dengan pesan error
   - Jika ada warning: Box kuning (hampir habis kuota)

4. **Auto-fill Asuransi**
   - Jika voucher terikat dengan asuransi, otomatis pilih asuransi tersebut

5. **Kalkulasi Diskon**
   - Sistem otomatis hitung ulang total dengan diskon
   - **Prioritas Diskon:**
     1. **Manual Voucher Code** (jika kasir ketik & validate voucher code)
     2. **Insurance Discount Percentage** (jika asuransi punya discount_percentage > 0, contoh: BPJS 100%)
     3. **Auto Voucher** (jika ada voucher di database untuk asuransi tersebut)
   - Real-time calculation saat pilih asuransi atau input layanan
   - Contoh: BPJS Kesehatan dengan discount_percentage 100% → diskon 100% otomatis tanpa perlu voucher

6. **Submit Transaksi**
   - Validasi ulang voucher saat submit
   - Cek minimum transaksi
   - Cek usage limit
   - Increment `used_count` setelah transaksi berhasil

### Di Marketing (Voucher Management)

1. **Create Voucher**
   - Input kode unik (required)
   - Pilih asuransi (optional - kosongkan untuk voucher umum)
   - Set tipe dan nilai diskon
   - Set minimum transaksi (optional)
   - Set batas penggunaan (optional)
   - Set periode berlaku (optional)

2. **Edit Voucher**
   - Update semua field kecuali `used_count` (readonly)
   - Tampilkan statistik penggunaan: "3 / 100"
   - Progress bar visual untuk tracking usage

3. **List Voucher**
   - Tampilan kode voucher dengan format monospace
   - Deskripsi singkat
   - Info asuransi atau "Voucher Umum"
   - Nilai diskon dengan max discount
   - Minimum transaksi
   - Progress penggunaan dengan bar
   - Periode berlaku
   - Status aktif/nonaktif

## API Endpoints

### Check Voucher (AJAX)
```
POST /kasir/transactions/check-voucher
Content-Type: application/json

Request:
{
  "code": "NEWYEAR2026",
  "transaction_amount": 1000000
}

Response (Success):
{
  "valid": true,
  "message": "Voucher valid!",
  "voucher": {
    "code": "NEWYEAR2026",
    "description": "Diskon Tahun Baru 2026 - 20% maksimal Rp 500.000",
    "discount_type": "percentage",
    "discount_value": 20,
    "max_discount": 500000,
    "discount_text": "20% (Maks: Rp 500.000)",
    "min_transaction": 500000,
    "usage_remaining": "45 / 50",
    "insurance_id": null
  }
}

Response (Error):
{
  "valid": false,
  "message": "Voucher tidak valid atau sudah kadaluarsa"
}
```

## Model Methods

### Voucher Model

```php
// Validasi voucher dengan amount transaksi
public function isValid(?float $transactionAmount = null): bool

// Get pesan error validasi
public function getValidationMessage(?float $transactionAmount = null): ?string

// Hitung diskon berdasarkan amount
public function calculateDiscount(float $amount): float

// Increment usage count
public function incrementUsage(): void

// Get text formatted diskon
public function getDiscountText(): string
```

## Sample Vouchers

Sistem telah di-seed dengan voucher contoh:

### 1. Voucher Asuransi
- **PRUDEN60-2026**: Prudential 60% (dari voucher lama, updated)
- **ALLIAN100-2026**: Allianz 100% (dari voucher lama, updated)
- **RELIAN80-2026**: Reliance 80% (dari voucher lama, updated)

### 2. Voucher Umum
- **NEWYEAR2026**: Diskon Tahun Baru 20% maks Rp 500.000
  - Min transaksi: Rp 500.000
  - Usage limit: 50x
  - Valid: 1-31 Januari 2026

- **WELCOME100**: Diskon selamat datang Rp 100.000
  - Min transaksi: Rp 300.000
  - Usage limit: 200x
  - Valid: 3 bulan dari sekarang

- **RAMADAN50**: Diskon Ramadan 15% maks Rp 300.000
  - Min transaksi: Rp 200.000
  - Usage limit: 100x
  - Valid: 1 Maret - 30 April 2026

## Testing Flow

### 1. Test sebagai Marketing
```
1. Login sebagai user dengan role 'marketing'
2. Akses: /marketing/vouchers
3. Klik "Tambah Voucher"
4. Isi form:
   - Kode: TESTCODE123
   - Deskripsi: Testing voucher
   - Asuransi: (kosongkan untuk umum)
   - Tipe Diskon: percentage
   - Nilai Diskon: 10
   - Max Diskon: 100000
   - Min Transaksi: 100000
   - Usage Limit: 10
5. Submit dan verifikasi di list
```

### 2. Test sebagai Kasir
```
1. Login sebagai user dengan role 'kasir'
2. Akses: /kasir/transactions/create
3. Input voucher code: NEWYEAR2026
4. Klik "Cek Voucher"
5. Verifikasi:
   - Tampil info box hijau
   - Discount info muncul
   - Auto-select asuransi (jika terikat)
6. Pilih layanan medis dengan total >= min_transaction
7. Verifikasi diskon terkalkulasi
8. Submit transaksi
9. Check di database: used_count bertambah
```

## Business Rules

1. **Prioritas Diskon**
   - **Manual voucher code** (jika kasir input kode voucher) > **Insurance discount** > **Auto voucher dari asuransi**
   - Jika kasir input voucher code: gunakan diskon voucher, abaikan diskon asuransi
   - Jika tidak input voucher code: gunakan discount_percentage dari asuransi (contoh: BPJS 100%)
   - Auto voucher dari database hanya digunakan jika asuransi tidak punya discount_percentage

2. **Validasi Berlapis**
   - Real-time: AJAX check saat kasir input kode
   - Server-side: Validasi ulang saat submit transaksi

3. **Usage Tracking**
   - `used_count` increment hanya saat transaksi berhasil
   - Tidak decrement saat transaksi dibatalkan (untuk audit trail)

4. **Code Format**
   - Uppercase otomatis
   - Max 50 karakter
   - Unique constraint di database

5. **Minimum Transaction**
   - Jika set, transaksi harus >= min_transaction
   - Jika NULL, tidak ada minimum

6. **Usage Limit**
   - Jika set, cek `used_count < usage_limit`
   - Jika NULL, unlimited usage

7. **Insurance Discount**
   - Otomatis diterapkan jika asuransi memiliki discount_percentage > 0
   - Coverage_limit (jika ada) membatasi maksimal diskon total
   - Tampil real-time di form kasir saat pilih asuransi

## Files Modified/Created

### Migrations
- `2026_02_03_000003_add_code_to_vouchers.php` - Add voucher fields
- `2026_02_03_000004_make_insurance_id_nullable_in_vouchers.php` - Allow general vouchers

### Models
- `app/Models/Voucher.php` - Enhanced with validation and calculation methods

### Controllers
- `app/Http/Controllers/Marketing/VoucherController.php` - Updated validation
- `app/Http/Controllers/Kasir/TransactionController.php` - Add voucher logic

### Views
- `resources/views/marketing/vouchers/index.blade.php` - Enhanced list with usage tracking
- `resources/views/marketing/vouchers/create.blade.php` - Add code & tracking fields
- `resources/views/marketing/vouchers/edit.blade.php` - Add code & tracking fields
- `resources/views/kasir/transactions/create.blade.php` - Add voucher input & AJAX

### Routes
- `routes/web.php` - Add POST /kasir/transactions/check-voucher

### Seeders
- `database/seeders/VoucherSeeder.php` - Populate sample vouchers

## Notes

- Server running: `php artisan serve` at http://localhost:8000
- Voucher codes case-insensitive saat input, stored as uppercase
- Usage tracking audit trail (tidak decrement saat batal)
- Support untuk voucher tanpa batas (nullable fields)
- Real-time feedback untuk UX yang lebih baik
