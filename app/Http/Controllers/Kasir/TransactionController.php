<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\MedicalService;
use App\Models\Insurance;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::with(['insurance', 'user'])
            ->latest()
            ->paginate(15);
        return view('kasir.transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $medicalServices = MedicalService::all();
        $insurances = Insurance::all();
        return view('kasir.transactions.create', compact('medicalServices', 'insurances'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'insurance_id' => 'nullable|exists:insurances,id',
            'voucher_code' => 'nullable|string|max:50',
            'services' => 'required|array',
            'services.*.medical_service_id' => 'required|exists:medical_services,id',
            'services.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Validasi: Pastikan semua tindakan memiliki harga yang valid
            foreach ($validated['services'] as $service) {
                $medicalService = MedicalService::find($service['medical_service_id']);
                
                if (!$medicalService) {
                    DB::rollBack();
                    return back()->withInput()->with('error', "Tindakan medis tidak ditemukan.");
                }
                
                // Coba ambil harga dari API
                try {
                    $price = $medicalService->getCurrentPrice();
                    
                    // Validasi harga tidak boleh 0 atau null
                    if (!$price || $price <= 0) {
                        DB::rollBack();
                        return back()->withInput()->with('error', 
                            "Harga untuk tindakan '{$medicalService->name}' tidak tersedia. "
                            . "Silakan sinkronkan data dari RS Delta Surya atau hubungi administrator."
                        );
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 
                        "Gagal mendapatkan harga untuk tindakan '{$medicalService->name}': {$e->getMessage()}"
                    );
                }
            }
            
            // Buat transaksi
            $transaction = Transaction::create([
                'patient_name' => $validated['patient_name'],
                'insurance_id' => $validated['insurance_id'],
                'user_id' => auth()->id(),
                'subtotal' => 0,
                'discount_amount' => 0,
                'total' => 0,
                'payment_status' => 'pending',
            ]);

            $subtotal = 0;
            $totalDiscount = 0;

            // Handle voucher berdasarkan code atau insurance
            $voucher = null;
            $insurance = null;
            $voucherError = null;
            
            // Get insurance if selected
            if ($validated['insurance_id']) {
                $insurance = Insurance::find($validated['insurance_id']);
            }
            
            // Priority 1: Voucher code (jika diinput manual)
            if (!empty($validated['voucher_code'])) {
                $voucher = Voucher::where('code', $validated['voucher_code'])
                    ->where('is_active', true)
                    ->first();
                
                if (!$voucher) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 
                        "Kode voucher '{$validated['voucher_code']}' tidak ditemukan atau tidak aktif."
                    );
                }
                
                // Check if insurance matches (if insurance selected)
                if ($validated['insurance_id'] && $voucher->insurance_id && $voucher->insurance_id != $validated['insurance_id']) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 
                        "Voucher '{$voucher->code}' tidak berlaku untuk asuransi yang dipilih."
                    );
                }
                
                // Set insurance from voucher if not selected
                if (!$validated['insurance_id'] && $voucher->insurance_id) {
                    $validated['insurance_id'] = $voucher->insurance_id;
                    $transaction->insurance_id = $voucher->insurance_id;
                    $insurance = Insurance::find($voucher->insurance_id);
                }
            }
            // Priority 2: Auto voucher from insurance (jika tidak input code)
            elseif ($validated['insurance_id']) {
                // Cari voucher aktif untuk asuransi ini
                $voucher = Voucher::where('insurance_id', $validated['insurance_id'])
                    ->where('is_active', true)
                    ->orderBy('discount_value', 'desc') // Pilih yang diskon terbesar
                    ->first();
            }
            
            // Calculate subtotal first to validate voucher
            $tempSubtotal = 0;
            foreach ($validated['services'] as $service) {
                $medicalService = MedicalService::find($service['medical_service_id']);
                $quantity = $service['quantity'];
                $price = $medicalService->getCurrentPrice();
                $tempSubtotal += ($price * $quantity);
            }
            
            // Validate voucher with transaction amount
            if ($voucher) {
                if (!$voucher->isValid($tempSubtotal)) {
                    $validationMsg = $voucher->getValidationMessage($tempSubtotal);
                    DB::rollBack();
                    return back()->withInput()->with('error', 
                        "Voucher '{$voucher->code}' tidak dapat digunakan: {$validationMsg}"
                    );
                }
                
                $transaction->voucher_id = $voucher->id;
            }

            // Tambahkan detail transaksi
            foreach ($validated['services'] as $service) {
                $medicalService = MedicalService::find($service['medical_service_id']);
                $quantity = $service['quantity'];
                
                // Get current price from API (with fallback to database)
                $price = $medicalService->getCurrentPrice();
                
                // Hitung diskon per item
                // Priority 1: Voucher discount (if voucher code was entered)
                // Priority 2: Insurance discount percentage (if insurance selected)
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

                $itemSubtotal = ($price * $quantity) - ($discountPerItem * $quantity);
                $totalDiscount += ($discountPerItem * $quantity);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'medical_service_id' => $medicalService->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount_per_item' => $discountPerItem,
                    'subtotal' => $itemSubtotal,
                ]);

                $subtotal += ($price * $quantity);
            }

            // Update transaksi dengan total
            $transaction->update([
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'total' => $subtotal - $totalDiscount,
            ]);

            // Increment voucher usage if used
            if ($voucher && !empty($validated['voucher_code'])) {
                $voucher->incrementUsage();
            }

            DB::commit();

            $successMsg = 'Transaksi berhasil dibuat.';
            if ($voucher && !empty($validated['voucher_code'])) {
                $successMsg .= " Voucher {$voucher->code} berhasil digunakan (diskon: {$voucher->getDiscountText()})";
            } elseif ($insurance && $insurance->discount_percentage > 0) {
                $successMsg .= " Diskon asuransi {$insurance->name}: {$insurance->discount_percentage}%";
            }

            return redirect()->route('kasir.transactions.show', $transaction)
                ->with('success', $successMsg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['details.medicalService', 'insurance', 'voucher', 'user']);
        return view('kasir.transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        if ($transaction->payment_status === 'paid') {
            return back()->with('error', 'Transaksi yang sudah dibayar tidak dapat diubah.');
        }

        $medicalServices = MedicalService::all();
        $insurances = Insurance::all();
        $transaction->load('details.medicalService');
        
        return view('kasir.transactions.edit', compact('transaction', 'medicalServices', 'insurances'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->payment_status === 'paid') {
            return back()->with('error', 'Transaksi yang sudah dibayar tidak dapat diubah.');
        }

        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'insurance_id' => 'nullable|exists:insurances,id',
            'services' => 'required|array',
            'services.*.medical_service_id' => 'required|exists:medical_services,id',
            'services.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Validasi: Pastikan semua tindakan memiliki harga yang valid
            foreach ($validated['services'] as $service) {
                $medicalService = MedicalService::find($service['medical_service_id']);
                
                if (!$medicalService) {
                    DB::rollBack();
                    return back()->withInput()->with('error', "Tindakan medis tidak ditemukan.");
                }
                
                // Coba ambil harga dari API
                try {
                    $price = $medicalService->getCurrentPrice();
                    
                    // Validasi harga tidak boleh 0 atau null
                    if (!$price || $price <= 0) {
                        DB::rollBack();
                        return back()->withInput()->with('error', 
                            "Harga untuk tindakan '{$medicalService->name}' tidak tersedia. "
                            . "Silakan sinkronkan data dari RS Delta Surya atau hubungi administrator."
                        );
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 
                        "Gagal mendapatkan harga untuk tindakan '{$medicalService->name}': {$e->getMessage()}"
                    );
                }
            }
            
            // Hapus detail lama
            $transaction->details()->delete();

            $subtotal = 0;
            $totalDiscount = 0;

            // Ambil voucher jika ada asuransi
            $voucher = null;
            if ($validated['insurance_id']) {
                $insurance = Insurance::find($validated['insurance_id']);
                
                $voucher = Voucher::where('insurance_id', $validated['insurance_id'])
                    ->where('is_active', true)
                    ->first();
                    
                if ($voucher && $voucher->isValid()) {
                    // Voucher valid
                } elseif ($insurance->discount_percentage <= 0) {
                    \Log::warning("Insurance {$insurance->name} has no discount and no active voucher for transaction {$transaction->id}");
                }
            }

            // Tambahkan detail baru
            foreach ($validated['services'] as $service) {
                $medicalService = MedicalService::find($service['medical_service_id']);
                $quantity = $service['quantity'];
                $price = $medicalService->getCurrentPrice();
                
                $discountPerItem = 0;
                if ($voucher) {
                    $discountPerItem = $voucher->calculateDiscount($price);
                }

                $itemSubtotal = ($price * $quantity) - ($discountPerItem * $quantity);
                $totalDiscount += ($discountPerItem * $quantity);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'medical_service_id' => $medicalService->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount_per_item' => $discountPerItem,
                    'subtotal' => $itemSubtotal,
                ]);

                $subtotal += ($price * $quantity);
            }

            // Update transaksi
            $transaction->update([
                'patient_name' => $validated['patient_name'],
                'insurance_id' => $validated['insurance_id'],
                'voucher_id' => $voucher ? $voucher->id : null,
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'total' => $subtotal - $totalDiscount,
            ]);

            DB::commit();

            return redirect()->route('kasir.transactions.show', $transaction)
                ->with('success', 'Transaksi berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        if ($transaction->payment_status === 'paid') {
            return back()->with('error', 'Transaksi yang sudah dibayar tidak dapat dihapus.');
        }

        $transaction->delete();

        return redirect()->route('kasir.transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Process payment
     */
    public function pay(Transaction $transaction)
    {
        if ($transaction->payment_status === 'paid') {
            return back()->with('error', 'Transaksi sudah dibayar.');
        }

        $transaction->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->route('kasir.transactions.show', $transaction)
            ->with('success', 'Pembayaran berhasil.');
    }

    /**
     * Check voucher validity via AJAX
     */
    public function checkVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'insurance_id' => 'nullable|exists:insurances,id'
        ]);

        $voucher = Voucher::where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode voucher tidak ditemukan atau tidak aktif'
            ]);
        }

        // Check insurance match if provided
        if ($request->insurance_id && $voucher->insurance_id != $request->insurance_id) {
            return response()->json([
                'valid' => false,
                'message' => 'Voucher tidak berlaku untuk asuransi yang dipilih',
                'voucher' => [
                    'code' => $voucher->code,
                    'insurance_name' => $voucher->insurance->name
                ]
            ]);
        }

        // Validate with transaction amount
        if (!$voucher->isValid($request->subtotal)) {
            return response()->json([
                'valid' => false,
                'message' => $voucher->getValidationMessage($request->subtotal),
                'voucher' => [
                    'code' => $voucher->code,
                    'description' => $voucher->description
                ]
            ]);
        }

        // Calculate discount
        $discount = $voucher->calculateDiscount($request->subtotal);

        return response()->json([
            'valid' => true,
            'message' => 'Voucher valid dan dapat digunakan',
            'voucher' => [
                'code' => $voucher->code,
                'description' => $voucher->description,
                'discount_type' => $voucher->discount_type,
                'discount_value' => $voucher->discount_value,
                'discount_text' => $voucher->getDiscountText(),
                'discount_amount' => $discount,
                'insurance_id' => $voucher->insurance_id,
                'insurance_name' => $voucher->insurance->name,
                'min_transaction' => $voucher->min_transaction,
                'usage_remaining' => $voucher->usage_limit ? ($voucher->usage_limit - $voucher->used_count) : null
            ]
        ]);
    }

    /**
     * Print receipt as PDF
     */
    public function printReceipt(Transaction $transaction)
    {
        if ($transaction->payment_status !== 'paid') {
            return back()->with('error', 'Hanya transaksi yang sudah dibayar yang dapat dicetak.');
        }

        $transaction->load(['details.medicalService', 'insurance', 'voucher', 'user']);
        
        $pdf = Pdf::loadView('kasir.transactions.receipt', compact('transaction'));
        
        return $pdf->download('receipt-' . $transaction->transaction_code . '.pdf');
    }
}
