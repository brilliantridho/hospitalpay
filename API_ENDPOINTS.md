# API Endpoints Testing Guide

## Endpoint Summary

Berdasarkan dokumentasi API RS Delta Surya, berikut adalah endpoint yang tersedia:

### 1. Autentikasi (POST)
**Endpoint:** `POST https://recruitment.rsdeltasurya.com/api/v1/auth`  
**Method:** POST  
**Headers:** `Content-Type: application/json`  
**Body:**
```json
{
    "email": "your-email@rsdeltasurya.com",
    "password": "your-password"
}
```
**Response:**
```json
{
    "token_type": "Bearer",
    "expires_in": 86400,
    "access_token": "token-string"
}
```

---

### 2. Daftar Asuransi (GET)
**Endpoint:** `GET https://recruitment.rsdeltasurya.com/api/v1/insurances`  
**Method:** GET  
**Headers:** `Authorization: Bearer {token}`  
**Response:** Array of insurances

**cURL Example:**
```bash
curl --location 'https://recruitment.rsdeltasurya.com/api/v1/insurances' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

---

### 3. Daftar Tindakan Medis (GET)
**Endpoint:** `GET https://recruitment.rsdeltasurya.com/api/v1/procedures`  
**Method:** GET  
**Headers:** `Authorization: Bearer {token}`  
**Response:** Array of medical procedures

**cURL Example:**
```bash
curl --location 'https://recruitment.rsdeltasurya.com/api/v1/procedures' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**⚠️ CATATAN:** Endpoint yang benar adalah `/procedures` bukan `/medical-services`

---

### 4. Daftar Harga Tindakan Medis (GET)
**Endpoint:** `GET https://recruitment.rsdeltasurya.com/api/v1/procedures/{procedureId}/prices`  
**Method:** GET  
**Headers:** `Authorization: Bearer {token}`  
**Response:** Array of prices for specific procedure

**cURL Example:**
```bash
curl --location 'https://recruitment.rsdeltasurya.com/api/v1/procedures/019c125d-a359-7156-99cd-e188ff48294c/prices' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

---

## Perbedaan Method

### POST vs GET

| Endpoint | Method | Alasan |
|----------|--------|--------|
| `/auth` | **POST** | Mengirim kredensial (email & password) di request body |
| `/insurances` | **GET** | Mengambil data, autentikasi via Bearer token di header |
| `/procedures` | **GET** | Mengambil data, autentikasi via Bearer token di header |
| `/procedures/{id}/prices` | **GET** | Mengambil data, autentikasi via Bearer token di header |

**Penjelasan:**
- **POST** digunakan untuk autentikasi karena mengirim data sensitif (email & password) di request body
- **GET** digunakan untuk mengambil data karena hanya membaca, autentikasi menggunakan Bearer token di header

---

## Testing dengan Script

### 1. Test Autentikasi Saja
```bash
php simple-test.php
```

### 2. Test Semua Endpoint
```bash
php test-all-endpoints.php
```

**Catatan:** Anda perlu menggunakan **email dan password yang valid** di `.env`:
```env
EXTERNAL_API_EMAIL=your-valid-email@rsdeltasurya.com
EXTERNAL_API_PASSWORD=your-valid-password
```

---

## Implementasi di Code

### ExternalApiService.php

```php
// ✅ Authentication - POST
$response = Http::withHeaders([
    'Content-Type' => 'application/json',
])->post($this->baseUrl . '/auth', [
    'email' => $this->email,
    'password' => $this->password,
]);

// ✅ Get Insurances - GET with Bearer token
$response = Http::withToken($token)
    ->get($this->baseUrl . '/insurances');

// ✅ Get Procedures - GET with Bearer token
$response = Http::withToken($token)
    ->get($this->baseUrl . '/procedures');

// ✅ Get Procedure Prices - GET with Bearer token
$response = Http::withToken($token)
    ->get($this->baseUrl . '/procedures/' . $procedureId . '/prices');
```

### Bearer Token

Bearer token adalah standar autentikasi untuk API REST:
- Token dikirim di header `Authorization: Bearer {token}`
- Token didapat dari proses autentikasi (POST `/auth`)
- Token valid selama periode `expires_in` (default 86400 detik = 24 jam)
- Jika token expired, akan mendapat response 401 dan perlu autentikasi ulang

---

## Flow Penggunaan API

```
1. POST /auth 
   → Get access_token

2. GET /insurances (with Bearer token)
   → Get list of insurances

3. GET /procedures (with Bearer token)
   → Get list of procedures
   
4. GET /procedures/{id}/prices (with Bearer token)
   → Get prices for specific procedure
```

---

## Error Handling

| Status Code | Meaning | Action |
|-------------|---------|--------|
| 200 | Success | Process data |
| 401 | Unauthorized | Re-authenticate (token expired) |
| 422 | Validation Error | Check email/password |
| 500+ | Server Error | Wait and retry |

---

## Testing Checklist

- [ ] Test authentication with valid credentials
- [ ] Verify token is received and cached
- [ ] Test GET insurances with Bearer token
- [ ] Test GET procedures with Bearer token
- [ ] Test GET procedure prices with Bearer token and valid procedure ID
- [ ] Test token expiration and re-authentication
- [ ] Test error handling for invalid credentials
- [ ] Test error handling for expired token

