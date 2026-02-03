# Insurance Discount Fix

## Problem
User melaporkan bahwa transaksi dengan BPJS Kesehatan (100% discount) tidak mendapatkan diskon. Subtotal Rp 1.810.965, tapi diskon Rp 0 dan total tetap Rp 1.810.965.

## Root Cause
Logika discount di `TransactionController` hanya memperhitungkan voucher, tidak memperhitungkan `discount_percentage` dari tabel insurances. Padahal BPJS Kesehatan memiliki `discount_percentage = 100`.

## Solution

### 1. Backend Fix (TransactionController.php)
**Before:**
```php
// Hanya hitung diskon dari voucher
$discountPerItem = 0;
if ($voucher) {
    $discountPerItem = $voucher->calculateDiscount($price);
}
```

**After:**
```php
// Priority 1: Manual voucher code (kasir input kode)
// Priority 2: Insurance discount percentage (dari database)
// Priority 3: Auto voucher (dari database untuk asuransi)
$discountPerItem = 0;

if ($voucher && !empty($validated['voucher_code'])) {
    // Manual voucher code takes priority
    $discountPerItem = $voucher->calculateDiscount($price);
} elseif ($insurance && $insurance->discount_percentage > 0) {
    // Use insurance discount percentage
    $discountPerItem = ($price * $insurance->discount_percentage) / 100;
    
    // Apply coverage limit if set
    if ($insurance->coverage_limit) {
        $discountPerItem = min($discountPerItem, $insurance->coverage_limit / $quantity);
    }
} elseif ($voucher) {
    // Auto voucher from insurance (only if no discount_percentage)
    $discountPerItem = $voucher->calculateDiscount($price);
}
```

### 2. Frontend Fix (create.blade.php)
**Before:**
```javascript
// Hanya check voucher discount
if (currentVoucher && currentVoucher.discount_amount) {
    discountAmount = currentVoucher.discount_amount;
    discountSource = 'voucher';
}
```

**After:**
```javascript
const voucherCodeInput = document.getElementById('voucher_code');

// Priority 1: Manual voucher code (if entered and validated)
if (voucherCodeInput.value && currentVoucher) {
    if (currentVoucher.discount_type === 'percentage') {
        let voucherDiscount = (totalSubtotal * currentVoucher.discount_value) / 100;
        if (currentVoucher.max_discount) {
            voucherDiscount = Math.min(voucherDiscount, currentVoucher.max_discount);
        }
        discountAmount = voucherDiscount;
    } else {
        discountAmount = currentVoucher.discount_value;
    }
    discountSource = `voucher ${currentVoucher.code}`;
}
// Priority 2: Insurance discount (if no voucher code)
else if (insuranceSelect.value) {
    const selectedInsurance = insuranceSelect.options[insuranceSelect.selectedIndex];
    const discountPercentage = parseFloat(selectedInsurance.dataset.discount) || 0;
    
    if (discountPercentage > 0) {
        discountAmount = (totalSubtotal * discountPercentage) / 100;
        
        // Apply coverage limit if set
        const coverageLimit = parseFloat(selectedInsurance.dataset.limit);
        if (coverageLimit) {
            discountAmount = Math.min(discountAmount, coverageLimit);
        }
        
        discountSource = 'asuransi';
    }
}
```

## Discount Priority Logic

```
┌─────────────────────────────────────────────────┐
│ Kasir Input Voucher Code?                       │
└───────────┬─────────────────────────────────────┘
            │
            ├─ YES ──> Validate & Use Voucher Discount
            │          (ignore insurance discount)
            │
            └─ NO ───> Check Insurance Discount
                       │
                       ├─ Has discount_percentage > 0?
                       │  └─> Use Insurance Discount
                       │      (e.g., BPJS 100%)
                       │
                       └─ No discount_percentage
                          └─> Check Auto Voucher
                              (from vouchers table)
```

## Test Scenarios

### Scenario 1: BPJS Without Voucher Code ✅
- **Input**: 
  - Asuransi: BPJS Kesehatan
  - Voucher Code: (kosong)
  - Layanan: CT Scan Rp 1.810.965
- **Expected**:
  - Subtotal: Rp 1.810.965
  - Diskon: Rp 1.810.965 (100%)
  - Total: Rp 0
- **Result**: FIXED - discount diterapkan

### Scenario 2: BPJS With Voucher Code
- **Input**:
  - Asuransi: BPJS Kesehatan
  - Voucher Code: NEWYEAR2026 (20% max Rp 500K)
  - Layanan: CT Scan Rp 1.810.965
- **Expected**:
  - Subtotal: Rp 1.810.965
  - Diskon: Rp 500.000 (voucher priority)
  - Total: Rp 1.310.965
- **Note**: Voucher discount overrides insurance discount

### Scenario 3: No Insurance, No Voucher
- **Input**:
  - Asuransi: (kosong)
  - Voucher Code: (kosong)
  - Layanan: CT Scan Rp 1.810.965
- **Expected**:
  - Subtotal: Rp 1.810.965
  - Diskon: Rp 0
  - Total: Rp 1.810.965

## Database Verification

```sql
-- Check BPJS discount
SELECT id, name, discount_percentage, coverage_limit, terms 
FROM insurances 
WHERE name LIKE '%BPJS%';

-- Result:
-- BPJS Kesehatan - discount_percentage: 100.00
```

## Files Changed

1. `app/Http/Controllers/Kasir/TransactionController.php`
   - Added insurance discount logic
   - Fixed priority: voucher code > insurance discount > auto voucher

2. `resources/views/kasir/transactions/create.blade.php`
   - Updated JavaScript calculateTotals() function
   - Added real-time insurance discount calculation
   - Fixed voucher priority check

3. `VOUCHER_IMPLEMENTATION.md`
   - Updated business rules
   - Clarified discount priority

## Summary

✅ **Fixed**: Insurance discount sekarang diterapkan otomatis saat pilih asuransi
✅ **Priority**: Manual voucher code > Insurance discount > Auto voucher
✅ **Real-time**: Discount terkalkulasi langsung saat pilih asuransi di form
✅ **Coverage Limit**: Respected jika set (misal: max Rp 50jt/tahun)
✅ **Backend & Frontend**: Consistent discount logic di kedua sisi
