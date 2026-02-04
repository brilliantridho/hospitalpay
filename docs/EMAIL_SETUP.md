# Email Configuration Guide

## Development/Testing - Menggunakan LOG Driver (Default Sekarang)

File `.env` sudah dikonfigurasi untuk menggunakan `log` driver:

```env
MAIL_MAILER=log
```

Dengan konfigurasi ini:
- ✅ Tidak perlu setup SMTP
- ✅ Email disimpan di `storage/logs/laravel.log`
- ✅ File Excel tetap digenerate di `storage/app/reports/`
- ✅ Cocok untuk testing dan development

### Cara Testing:
1. Klik button **"📧 Kirim Email"** di dashboard
2. Cek file `storage/logs/laravel.log` untuk melihat email yang "dikirim"
3. File Excel tersimpan di `storage/app/reports/transactions_YYYY-MM-DD.xlsx`

---

## Production - Menggunakan Gmail SMTP

Untuk production dengan Gmail, ikuti langkah berikut:

### Step 1: Enable 2-Step Verification di Google Account

1. Login ke Google Account: https://myaccount.google.com/
2. Pilih **Security** di menu kiri
3. Cari **2-Step Verification**
4. Klik **Get started** dan ikuti instruksi
5. Selesaikan setup dengan nomor HP Anda

### Step 2: Generate App Password

1. Setelah 2-Step Verification aktif, kembali ke **Security**
2. Scroll ke bawah, cari **App passwords**
3. Klik **App passwords**
4. Pilih:
   - **App**: Mail
   - **Device**: Windows Computer (atau pilihan lain)
5. Klik **Generate**
6. **COPY** 16-digit password yang muncul (contoh: `abcd efgh ijkl mnop`)
7. Password ini yang akan digunakan, **BUKAN** password Google Anda

### Step 3: Update .env

Edit file `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=abcdefghijklmnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="Hospital Cashier System"
```

**PENTING:**
- Gunakan **App Password** (16 digit tanpa spasi), bukan password biasa
- `MAIL_USERNAME` = email lengkap (xxx@gmail.com)
- `MAIL_ENCRYPTION` harus `tls`

### Step 4: Clear Cache & Test

```bash
php artisan config:clear
php artisan cache:clear
```

Kemudian test lagi button "Kirim Email" di dashboard.

---

## Alternative - Menggunakan Mailtrap (Recommended untuk Development)

Mailtrap adalah fake SMTP server untuk testing email tanpa mengirim ke email sungguhan.

### Setup Mailtrap:

1. Register di https://mailtrap.io (gratis)
2. Buat inbox baru
3. Copy credentials yang diberikan
4. Update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@hospital.com
MAIL_FROM_NAME="Hospital Cashier System"
```

5. Semua email akan masuk ke inbox Mailtrap (bisa lihat di web dashboard)

---

## Troubleshooting

### Error: "Failed to authenticate on SMTP server"
**Solusi:**
- Pastikan 2-Step Verification sudah aktif
- Generate App Password baru dan gunakan yang baru
- Hapus semua spasi dari App Password
- Pastikan `MAIL_ENCRYPTION=tls` (bukan `ssl`)

### Error: "Connection timeout"
**Solusi:**
- Cek firewall/antivirus tidak memblok port 587
- Coba ganti `MAIL_PORT=465` dan `MAIL_ENCRYPTION=ssl`
- Pastikan koneksi internet stabil

### Error: "Less secure app access"
**Solusi:**
- Google sudah tidak support "Less secure app" sejak 2022
- **HARUS** menggunakan App Password, tidak ada cara lain
- Aktifkan 2-Step Verification terlebih dahulu

### Testing Email Configuration

Run command ini untuk test email config:

```bash
php artisan tinker
```

Kemudian:

```php
Mail::raw('Test email', function($msg) {
    $msg->to('test@example.com')->subject('Test');
});
```

Jika berhasil, tidak ada error. Cek log atau inbox tergantung driver yang digunakan.

---

## Rekomendasi per Environment

| Environment | Driver | Alasan |
|------------|--------|--------|
| Development/Local | `log` atau `mailtrap` | Tidak perlu setup SMTP, mudah debug |
| Staging | `mailtrap` | Email tidak terkirim ke user sungguhan |
| Production | `smtp` (Gmail/SendGrid) | Email terkirim sungguhan ke user |

---

## Switching Between Configurations

### Mode LOG (Current):
```env
MAIL_MAILER=log
```

### Mode SMTP Gmail:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

### Mode Mailtrap:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_user
MAIL_PASSWORD=your_mailtrap_pass
MAIL_ENCRYPTION=tls
```

Setelah ubah, selalu jalankan:
```bash
php artisan config:clear
```
