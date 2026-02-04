# Testing Voucher Functionality

## ✅ Perbaikan yang Sudah Dilakukan

### 1. JavaScript Fixes
- ✅ Fixed: JavaScript error dari `add-service` button yang tidak ada
- ✅ Fixed: Duplicate event listeners untuk voucher input
- ✅ Fixed: Sequence logic untuk disable insurance
- ✅ Added: Console logging untuk debugging

### 2. Backend Fixes
- ✅ Fixed: Null check untuk `insurance_name` di response
- ✅ Added: `max_discount` ke response voucher
- ✅ Added: Better error handling

### 3. Debugging Tools Added
- ✅ Test voucher page: `/kasir/test-voucher`
- ✅ Console logs di browser untuk tracking
- ✅ Error messages yang lebih detail

## 🧪 Cara Test Voucher

### Method 1: Via Test Page
1. Login sebagai user **kasir** 
2. Buka: `http://127.0.0.1:8000/kasir/test-voucher`
3. Gunakan voucher code:
   - `NEWYEAR2026`
   - `WELCOME100`
   - `RAMADAN50`
   - `ASURAN2026`
4. Klik "Check Voucher"
5. Lihat hasil di halaman dan console browser (F12)

### Method 2: Via Transaction Create Page
1. Login sebagai user **kasir**
2. Buka: `http://127.0.0.1:8000/kasir/transactions/create`
3. Pilih tindakan medis dan qty
4. Scroll ke bagian "Kode Voucher"
5. Input voucher code (otomatis uppercase)
6. Klik "Cek Voucher"
7. Lihat hasil dan check browser console (F12)

## 🔍 Debugging Steps

### Check Browser Console
1. Buka Developer Tools (F12)
2. Go to Console tab
3. Coba klik "Cek Voucher"
4. Lihat output:
   ```javascript
   Checking voucher: NEWYEAR2026 Subtotal: 100000
   Response status: 200
   Voucher check result: {valid: true, ...}
   ```

### Common Issues & Solutions

#### Issue 1: "Cannot read property 'addEventListener' of null"
**Cause**: Element tidak ditemukan di DOM
**Solution**: ✅ Fixed - added null check

#### Issue 2: CSRF Token Mismatch
**Symptoms**: HTTP 419 error
**Solution**: 
```bash
php artisan config:clear
php artisan cache:clear
```
Refresh browser dan login ulang

#### Issue 3: Route not found (404)
**Solution**:
```bash
php artisan route:clear
php artisan route:list | Select-String "check-voucher"
```

#### Issue 4: Voucher tidak muncul
**Check database**:
```bash
php artisan tinker --execute="print_r(App\Models\Voucher::select('code', 'is_active')->get()->toArray());"
```

### Network Tab Check
1. Buka Developer Tools (F12)
2. Go to Network tab
3. Klik "Cek Voucher"
4. Cari request ke `check-voucher`
5. Check:
   - Status Code: should be 200
   - Request Headers: X-CSRF-TOKEN present
   - Request Payload: code, subtotal, insurance_id
   - Response: JSON dengan valid/message

## 📋 Testing Checklist

- [ ] JavaScript tidak error di console
- [ ] Button "Cek Voucher" bisa diklik
- [ ] Request ke `/kasir/transactions/check-voucher` berhasil (200)
- [ ] Response JSON valid
- [ ] Voucher valid message muncul
- [ ] Insurance field disabled saat voucher valid
- [ ] Discount amount terhitung dengan benar
- [ ] Bisa create transaksi dengan voucher
- [ ] Voucher usage_count bertambah setelah transaksi

## 🚀 Next Steps

If still not working:
1. Check browser console untuk error
2. Check Network tab untuk HTTP request/response
3. Test via `/kasir/test-voucher` page
4. Check Laravel logs: `storage/logs/laravel.log`
5. Pastikan sudah login sebagai user dengan role "kasir"

## 📞 Debug Commands

```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Check routes
php artisan route:list | Select-String "voucher"

# Check vouchers in DB
php artisan tinker --execute="App\Models\Voucher::all(['code', 'is_active'])"

# Check user role
php artisan tinker --execute="App\Models\User::where('email', 'kasir@test.com')->first()->role"
```
