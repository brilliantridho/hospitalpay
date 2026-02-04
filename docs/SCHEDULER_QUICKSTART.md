# 🚀 Quick Setup - Scheduler Otomatis

## Pilih Salah Satu:

### ⚡ Option 1: Auto Setup (Termudah)
```powershell
# Run as Administrator
PowerShell -ExecutionPolicy Bypass -File setup-scheduler.ps1
```

### 🔧 Option 2: Manual Setup
1. Buka Task Scheduler: `Win + R` → `taskschd.msc`
2. Create Task → Ikuti panduan di [SCHEDULER_SETUP.md](SCHEDULER_SETUP.md)

---

## ✅ Verify Setup

```powershell
# Cek scheduled tasks
php artisan schedule:list

# Test manual
php artisan test:scheduled-report

# Test dengan date spesifik
php artisan test:scheduled-report 2026-02-02
```

---

## 📋 Current Schedule

- ⏰ **Waktu**: 01:00 AM (dini hari)
- 📅 **Frekuensi**: Setiap hari
- 📱 **Output**: Telegram @masohdir
- 🌍 **Timezone**: Asia/Jakarta (WIB)

---

## 🎯 Ubah Jadwal

Edit [routes/console.php](routes/console.php):

```php
// Ganti jam 9 pagi
Schedule::command('report:send-daily-transactions')
    ->dailyAt('09:00')
    ->timezone('Asia/Jakarta');
```

**No restart needed!** Langsung aktif.

---

## 🧪 Commands

```powershell
# List schedule
php artisan schedule:list

# Test report
php artisan test:scheduled-report

# Run scheduler manually
php artisan schedule:run

# Send report langsung
php artisan report:send-daily-transactions
```

---

## 📖 Full Documentation

- Setup lengkap: [SCHEDULER_SETUP.md](SCHEDULER_SETUP.md)
- Telegram setup: [TELEGRAM_QUICKSTART.md](TELEGRAM_QUICKSTART.md)
- Insurance discount: [INSURANCE_DISCOUNT_GUIDE.md](INSURANCE_DISCOUNT_GUIDE.md)

---

**Done!** 🎉 Scheduler akan kirim laporan otomatis jam 01:00 setiap hari.
