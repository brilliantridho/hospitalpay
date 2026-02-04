# 🕐 Setup Scheduler Otomatis untuk Laporan Harian

## 📋 Overview

Laravel Scheduler akan otomatis mengirim laporan transaksi harian ke Telegram setiap jam **01:00 dini hari** (timezone Asia/Jakarta).

**Current Schedule:**
- ⏰ **Waktu**: 01:00 AM (dini hari)
- 📅 **Frekuensi**: Setiap hari
- 📱 **Output**: Telegram notification + Excel file
- 🌍 **Timezone**: Asia/Jakarta (WIB)

---

## 🚀 Quick Start (Windows)

### Option 1: Task Scheduler (Recommended untuk Production)

#### Step 1: Buka Task Scheduler
1. Tekan `Win + R`
2. Ketik `taskschd.msc`
3. Enter

#### Step 2: Create New Task
1. Klik **"Create Task"** (bukan "Create Basic Task")
2. Tab **General**:
   - Name: `Hospital Pay - Laravel Scheduler`
   - Description: `Run Laravel scheduler every minute to check for scheduled tasks`
   - ✅ Check: **"Run whether user is logged on or not"**
   - ✅ Check: **"Run with highest privileges"**
   - Configure for: **Windows 10/11**

#### Step 3: Triggers
1. Tab **Triggers** → New
2. Begin the task: **At startup**
3. Repeat task every: **1 minute**
4. For a duration of: **Indefinitely**
5. ✅ Enabled
6. OK

#### Step 4: Actions
1. Tab **Actions** → New
2. Action: **Start a program**
3. Program/script: `C:\Windows\System32\cmd.exe`
4. Add arguments: `/c "D:\R\hospitalpay\run-scheduler.bat"`
5. Start in: `D:\R\hospitalpay`
6. OK

#### Step 5: Conditions (Optional)
1. Tab **Conditions**
2. ❌ Uncheck: **"Start the task only if the computer is on AC power"**
3. ✅ Check: **"Wake the computer to run this task"**

#### Step 6: Settings
1. Tab **Settings**
2. ✅ Check: **"Allow task to be run on demand"**
3. ✅ Check: **"Run task as soon as possible after a scheduled start is missed"**
4. If the task is already running: **Do not start a new instance**

#### Step 7: Finish
1. Click **OK**
2. Enter Windows password jika diminta
3. Task akan langsung aktif!

---

### Option 2: Manual Testing (Development)

Untuk testing tanpa setup Task Scheduler:

```powershell
# Test sekali
php artisan schedule:run

# Test dengan date spesifik
php artisan test:scheduled-report 2026-02-02

# Test untuk hari ini
php artisan test:scheduled-report today

# Test untuk kemarin
php artisan test:scheduled-report yesterday

# Run scheduler terus-menerus (CTRL+C untuk stop)
while ($true) {
    php artisan schedule:run
    Start-Sleep -Seconds 60
}
```

---

## ⚙️ Mengubah Jadwal

Edit file: [routes/console.php](routes/console.php)

### Contoh Jadwal:

```php
// Jam 1 pagi (default)
Schedule::command('report:send-daily-transactions')
    ->dailyAt('01:00')
    ->timezone('Asia/Jakarta');

// Jam 9 pagi
Schedule::command('report:send-daily-transactions')
    ->dailyAt('09:00')
    ->timezone('Asia/Jakarta');

// Jam 5 sore
Schedule::command('report:send-daily-transactions')
    ->dailyAt('17:00')
    ->timezone('Asia/Jakarta');

// Setiap Senin jam 8 pagi
Schedule::command('report:send-daily-transactions')
    ->weeklyOn(1, '08:00')
    ->timezone('Asia/Jakarta');

// Setiap tanggal 1 jam 12 malam
Schedule::command('report:send-daily-transactions')
    ->monthlyOn(1, '00:00')
    ->timezone('Asia/Jakarta');

// Setiap 5 menit (untuk testing)
Schedule::command('report:send-daily-transactions')
    ->everyFiveMinutes()
    ->timezone('Asia/Jakarta');

// Setiap menit (untuk testing)
Schedule::command('report:send-daily-transactions')
    ->everyMinute()
    ->timezone('Asia/Jakarta');
```

**Setelah edit, tidak perlu restart Task Scheduler!** Laravel akan otomatis baca konfigurasi baru.

---

## 🧪 Testing

### 1. Cek Scheduled Tasks
```powershell
php artisan schedule:list
```

Output:
```
  0 1 * * *  php artisan report:send-daily-transactions ....... Next Due: 10 jam dari sekarang
```

### 2. Test Manual Run
```powershell
# Run scheduler (akan cek apakah ada task yang perlu dijalankan)
php artisan schedule:run

# Run command langsung (bypass scheduler)
php artisan report:send-daily-transactions

# Test dengan date custom
php artisan test:scheduled-report 2026-02-02
```

### 3. Test Scheduler Script
```powershell
# Test bat file
.\run-scheduler.bat

# Test PowerShell file
.\run-scheduler.ps1
```

### 4. Verifikasi Task Scheduler
1. Buka Task Scheduler
2. Cari task: **"Hospital Pay - Laravel Scheduler"**
3. Right-click → **Run** untuk test manual
4. Cek **History** tab untuk melihat log

---

## 📊 Monitoring

### Check Logs
```powershell
# Laravel log
Get-Content storage\logs\laravel.log -Tail 50

# Filter scheduler log
Get-Content storage\logs\laravel.log | Select-String "daily transaction"

# Real-time monitoring
Get-Content storage\logs\laravel.log -Wait
```

### Check Last Run
```powershell
# Di Task Scheduler
# Last Run Time: akan tampil kapan terakhir jalan
# Next Run Time: akan tampil kapan berikutnya
# Last Run Result: 0x0 = success
```

---

## 🚨 Troubleshooting

### Task tidak jalan otomatis?

**1. Cek Task Scheduler History:**
```
Task Scheduler → View → Show History (enable)
→ Cari task → Tab History
```

**2. Cek PHP di PATH:**
```powershell
php --version
# Jika error "command not found", tambahkan PHP ke PATH
```

**3. Test manual:**
```powershell
cd D:\R\hospitalpay
php artisan schedule:run
```

**4. Cek permissions:**
- Task Scheduler harus run dengan user yang punya akses ke folder project
- User harus punya akses write ke `storage/` folder

### Laporan tidak terkirim?

**1. Cek Telegram config:**
```powershell
php artisan test:scheduled-report
```

**2. Cek koneksi internet**

**3. Cek log error:**
```powershell
Get-Content storage\logs\laravel.log -Tail 20
```

**4. Test Telegram bot:**
```powershell
# Via browser
http://localhost:8000 → Login → Test Telegram
```

### Scheduler jalan tapi tidak ada transaksi?

Ini normal! Jika tidak ada transaksi kemarin, sistem akan kirim notifikasi kosong ke Telegram:

```
📊 Laporan Transaksi Harian

📅 Tanggal: 03 Februari 2026
📦 Total Transaksi: 0 transaksi

Tidak ada transaksi pada tanggal ini.

🏥 Hospital Pay System
```

---

## 🎛️ Advanced Options

### Multiple Schedules
```php
// Laporan harian pagi
Schedule::command('report:send-daily-transactions')
    ->dailyAt('09:00')
    ->name('morning-report');

// Laporan mingguan
Schedule::command('report:send-daily-transactions')
    ->weeklyOn(1, '08:00')
    ->name('weekly-report');
```

### Error Notifications
```php
Schedule::command('report:send-daily-transactions')
    ->dailyAt('01:00')
    ->onFailure(function () {
        // Send notification on failure
        Log::error('Daily report failed to send');
    })
    ->onSuccess(function () {
        Log::info('Daily report sent successfully');
    });
```

### Skip on Weekends
```php
Schedule::command('report:send-daily-transactions')
    ->dailyAt('01:00')
    ->weekdays(); // Senin-Jumat saja
```

### Run on Specific Days
```php
Schedule::command('report:send-daily-transactions')
    ->dailyAt('01:00')
    ->days([1, 2, 3, 4, 5]); // Senin-Jumat (1=Senin, 7=Minggu)
```

---

## 📝 Best Practices

1. **Test dulu sebelum production:**
   ```powershell
   php artisan test:scheduled-report
   ```

2. **Monitor log secara berkala:**
   ```powershell
   Get-Content storage\logs\laravel.log -Tail 50
   ```

3. **Backup database sebelum update scheduler**

4. **Set timezone yang benar:**
   ```php
   ->timezone('Asia/Jakarta')
   ```

5. **Gunakan `everyMinute()` hanya untuk testing:**
   - Production: `dailyAt('01:00')`
   - Testing: `everyMinute()` atau `everyFiveMinutes()`

---

## ✅ Checklist Setup

Setelah setup, pastikan:

- [ ] Task Scheduler sudah dibuat dan enabled
- [ ] Task jalan setiap menit (cek Last Run Time)
- [ ] PHP accessible di command line (`php --version`)
- [ ] Folder `storage/logs/` writeable
- [ ] Telegram bot config sudah benar
- [ ] Test manual berhasil: `php artisan test:scheduled-report`
- [ ] Log tidak ada error: `storage/logs/laravel.log`
- [ ] Notifikasi Telegram diterima saat test

---

## 🎉 Done!

Scheduler sudah aktif dan akan otomatis kirim laporan setiap jam 01:00 dini hari ke Telegram!

**Next Day Check:**
Besok pagi, cek Telegram apakah laporan kemarin sudah masuk otomatis. 📱✨

---

**Last Updated:** February 3, 2026  
**Schedule Time:** 01:00 AM WIB Daily  
**Notification:** Telegram @masohdir
