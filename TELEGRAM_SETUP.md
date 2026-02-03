# Setup Telegram Bot untuk Notifikasi Laporan

## Langkah-langkah Setup

### 1. Buat Bot Telegram Baru

1. Buka Telegram dan cari **@BotFather**
2. Kirim perintah `/newbot`
3. Ikuti instruksi:
   - Masukkan nama bot (contoh: `Hospital Pay Bot`)
   - Masukkan username bot (harus diakhiri dengan 'bot', contoh: `hospitalpay_bot`)
4. Setelah berhasil, BotFather akan memberikan **Bot Token**
   - Contoh: `7503866221:AAFw8wVQI2MClhq9-example_token_here`
   - **SIMPAN TOKEN INI!**

### 2. Dapatkan Chat ID

Ada 2 cara untuk mendapatkan Chat ID:

#### Cara 1: Menggunakan @userinfobot (Paling Mudah)
1. Cari **@userinfobot** di Telegram
2. Klik Start
3. Bot akan mengirimkan info Anda termasuk **Chat ID**
4. Copy angka Chat ID tersebut

#### Cara 2: Manual dari Bot Anda
1. Cari bot Anda yang baru dibuat (contoh: `@hospitalpay_bot`)
2. Klik Start dan kirim pesan apa saja
3. Buka browser dan akses:
   ```
   https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates
   ```
   Ganti `<YOUR_BOT_TOKEN>` dengan token dari BotFather
4. Cari field `"chat":{"id":123456789}` di response JSON
5. Angka `123456789` adalah Chat ID Anda

### 3. Konfigurasi di Laravel

1. Buka file `.env` di root project
2. Isi nilai berikut:
   ```env
   TELEGRAM_BOT_TOKEN=7503866221:AAFw8wVQI2MClhq9-example_token_here
   TELEGRAM_CHAT_ID=123456789
   ```
   
   **Catatan:**
   - `TELEGRAM_BOT_TOKEN`: Token dari BotFather
   - `TELEGRAM_CHAT_ID`: ID chat yang akan menerima notifikasi

3. Save file `.env`

### 4. Test Koneksi

Setelah setup, test koneksi dengan cara:

1. Login ke aplikasi (kasir atau marketing)
2. Akses halaman Test API atau dashboard
3. Klik tombol **"Test Telegram"**
4. Jika berhasil, Anda akan menerima pesan dari bot di Telegram

**Pesan yang akan diterima:**
```
✅ Koneksi Telegram Berhasil!

Bot: @hospitalpay_bot
Nama: Hospital Pay Bot

🏥 Hospital Pay System Ready
```

### 5. Kirim Laporan Manual

Untuk mengirim laporan transaksi harian:

1. Login sebagai **kasir** atau **marketing**
2. Di dashboard, cari tombol **"Kirim Laporan Harian"**
3. Pilih tanggal (default: kemarin)
4. Klik **"Kirim ke Telegram"**
5. Bot akan mengirim laporan berisi:
   - Tanggal laporan
   - Total transaksi
   - File Excel (lampiran)

**Format pesan:**
```
📊 Laporan Transaksi Harian

📅 Tanggal: 02 Februari 2026
💰 Total Transaksi: 15 transaksi

Laporan detail terlampir dalam file Excel.

🏥 Hospital Pay System
```

### 6. Automated Report (Otomatis)

Laporan akan dikirim otomatis setiap hari pukul 01:00 AM melalui scheduler Laravel.

**Setup Cron Job** (untuk production):

Di server, tambahkan cron job:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Test Manual:**
```bash
php artisan report:send-daily-transactions
```

## Troubleshooting

### Error: "Failed to send Telegram notification"

**Kemungkinan penyebab:**
1. **Token salah**: Periksa kembali token di `.env`
2. **Chat ID salah**: Pastikan Chat ID benar
3. **Bot belum di-start**: Buka chat dengan bot dan klik Start
4. **Internet error**: Periksa koneksi internet server

**Solusi:**
- Verifikasi token dengan akses:
  ```
  https://api.telegram.org/bot<YOUR_TOKEN>/getMe
  ```
- Harus return info bot Anda

### Error: "Bad Request: chat not found"

**Penyebab:** Bot belum pernah distart atau Chat ID salah

**Solusi:**
1. Buka Telegram
2. Cari bot Anda
3. Klik **Start**
4. Coba test koneksi lagi

### Laporan tidak dikirim otomatis

**Penyebab:** Scheduler Laravel belum jalan

**Solusi:**
1. **Development:** Jalankan manual:
   ```bash
   php artisan schedule:work
   ```
   
2. **Production:** Setup cron job (lihat #6)

3. **Test manual:**
   ```bash
   php artisan report:send-daily-transactions
   ```

## Keuntungan Telegram vs Email

✅ **Lebih Mudah Setup**
- Tidak perlu konfigurasi SMTP yang rumit
- Tidak perlu email & password
- Cukup token & chat ID

✅ **Lebih Cepat**
- Notifikasi real-time
- File langsung tersedia di Telegram

✅ **Lebih Aman**
- Tidak perlu simpan password email
- Token bisa di-revoke kapan saja

✅ **Lebih Fleksibel**
- Bisa kirim ke group Telegram
- Support multimedia (foto, video, file)
- Support formatting (bold, italic, markdown)

## Tips

1. **Gunakan Group Telegram** untuk tim:
   - Buat group Telegram
   - Invite bot ke group
   - Dapatkan Group Chat ID (angka negatif, contoh: `-123456789`)
   - Update `.env` dengan Group Chat ID

2. **Revoke Token** jika bocor:
   - Kirim `/revoke` ke @BotFather
   - Generate token baru
   - Update `.env`

3. **Notifikasi Custom:**
   - Edit `app/Services/TelegramNotificationService.php`
   - Customize format pesan sesuai kebutuhan

## Command Artisan

```bash
# Send report manual
php artisan report:send-daily-transactions

# Test scheduler
php artisan schedule:list

# Run scheduler (development)
php artisan schedule:work
```

## Support

Jika ada masalah, cek:
1. File log: `storage/logs/laravel.log`
2. Telegram Bot API: https://core.telegram.org/bots/api
3. Package docs: https://telegram-bot-sdk.readme.io/
