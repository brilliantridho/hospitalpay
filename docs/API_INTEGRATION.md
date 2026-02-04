# API External Integration Guide

## Format API Eksternal

### Autentikasi
**Endpoint:** `POST https://recruitment.rsdeltasurya.com/api/v1/auth`

**Request:**
```bash
curl --location 'https://recruitment.rsdeltasurya.com/api/v1/auth' \
--header 'Content-Type: application/json' \
--data-raw '{
    "email": "arfi.afianto@rsdeltasurya.com",
    "password": "081234567890"
}'
```

**Response:**
```json
{
    "token_type": "Bearer",
    "expires_in": 86400,
    "access_token": "9cf2dd9d-9c53-4e1db85d7b3fa2217477|hGzYyKej9VScCOG9G4suVb9Ly5iZYCPPUyIhdTnv60442450"
}
```

## Konfigurasi

### 1. Setup Environment Variables

Edit file `.env` dan isi dengan informasi autentikasi Anda:

```env
EXTERNAL_API_BASE_URL=https://recruitment.rsdeltasurya.com/api/v1
EXTERNAL_API_EMAIL=your-email@rsdeltasurya.com
EXTERNAL_API_PASSWORD=your-password-here
```

**PENTING:**
- Email harus terdaftar di sistem RS Delta Surya
- Password adalah kredensial yang valid untuk autentikasi
- Jika menggunakan contoh dari dokumentasi: `arfi.afianto@rsdeltasurya.com` dengan password `081234567890`
- Untuk production, gunakan kredensial Anda sendiri yang telah terdaftar

### 2. Endpoint yang Tersedia

Setelah login sebagai user (marketing atau kasir), Anda dapat mengakses endpoint berikut:

#### Test Autentikasi
- **URL:** `/api-sync/test-auth`
- **Method:** GET
- **Deskripsi:** Test koneksi dan autentikasi ke API eksternal
- **Response:** JSON dengan status autentikasi

#### Sync Data Asuransi
- **URL:** `/api-sync/sync-insurances`
- **Method:** POST
- **Deskripsi:** Sinkronisasi data asuransi dari API eksternal ke database lokal
- **Response:** Redirect dengan pesan sukses/error

#### Sync Data Tindakan Medis
- **URL:** `/api-sync/sync-medical-services`
- **Method:** POST
- **Deskripsi:** Sinkronisasi data tindakan medis dari API eksternal ke database lokal
- **Response:** Redirect dengan pesan sukses/error

#### Sync Semua Data
- **URL:** `/api-sync/sync-all`
- **Method:** POST
- **Deskripsi:** Sinkronisasi semua data (asuransi + tindakan medis) sekaligus
- **Response:** Redirect dengan ringkasan hasil sinkronisasi

## Cara Penggunaan

### Via Browser/Postman

1. **Test Autentikasi:**
   ```
   GET http://localhost:8000/api-sync/test-auth
   ```

2. **Sync Data:**
   ```
   POST http://localhost:8000/api-sync/sync-all
   ```

### Via Artisan Command (Coming Soon)

Anda dapat membuat command artisan untuk melakukan sync otomatis:

```bash
php artisan api:sync-all
php artisan api:sync-insurances
php artisan api:sync-medical-services
```

## Implementasi Teknis

### 1. Service Class: `ExternalApiService`

Service ini menangani semua komunikasi dengan API eksternal:

- **Autentikasi:** Method `authenticate()` akan mendapatkan token dan menyimpannya di cache selama 50 menit
- **Get Insurances:** Method `getInsurances()` untuk mengambil daftar asuransi
- **Get Medical Services:** Method `getMedicalServices()` untuk mengambil daftar tindakan medis
- **Auto Re-authentication:** Jika token expired (401), akan otomatis melakukan re-autentikasi

### 2. Controller: `ApiSyncController`

Controller ini menyediakan endpoint untuk sinkronisasi:

- `testAuth()` - Test autentikasi
- `syncInsurances()` - Sync data asuransi
- `syncMedicalServices()` - Sync data tindakan medis
- `syncAll()` - Sync semua data sekaligus

### 3. Token Caching

Token autentikasi di-cache selama 50 menit untuk menghindari request autentikasi berulang-ulang. Cache akan dihapus otomatis jika:
- Token expired (mendapat response 401)
- Waktu cache habis

## Database Schema

### Tabel Insurances

Kolom yang ditambahkan:
- `code` (string, nullable) - Kode unik dari API eksternal
- `discount_percentage` (decimal 5,2) - Persentase diskon

### Tabel Medical Services

Kolom yang ditambahkan:
- `code` (string, nullable) - Kode unik dari API eksternal
- `category` (string, nullable) - Kategori tindakan medis

## Error Handling

Service ini dilengkapi dengan error handling yang komprehensif:

1. **Log Error:** Semua error dicatat di log Laravel
2. **User Feedback:** Pesan error yang user-friendly
3. **Transaction Rollback:** Jika terjadi error saat sync, perubahan database akan di-rollback
4. **Retry Mechanism:** Auto retry jika token expired

## Troubleshooting

### Error: "Gagal autentikasi ke API eksternal"

**Solusi:**
- Pastikan `EXTERNAL_API_EMAIL` dan `EXTERNAL_API_PASSWORD` sudah benar di `.env`
- Pastikan email sudah terdaftar di sistem (gunakan email dengan domain @rsdeltasurya.com atau email yang valid)
- Pastikan password sesuai dengan akun Anda
- Testing menunjukkan error yang mungkin muncul:
  - Status 422 dengan "Email yang dipilih tidak valid" = Email tidak terdaftar
  - Status 422 dengan "Password salah" = Email valid tapi password salah
  - Status 200 = Autentikasi berhasil
- Cek koneksi internet
- Cek log di `storage/logs/laravel.log`

### Testing dengan Script

Gunakan `simple-test.php` untuk test koneksi:
```bash
php simple-test.php
```

Script ini akan menampilkan:
- URL endpoint yang digunakan
- Email dan password yang digunakan
- Status code response
- Response body dari API

### Error: "Gagal mendapatkan data dari API"

**Solusi:**
- Pastikan autentikasi berhasil terlebih dahulu dengan test `/api-sync/test-auth`
- Cek apakah endpoint API eksternal tersedia
- Cek log untuk detail error

## Customization

### Menyesuaikan Struktur Response API

Jika struktur response dari API eksternal berbeda, edit method di `ExternalApiService.php`:

```php
// Di method getInsurances()
$insurance = Insurance::updateOrCreate(
    ['code' => $insuranceData['code'] ?? $insuranceData['id']], // sesuaikan key
    [
        'name' => $insuranceData['name'],
        'discount_percentage' => $insuranceData['discount_percentage'] ?? 0,
    ]
);
```

### Menambahkan Endpoint Baru

1. Tambahkan method di `ExternalApiService.php`:
   ```php
   public function getNewData() {
       $token = $this->getToken();
       // ... implementasi
   }
   ```

2. Tambahkan method di `ApiSyncController.php`:
   ```php
   public function syncNewData() {
       $data = $this->apiService->getNewData();
       // ... process data
   }
   ```

3. Tambahkan route di `web.php`:
   ```php
   Route::post('/sync-new-data', [ApiSyncController::class, 'syncNewData'])->name('sync-new-data');
   ```

## Security Notes

⚠️ **PENTING:**
- Jangan commit file `.env` ke git
- Pastikan kredensial API disimpan dengan aman
- Token di-cache di server, pastikan cache driver aman
- Endpoint sync hanya bisa diakses oleh user yang sudah login
