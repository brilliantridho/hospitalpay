# Error Handling Documentation

## Pesan Error Autentikasi

Sistem sekarang dapat membedakan berbagai jenis error autentikasi dengan pesan yang spesifik:

### 1. Email Tidak Terdaftar
**Status Code:** 422  
**Response API:**
```json
{
    "message": "Email yang dipilih tidak valid.",
    "errors": {
        "email": ["Email yang dipilih tidak valid."]
    }
}
```
**Pesan ke User:**  
> "Email tidak terdaftar di sistem. Pastikan email Anda sudah terdaftar."

**Solusi:**
- Gunakan email yang sudah terdaftar di sistem RS Delta Surya
- Periksa ejaan email
- Hubungi administrator untuk registrasi email baru

---

### 2. Password Salah
**Status Code:** 422  
**Response API:**
```json
{
    "message": "Password salah.",
    "errors": {
        "password": ["Password salah."]
    }
}
```
**Pesan ke User:**  
> "Password yang Anda masukkan salah. Periksa kembali password Anda."

**Solusi:**
- Periksa kembali password yang dimasukkan
- Pastikan tidak ada spasi di awal/akhir
- Reset password jika lupa

---

### 3. Email dan Password Salah
**Status Code:** 422  
**Response API:**
```json
{
    "message": "Validation failed.",
    "errors": {
        "email": ["Email tidak valid."],
        "password": ["Password tidak valid."]
    }
}
```
**Pesan ke User:**  
> "Email dan password tidak valid. Periksa kembali kredensial Anda."

**Solusi:**
- Periksa kedua field
- Pastikan menggunakan kredensial yang benar

---

### 4. Autentikasi Berhasil
**Status Code:** 200  
**Response API:**
```json
{
    "token_type": "Bearer",
    "expires_in": 86400,
    "access_token": "9cf2dd9d-9c53-4e1db85d7b3fa2217477|..."
}
```
**Pesan ke User:**  
> "Autentikasi berhasil"

**Detail:**
- Token akan di-cache selama (expires_in - 5 menit)
- Token otomatis diperpanjang saat digunakan
- Token Type: Bearer

---

### 5. Server Error (500+)
**Status Code:** 500, 502, 503, etc.  
**Pesan ke User:**  
> "Server API eksternal sedang bermasalah. Silakan coba lagi nanti."

**Solusi:**
- Tunggu beberapa saat
- Coba lagi nanti
- Hubungi administrator jika error berlanjut

---

### 6. Connection Error
**Kondisi:** Network timeout, DNS failure, etc.  
**Pesan ke User:**  
> "Terjadi kesalahan koneksi: [detail error]"

**Solusi:**
- Periksa koneksi internet
- Periksa firewall
- Pastikan URL API benar

---

## Testing Error Handling

### Via Script PHP
```bash
php simple-test.php
```

Script ini akan test 3 skenario:
1. Email tidak terdaftar
2. Email valid, password salah
3. Email dan password dari contoh

### Via Web Interface
1. Login ke aplikasi
2. Akses: http://localhost:8000/api-sync/test-page
3. Klik "Test Auth"
4. Lihat pesan error yang muncul di console browser

### Via Log File
Semua error dicatat di:
```
storage/logs/laravel.log
```

Format log:
```
[timestamp] local.ERROR: External API authentication failed
{
    "status": 422,
    "body": "{...}",
    "parsed_message": "Email tidak terdaftar di sistem..."
}
```

---

## Implementasi di Code

### ExternalApiService.php

Method `authenticate()` sekarang return array:
```php
[
    'success' => true|false,
    'token' => string|null,
    'message' => string
]
```

Method `parseAuthenticationError()` menangani parsing error:
```php
protected function parseAuthenticationError($response)
{
    // Parse status code dan error messages
    // Return pesan yang user-friendly
}
```

### ApiSyncController.php

Update penggunaan:
```php
$authResult = $this->apiService->authenticate();

if (!$authResult['success']) {
    return back()->with('error', $authResult['message']);
}

// Use token: $authResult['token']
```

---

## Best Practices

1. **Selalu tampilkan pesan error yang jelas** kepada user
2. **Log detail error** untuk debugging
3. **Jangan expose sensitive information** di pesan error
4. **Gunakan cache** untuk menghindari request berulang
5. **Handle edge cases** seperti network timeout

---

## Troubleshooting Quick Reference

| Pesan Error | Status | Penyebab | Solusi |
|-------------|--------|----------|--------|
| Email tidak terdaftar | 422 | Email belum terdaftar | Gunakan email valid |
| Password salah | 422 | Password tidak cocok | Periksa password |
| Email dan password tidak valid | 422 | Kedua field salah | Periksa kedua field |
| Server bermasalah | 500+ | Server error | Tunggu dan coba lagi |
| Kesalahan koneksi | - | Network issue | Periksa koneksi |
| Token expired | 401 | Token sudah expired | Auto re-authenticate |

