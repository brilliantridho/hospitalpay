# ⚠️ Troubleshooting: "Bad Request: chat not found"

## Masalah
Saat kirim laporan ke Telegram, muncul error:
```
Error: Bad Request: chat not found
```

## Penyebab
Chat ID di `.env` salah atau bot belum pernah menerima pesan dari Anda.

## Solusi Cepat (Pilih salah satu)

### Cara 1: Menggunakan Command Helper (Termudah!)

1. **Buka Telegram**, cari bot Anda: `@masbrill_bot`
2. Klik **Start** dan kirim pesan: `hello`
3. **Jalankan command:**
   ```bash
   php artisan telegram:get-chat-id
   ```
4. **Copy Chat ID** yang ditampilkan
5. **Update `.env`:**
   ```env
   TELEGRAM_CHAT_ID=1234567890
   ```
6. **Reload config:**
   ```bash
   php artisan config:clear
   ```

### Cara 2: Menggunakan @userinfobot

1. Buka Telegram, cari `@userinfobot`
2. Klik **Start**
3. Bot akan kirim info Anda, copy **Id** (angka)
4. **PENTING:** Buka bot Anda `@masbrill_bot`, klik **Start** dan kirim pesan
5. Update `.env` dengan Chat ID tersebut
6. Reload config: `php artisan config:clear`

### Cara 3: Manual via Browser

1. Buka bot `@masbrill_bot` di Telegram
2. Klik **Start** dan kirim pesan `hello`
3. Buka browser, akses:
   ```
   https://api.telegram.org/bot8538759033:AAEMAVeqjnuqW5-cQfo0N64p66qZ_m15mY4/getUpdates
   ```
4. Cari `"chat":{"id":1234567890}` di response JSON
5. Copy angka Chat ID tersebut
6. Update `.env` dan reload config

## Verifikasi Chat ID Benar

Setelah update Chat ID, test dengan command:

```bash
php artisan telegram:get-chat-id
```

**Output yang benar:**
```
Found messages:

Chat ID: 1234567890
From: Your Name
Message: hello
---
```

## Test Koneksi

Setelah Chat ID benar, test kirim pesan:

```bash
# Via aplikasi: Dashboard → "🔔 Test Telegram"
```

Atau manual via tinker:
```bash
php artisan tinker
>>> use Telegram\Bot\Laravel\Facades\Telegram;
>>> $chatId = config('telegram.bots.default.chat_id');
>>> Telegram::sendMessage(['chat_id' => $chatId, 'text' => '✅ Test berhasil!']);
>>> exit
```

## Kesalahan Umum

### ❌ Chat ID dari @userinfobot tapi tidak start bot
**Solusi:** Harus buka `@masbrill_bot` dan klik Start + kirim pesan

### ❌ Chat ID menggunakan angka negatif untuk personal chat
**Solusi:** Personal chat ID selalu positif (contoh: 1868709569)
Angka negatif hanya untuk group (contoh: -1001234567890)

### ❌ Copy Chat ID dengan spasi atau karakter lain
**Solusi:** Hanya angka, tidak ada spasi/tanda lain

### ❌ Lupa reload config setelah update .env
**Solusi:** Selalu jalankan `php artisan config:clear` setelah edit `.env`

## Periksa File Reports

Jika error tentang file Excel:
```bash
# Buat folder reports jika belum ada
mkdir storage/app/reports

# Verifikasi folder exists
ls storage/app/reports
```

## Command Berguna

```bash
# Get Chat ID
php artisan telegram:get-chat-id

# Test bot connection
php artisan tinker
>>> use Telegram\Bot\Laravel\Facades\Telegram;
>>> Telegram::getMe();

# Clear config cache
php artisan config:clear

# Send test report
# Dashboard → Kirim Telegram
```

## Masih Error?

1. Periksa token benar di `.env`
2. Pastikan bot tidak di-block oleh user
3. Pastikan bot tidak di-delete/revoke
4. Check logs: `storage/logs/laravel.log`

## Contact Support

- Bot Username: @masbrill_bot
- Token (first 10 chars): 8538759033...
- Current Chat ID di .env: 1868709569

Jika masih ada masalah, screenshot error dan kirim!
