# 🚨 SOLUSI: Laporan Tidak Terkirim

## ❌ Masalah yang Ditemukan

Task Scheduler **TIDAK BERJALAN** dengan benar karena:
- Trigger tidak dikonfigurasi dengan benar (Repeat: Every: N/A)
- Last Run Time: 11/30/1999 (tidak pernah berjalan)
- Last Result: 267011 (error code)

Meskipun command manual **BERHASIL** (`php artisan report:send-daily-transactions`), scheduler otomatis tidak pernah berjalan.

---

## ✅ Solusi

### Cara 1: Script BAT (PALING MUDAH - RECOMMENDED)

1. **Klik kanan** pada file [fix-scheduler.bat](fix-scheduler.bat)
2. **Pilih "Run as administrator"**
3. Tunggu proses selesai
4. ✅ Done!

### Cara 2: PowerShell (Alternatif)

1. **Buka PowerShell sebagai Administrator**:
   - Klik kanan pada tombol Start
   - Pilih "Windows PowerShell (Admin)" atau "Terminal (Admin)"

2. **Jalankan**:
   ```powershell
   cd D:\R\hospitalpay
   .\fix-scheduler.ps1
   ```

### Cara 3: Manual via Task Scheduler GUI

### Cara 3: Manual via Task Scheduler

Jika script tidak bisa dijalankan, lakukan manual:

1. **Buka Task Scheduler**:
   - Tekan `Win + R`
   - Ketik `taskschd.msc`
   - Enter

2. **Hapus task lama**:
   - Cari "HospitalPay-Scheduler"
   - Klik kanan → Delete

3. **Buat task baru**:
   - Klik "Create Task" (bukan "Create Basic Task")
   
   **Tab General**:
   - Name: `HospitalPay-Scheduler`
   - Description: `Hospital Pay Auto Scheduler`
   - ✅ Run whether user is logged on or not
   - ✅ Run with highest privileges
   
   **Tab Triggers**:
   - New → Begin the task: **At startup**
   - ✅ Enabled
   - Advanced settings:
     - ✅ Repeat task every: **1 minute**
     - ✅ For a duration of: **Indefinitely**
   - OK
   
   **Tab Actions**:
   - New → Action: **Start a program**
   - Program/script: `cmd.exe`
   - Add arguments: `/c D:\R\hospitalpay\run-scheduler.bat`
   - Start in: `D:\R\hospitalpay`
   - OK
   
   **Tab Conditions**:
   - ❌ UNCHECK: "Start the task only if the computer is on AC power"
   - ❌ UNCHECK: "Stop if the computer switches to battery power"
   - OK
   
   **Tab Settings**:
   - ✅ Allow task to be run on demand
   - ✅ Run task as soon as possible after a scheduled start is missed
   - ✅ If the task fails, restart every: **1 minute**
   - OK

4. **Test task**:
   - Klik kanan pada task → Run
   - Tunggu 1 menit
   - Refresh → Cek "Last Run Time" harus terisi

---4: Manual via Command Prompt (Tercepat untuk Manual

### Cara 3: Manual via PowerShell (Alternatif)

Salin dan paste command ini di **Command Prompt (Admin)**:

```cmd
schtasks /Delete /TN "HospitalPay-Scheduler" /F
schtasks /Create /TN "HospitalPay-Scheduler" /TR "cmd /c D:\R\hospitalpay\run-scheduler.bat" /SC MINUTE /MO 1 /ST 00:00 /F /RL HIGHEST
schtasks /Run /TN "HospitalPay-Scheduler"
```

---

## 🔍 Verifikasi

Setelah memperbaiki, verifikasi dengan cara:

### 1. Cek Task Scheduler berjalan:
```powershell
Get-ScheduledTask -TaskName "HospitalPay-Scheduler" | Get-ScheduledTaskInfo
```

Harusnya menampilkan:
- ✅ **LastRunTime**: waktu terbaru (bukan 11/30/1999)
- ✅ **LastTaskResult**: 0 (sukses)
- ✅ **NextRunTime**: 1 menit dari sekarang

### 2. Cek schedule Laravel:
```bash
php artisan schedule:list
```

Output:
```
10 16 * * * php artisan report:send-daily-transactions ... Next Due: XX jam dari sekarang
```

### 3. Test manual (pastikan berhasil):
```bash
php artisan report:send-daily-transactions
```

Output:
```
Daily transaction report sent successfully to Telegram!
```

### 4. Cek log:
```bash
Get-Content storage\logs\laravel.log -Tail 20
```

Tidak boleh ada error tentang Telegram atau schedule.

---

## 📊 Kapan Laporan Dikirim?

Berdasarkan [console.php](routes/console.php#L12):
- **Waktu**: Setiap hari jam **16:10 WIB** (4:10 PM)
- **Konten**: Transaksi hari kemarin
- **Format**: Pesan Telegram + File Excel
- **Kondisi**: Hanya dikirim jika ada transaksi

---

## 🧪 Test Pengiriman Manual

Jika ingin test sekarang tanpa menunggu jam 16:10:

```bash
# Test dengan tanggal kemarin
php artisan report:send-daily-transactions

# Test dengan tanggal spesifik
php artisan test:scheduled-report 2026-02-02
```

---

## 📝 Troubleshooting

### Task Scheduler masih tidak jalan?

1. **Cek Event Viewer**:
   ```
   Win + R → eventvwr.msc → Windows Logs → Application
   Cari error dari "Task Scheduler"
   ```

2. **Cek permission**:
   - Task harus run as Administrator
   - User harus punya permission untuk run scheduled tasks

3. **Cek service**:
   ```powershell
   Get-Service -Name "Schedule" | Select-Object Status
   ```
   Harus **Running**. Jika tidak:
   ```powershell
   Start-Service -Name "Schedule"
   ```

### Telegram tidak menerima laporan?

1. **Cek config Telegram**:
   ```bash
   php artisan tinker
   >>> config('telegram.bots.default.token')
   >>> config('telegram.bots.default.chat_id')
   ```

2. **Test koneksi Telegram**:
   ```bash
   php artisan telegram:test
   ```

3. **Cek file .env**:
   ```env
   TELEGRAM_BOT_TOKEN=your_bot_token
   TELEGRAM_CHAT_ID=your_chat_id
   ```

---

## 📚 File Terkait

- [routes/console.php](routes/console.php) - Konfigurasi schedule
- [app/Console/Commands/SendDailyTransactionReport.php](app/Console/Commands/SendDailyTransactionReport.php) - Command laporan
- [app/Services/TelegramNotificationService.php](app/Services/TelegramNotificationService.php) - Service Telegram
- [run-scheduler.bat](run-scheduler.bat) - Script yang dijalankan Task Scheduler
- [fix-scheduler.ps1](fix-scheduler.ps1) - Script perbaikan otomatis

---

## 💡 Tips

1. **Jangan ubah waktu schedule** di console.php tanpa restart Task Scheduler
2. **Monitor log** setiap hari untuk memastikan laporan terkirim
3. **Backup database** sebelum melakukan perubahan
4. **Test manual** setelah setiap perubahan konfigurasi

---

## ✅ Checklist

- [ ] Task Scheduler sudah dibuat ulang dengan trigger yang benar
- [ ] LastRunTime tidak lagi 11/30/1999
- [ ] Command manual berhasil mengirim ke Telegram
- [ ] Schedule Laravel menampilkan waktu next run yang benar
- [ ] File Excel terbentuk di storage/app/reports/
- [ ] Telegram menerima pesan + file

---

**Status Saat Ini**: ⚠️ Task Scheduler TIDAK BERJALAN
**Solusi**: Jalankan `fix-scheduler.ps1` sebagai Administrator
