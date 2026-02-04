<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\MedicalService;
use App\Models\Insurance;
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
            'insurance_id' => 'required|exists:insurances,id',
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
            
            // Get insurance
            $insurance = Insurance::findOrFail($validated['insurance_id']);
            
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
            $totalDiscountBeforeLimit = 0;
            $serviceDetails = [];

            // First pass: Calculate subtotal and theoretical discount
            foreach ($validated['services'] as $service) {
                $medicalService = MedicalService::find($service['medical_service_id']);
                $quantity = $service['quantity'];
                
                // Get current price from API (with fallback to database)
                $price = $medicalService->getCurrentPrice();
                
                // Hitung diskon per item dari asuransi
                $discountPerItem = 0;
                
                if ($insurance->discount_percentage > 0) {
                    // Use insurance discount percentage
                    $discountPerItem = ($price * $insurance->discount_percentage) / 100;
                    
                    // Apply coverage limit if set
                    if ($insurance->coverage_limit) {
                        $discountPerItem = min($discountPerItem, $insurance->coverage_limit / $quantity);
                    }
                }

                $totalDiscountBeforeLimit += ($discountPerItem * $quantity);
                $subtotal += ($price * $quantity);
                
                $serviceDetails[] = [
                    'medical_service_id' => $medicalService->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount_per_item' => $discountPerItem,
                ];
            }

            // Apply max discount amount limit if set
            $finalDiscount = $totalDiscountBeforeLimit;
            $discountRatio = 1;
            
            if ($insurance->max_discount_amount && $totalDiscountBeforeLimit > $insurance->max_discount_amount) {
                $finalDiscount = $insurance->max_discount_amount;
                // Calculate ratio to adjust discount per item proportionally
                $discountRatio = $finalDiscount / $totalDiscountBeforeLimit;
            }

            // Second pass: Create transaction details with adjusted discount
            foreach ($serviceDetails as $detail) {
                $adjustedDiscountPerItem = $detail['discount_per_item'] * $discountRatio;
                $itemSubtotal = ($detail['price'] * $detail['quantity']) - ($adjustedDiscountPerItem * $detail['quantity']);
                
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'medical_service_id' => $detail['medical_service_id'],
                    'quantity' => $detail['quantity'],
                    'price' => $detail['price'],
                    'discount_per_item' => $adjustedDiscountPerItem,
                    'subtotal' => $itemSubtotal,
                ]);
            }

            // Update transaksi dengan total
            $transaction->update([
                'subtotal' => $subtotal,
                'discount_amount' => $finalDiscount,
                'total' => $subtotal - $finalDiscount,
            ]);

            DB::commit();

            $successMsg = 'Transaksi berhasil dibuat dengan asuransi ' . $insurance->name;
            if ($insurance->discount_percentage > 0) {
                $successMsg .= " (diskon: {$insurance->discount_percentage}%)";
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
        $transaction->load(['details.medicalService', 'insurance', 'user']);
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
            'insurance_id' => 'required|exists:insurances,id',
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
            $totalDiscountBeforeLimit = 0;
            $serviceDetails = [];

            // Get insurance
            $insurance = null;
            if ($validated['insurance_id']) {
                $insurance = Insurance::find($validated['insurance_id']);
            }

            // First pass: Calculate subtotal and theoretical discount
            foreach ($validated['services'] as $service) {
                $medicalService = MedicalService::find($service['medical_service_id']);
                $quantity = $service['quantity'];
                $price = $medicalService->getCurrentPrice();
                
                $discountPerItem = 0;
                if ($insurance && $insurance->discount_percentage > 0) {
                    // Use insurance discount percentage
                    $discountPerItem = ($price * $insurance->discount_percentage) / 100;
                    
                    // Apply coverage limit if set
                    if ($insurance->coverage_limit) {
                        $discountPerItem = min($discountPerItem, $insurance->coverage_limit / $quantity);
                    }
                }

                $totalDiscountBeforeLimit += ($discountPerItem * $quantity);
                $subtotal += ($price * $quantity);
                
                $serviceDetails[] = [
                    'medical_service_id' => $medicalService->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount_per_item' => $discountPerItem,
                ];
            }

            // Apply max discount amount limit if set
            $finalDiscount = $totalDiscountBeforeLimit;
            $discountRatio = 1;
            
            if ($insurance && $insurance->max_discount_amount && $totalDiscountBeforeLimit > $insurance->max_discount_amount) {
                $finalDiscount = $insurance->max_discount_amount;
                // Calculate ratio to adjust discount per item proportionally
                $discountRatio = $finalDiscount / $totalDiscountBeforeLimit;
            }

            // Second pass: Create transaction details with adjusted discount
            foreach ($serviceDetails as $detail) {
                $adjustedDiscountPerItem = $detail['discount_per_item'] * $discountRatio;
                $itemSubtotal = ($detail['price'] * $detail['quantity']) - ($adjustedDiscountPerItem * $detail['quantity']);
                
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'medical_service_id' => $detail['medical_service_id'],
                    'quantity' => $detail['quantity'],
                    'price' => $detail['price'],
                    'discount_per_item' => $adjustedDiscountPerItem,
                    'subtotal' => $itemSubtotal,
                ]);
            }

            // Update transaksi
            $transaction->update([
                'patient_name' => $validated['patient_name'],
                'insurance_id' => $validated['insurance_id'],
                'subtotal' => $subtotal,
                'discount_amount' => $finalDiscount,
                'total' => $subtotal - $finalDiscount,
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
                'insurance_name' => $voucher->insurance ? $voucher->insurance->name : null,
                'min_transaction' => $voucher->min_transaction,
                'usage_remaining' => $voucher->usage_limit ? ($voucher->usage_limit - $voucher->used_count) : null,
                'max_discount' => $voucher->max_discount
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
