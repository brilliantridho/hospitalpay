# 🚀 Quick Setup - Telegram Bot

Panduan singkat untuk setup notifikasi Telegram dalam 5 menit.

## Step 1: Buat Bot (2 menit)

1. **Buka Telegram**, cari `@BotFather`
2. Kirim `/newbot`
3. Nama bot: `Hospital Pay Bot`
4. Username: `hospitalpay_bot` (harus diakhiri 'bot')
5. **Copy token** yang diberikan
   ```
   Contoh: 7503866221:AAFw8wVQI2MClhq9-example
   ```

## Step 2: Dapatkan Chat ID (1 menit)

**Cara termudah:**
1. Cari `@userinfobot` di Telegram
2. Klik Start
3. **Copy ID** yang ditampilkan
   ```
   Contoh: 123456789
   ```

**Atau:**
1. Cari bot Anda (contoh: `@hospitalpay_bot`)
2. Klik Start, kirim pesan apa saja
3. Buka: `https://api.telegram.org/bot<TOKEN>/getUpdates`
4. Cari `"chat":{"id":123456789}`

## Step 3: Konfigurasi (1 menit)

Buka file `.env`, tambahkan:

```env
TELEGRAM_BOT_TOKEN=7503866221:AAFw8wVQI2MClhq9-example
TELEGRAM_CHAT_ID=123456789
```

Ganti dengan token & chat ID Anda!

## Step 4: Test (1 menit)

1. Login ke aplikasi
2. Dashboard → Klik tombol **"🔔 Test Telegram"**
3. Cek Telegram, harus ada pesan:
   ```
   ✅ Koneksi Telegram Berhasil!
   
   Bot: @hospitalpay_bot
   Nama: Hospital Pay Bot
   
   🏥 Hospital Pay System Ready
   ```

## ✅ Selesai!

Sekarang Anda bisa:
- ✅ Test koneksi: Tombol "Test Telegram"
- ✅ Kirim laporan manual: Tombol "Kirim Telegram"
- ✅ Laporan otomatis: Setiap hari jam 01:00 AM

## 📖 Dokumentasi Lengkap

Lihat file [TELEGRAM_SETUP.md](TELEGRAM_SETUP.md) untuk:
- Setup untuk group Telegram
- Troubleshooting
- Automated scheduler
- Customisasi format pesan

## ⚡ Tips

**Kirim ke Group Telegram:**
1. Buat group Telegram
2. Invite bot ke group
3. Dapatkan Group Chat ID (angka negatif)
4. Update `.env` dengan Group Chat ID

**Revoke Token jika bocor:**
1. Kirim `/revoke` ke @BotFather
2. Generate token baru
3. Update `.env`
