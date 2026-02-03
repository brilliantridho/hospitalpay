# 📋 Panduan Insurance Discount System

## 🎯 Overview

Sistem diskon asuransi memungkinkan pasien mendapatkan potongan harga otomatis berdasarkan asuransi yang dipilih. Diskon ini **tidak akan hilang** saat sinkronisasi data dari API eksternal RS Delta Surya.

---

## 💡 Priority Diskon

Sistem menggunakan **3-level priority** untuk menghitung diskon:

### 1️⃣ **Priority 1: Manual Voucher Code** (Tertinggi)
- Kasir input voucher code secara manual
- Berlaku untuk voucher khusus/promo tertentu
- **Override** semua diskon lainnya

### 2️⃣ **Priority 2: Insurance Discount Percentage**
- Diskon langsung dari persentase asuransi (`discount_percentage`)
- Berlaku otomatis saat pilih asuransi
- Tidak perlu input voucher code
- **Contoh**: BPJS 100%, Mandiri Inhealth 90%

### 3️⃣ **Priority 3: Auto Voucher from Insurance**
- Sistem otomatis cari voucher terbaik untuk asuransi tersebut
- Hanya berlaku jika `discount_percentage` = 0 atau NULL
- Pilih voucher dengan diskon terbesar

---

## 📊 Diskon Aktif Saat Ini

| Asuransi | Diskon | Keterangan |
|----------|--------|------------|
| BPJS Kesehatan | **100%** | Gratis total untuk pasien |
| Mandiri Inhealth | **90%** | Pasien bayar 10% saja |
| Allianz Indonesia | **85%** | Pasien bayar 15% |
| Asuransi Allianz | **80%** | Pasien bayar 20% |
| Prudential/Asuransi Prudential | **75%** | Pasien bayar 25% |
| Manulife Indonesia | **70%** | Pasien bayar 30% |
| BCA Life | **65%** | Pasien bayar 35% |
| Asuransi Reliance | **60%** | Pasien bayar 40% |
| Reliance Indonesia | **60%** | Pasien bayar 40% |

---

## 🔧 Set/Update Diskon

### Command: Update Batch Discounts
```bash
php artisan insurance:update-discounts
```

**Output:**
```
Updating insurance discounts...

✅ BPJS Kesehatan: 100%
✅ Mandiri Inhealth: 90%
✅ Allianz Indonesia: 85%
...
Updated: 10 insurances
```

### Manual Update via Database
```sql
UPDATE insurances 
SET discount_percentage = 100 
WHERE name LIKE '%BPJS%';
```

### Via Tinker
```bash
php artisan tinker
```
```php
use App\Models\Insurance;
$bpjs = Insurance::where('name', 'LIKE', '%BPJS%')->first();
$bpjs->discount_percentage = 100;
$bpjs->save();
```

---

## 🔄 API Sync & Discount Preservation

### ⚠️ Masalah Sebelumnya
Sebelum update, setiap kali sync data dari API RS Delta Surya:
- `discount_percentage` tertimpa menjadi 0
- Admin harus manual set ulang diskon
- Sangat merepotkan jika sync sering dilakukan

### ✅ Solusi: Smart Preservation
Update terbaru di `ApiSyncController.php`:

```php
if ($existing) {
    // Update existing: preserve discount_percentage
    $existing->update([
        'name' => $insuranceData['name'],
        'description' => $insuranceData['description'] ?? null,
        // discount_percentage NOT updated - preserve manual settings
    ]);
} else {
    // Create new: use default from API
    Insurance::create([
        'code' => $insuranceData['id'],
        'name' => $insuranceData['name'],
        'discount_percentage' => $insuranceData['discount_percentage'] ?? 0,
        'description' => $insuranceData['description'] ?? null,
    ]);
}
```

**Hasil:**
- ✅ Insurance existing: `discount_percentage` **TIDAK berubah**
- ✅ Insurance baru: Gunakan default dari API (biasanya 0)
- ✅ Name & description tetap update dari API
- ✅ **Tidak perlu manual update lagi** setelah sync!

---

## 🧪 Testing

### 1. Cek Diskon Saat Ini
```bash
php artisan check:insurance
```

### 2. Test Kalkulasi Diskon
```bash
php artisan test:insurance-discount
```

**Output:**
```
Service: Konsultasi Dokter Umum
Price per item: Rp 150.000
Quantity: 2
Subtotal: Rp 300.000

With BPJS Kesehatan (100.00%):
  Discount: Rp 300.000
  Total: Rp 0

With Mandiri Inhealth (90.00%):
  Discount: Rp 270.000
  Total: Rp 30.000
```

### 3. Test Preservation Saat Update
```bash
php artisan test:sync-preservation
```

**Output:**
```
BEFORE: BPJS Kesehatan = 100.00%
AFTER: BPJS Kesehatan = 100.00%
✅ SUCCESS: Discount preserved during update!
```

### 4. Test via Browser
1. Login sebagai kasir: http://localhost:8000
2. Buat transaksi baru
3. Pilih layanan medis + quantity
4. Pilih asuransi (contoh: BPJS Kesehatan)
5. **Lihat total terpotong otomatis** di bagian bawah form
6. Submit transaksi
7. Cek detail transaksi - diskon tertera jelas

---

## 📌 Coverage Limit

Beberapa asuransi memiliki **coverage limit** (batas tanggungan maksimal):

```php
// Example in transaction calculation:
if ($insurance->coverage_limit) {
    $discountPerItem = min($discountPerItem, $insurance->coverage_limit / $quantity);
}
```

**Set Coverage Limit:**
```bash
php artisan tinker
```
```php
$insurance = Insurance::find(1);
$insurance->coverage_limit = 5000000; // Rp 5 juta per tahun
$insurance->save();
```

---

## 🚨 Troubleshooting

### Diskon tidak terpotong saat transaksi?

**1. Cek data discount_percentage:**
```bash
php artisan check:insurance
```
Pastikan asuransi yang dipilih memiliki `discount_percentage > 0`

**2. Update diskon jika masih 0:**
```bash
php artisan insurance:update-discounts
```

**3. Cek log Laravel:**
```bash
tail -f storage/logs/laravel.log
```

**4. Test kalkulasi:**
```bash
php artisan test:insurance-discount
```

### Diskon hilang setelah sync API?

**Cek kode ApiSyncController:**
- Pastikan update menggunakan `$existing->update()` tanpa `discount_percentage`
- Jangan gunakan `updateOrCreate()` yang akan overwrite semua field

**Verify:**
```bash
php artisan test:sync-preservation
```

### Browser tidak update total otomatis?

**Cek JavaScript di create.blade.php:**
- Function `calculateTotals()` harus terpanggil saat pilih insurance
- Event listener `change` pada select insurance harus aktif

**Debug di Browser Console:**
```javascript
// Cek apakah insurance discount terdeteksi
const insuranceSelect = document.getElementById('insurance_id');
const selectedOption = insuranceSelect.options[insuranceSelect.selectedIndex];
console.log('Discount:', selectedOption.dataset.discount);
```

---

## 📖 Related Commands

```bash
# Cek data insurance
php artisan check:insurance

# Update batch discounts
php artisan insurance:update-discounts

# Test kalkulasi
php artisan test:insurance-discount

# Test preservation
php artisan test:sync-preservation

# Sync dari API (discount akan preserved)
php artisan api:sync-insurances
```

---

## 🎓 Tips

1. **Set diskon sekali** via `php artisan insurance:update-discounts`
2. **Sync API kapan saja** tanpa khawatir diskon hilang
3. **Test sebelum production** menggunakan command test
4. **Monitor log** untuk debug masalah
5. **Backup database** sebelum update besar

---

## ✅ Checklist Setup

- [ ] Database migration selesai
- [ ] Seeder dijalankan
- [ ] Insurance discount diset via command
- [ ] Test kalkulasi berhasil
- [ ] Test via browser berhasil
- [ ] API sync tidak menghilangkan diskon
- [ ] Documentation dibaca tim

---

**Last Updated:** February 3, 2026
**Version:** 2.0 (with API Sync Preservation)
