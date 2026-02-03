# Validasi Harga untuk Transaksi

## Masalah
Kasir dan marketing memerlukan nominal harga untuk transaksi. Jika tidak ada nominal, maka tidak dapat terjadi transaksi apapun, baik untuk asuransi maupun tindakan medis.

## Solusi yang Diimplementasikan

### 1. **Validasi Harga Tindakan Medis**

Sebelum transaksi dibuat, sistem akan:

1. **Memvalidasi ketersediaan harga** dari setiap tindakan medis
2. **Mengambil harga real-time** dari API RS Delta Surya
3. **Menolak transaksi** jika harga tidak tersedia atau = 0

```php
// Validasi sebelum membuat transaksi
foreach ($validated['services'] as $service) {
    $medicalService = MedicalService::find($service['medical_service_id']);
    $price = $medicalService->getCurrentPrice();
    
    // Validasi harga tidak boleh 0 atau null
    if (!$price || $price <= 0) {
        return back()->with('error', 
            "Harga untuk tindakan '{$medicalService->name}' tidak tersedia."
        );
    }
}
```

### 2. **Sumber Harga**

#### Tindakan Medis (Medical Services):
- **Primary**: API RS Delta Surya `/procedures/{id}/prices` (real-time)
- **Fallback**: Database field `price` (jika API gagal)
- **Cache**: 1 jam per tindakan per tanggal

#### Asuransi (Insurance):
- **Diskon**: Field `discount_percentage` di database (manual input)
- **Voucher**: Tabel `vouchers` dengan relasi ke asuransi
- **API RS Delta Surya**: TIDAK menyediakan data diskon/harga asuransi

### 3. **Alur Transaksi**

```
1. Kasir memilih tindakan medis
   ↓
2. Sistem validasi: Apakah harga tersedia?
   ├─ YA → Lanjut ke langkah 3
   └─ TIDAK → Tampilkan error dan batalkan transaksi
   ↓
3. Kasir memilih asuransi (opsional)
   ↓
4. Sistem cari voucher aktif untuk asuransi tersebut
   ├─ Ada voucher → Gunakan diskon voucher
   └─ Tidak ada voucher → Gunakan discount_percentage dari database
   ↓
5. Hitung total dan buat transaksi
```

## Error Messages

### Harga Tindakan Tidak Tersedia
```
Harga untuk tindakan 'CT Scan' tidak tersedia. 
Silakan sinkronkan data dari RS Delta Surya atau hubungi administrator.
```

### Gagal Mengambil Harga dari API
```
Gagal mendapatkan harga untuk tindakan 'EKG': Connection timeout
```

## Cara Mengatasi Error

### 1. **Jika Harga Tindakan Tidak Tersedia**

**Opsi A: Sinkronisasi Data dari API**
```bash
# Via artisan command
php artisan external:sync --procedures

# Via web interface (untuk kasir/marketing)
# Klik tombol "Sync Data dari RS Delta Surya" di halaman transaksi
```

**Opsi B: Set Manual di Database**
```sql
UPDATE medical_services 
SET price = 150000 
WHERE code = 'CT-SCAN-001';
```

### 2. **Jika Asuransi Tidak Ada Diskon**

**Opsi A: Set Discount di Database**
```sql
UPDATE insurances 
SET discount_percentage = 10.00 
WHERE name = 'BPJS Kesehatan';
```

**Opsi B: Buat Voucher untuk Asuransi**
```sql
INSERT INTO vouchers (insurance_id, code, discount_percentage, valid_from, valid_until, is_active)
VALUES (1, 'BPJS2026', 15.00, '2026-01-01', '2026-12-31', true);
```

## Struktur Data API RS Delta Surya

### Response `/insurances`
```json
{
  "insurances": [
    {
      "id": "uuid",
      "name": "AdMedika"
    }
  ]
}
```
⚠️ **Tidak ada field discount atau harga**

### Response `/procedures`
```json
{
  "procedures": [
    {
      "id": "uuid", 
      "name": "CT Scan"
    }
  ]
}
```
⚠️ **Tidak ada field harga**

### Response `/procedures/{id}/prices`
```json
{
  "prices": [
    {
      "id": "uuid",
      "unit_price": 1309491,
      "start_date": {
        "value": "2026-01-01"
      },
      "end_date": {
        "value": "2026-12-31"
      }
    }
  ]
}
```
✅ **Harga tersedia dengan range tanggal**

## Kesimpulan

1. **Tindakan Medis**: Harga **HARUS** tersedia dari API atau database sebelum transaksi dibuat
2. **Asuransi**: Diskon diambil dari voucher atau field `discount_percentage` (tidak dari API)
3. **Validasi Ketat**: Sistem akan menolak transaksi jika harga tidak valid
4. **Real-time Pricing**: Harga selalu diambil real-time dari API saat transaksi dibuat
5. **Fallback Mechanism**: Jika API gagal, gunakan harga dari database

## Testing

```bash
# Test validasi harga
php artisan tinker

>>> $service = App\Models\MedicalService::first();
>>> $price = $service->getCurrentPrice();
>>> echo "Price: Rp " . number_format($price, 0, ',', '.');
```

## Log Monitoring

Check logs untuk error:
```bash
tail -f storage/logs/laravel.log | grep "Failed to get current price"
```
