<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik kunjungan per asuransi
        $visitStats = Transaction::select('insurance_id', DB::raw('count(*) as total_visits'))
            ->whereNotNull('insurance_id')
            ->where('payment_status', 'paid')
            ->groupBy('insurance_id')
            ->with('insurance')
            ->orderBy('total_visits', 'desc')
            ->get();

        // Statistik pembayaran per asuransi
        $paymentStats = Transaction::select('insurance_id', DB::raw('sum(total) as total_payment'), DB::raw('count(*) as total_transactions'))
            ->whereNotNull('insurance_id')
            ->where('payment_status', 'paid')
            ->groupBy('insurance_id')
            ->with('insurance')
            ->orderBy('total_payment', 'desc')
            ->get();

        // Total transaksi hari ini
        $todayTransactions = Transaction::whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->count();

        // Total revenue hari ini
        $todayRevenue = Transaction::whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->sum('total');

        // Total diskon yang diberikan
        $totalDiscount = Transaction::where('payment_status', 'paid')
            ->sum('discount_amount');

        // Transaksi terbaru
        $recentTransactions = Transaction::with(['insurance', 'user'])
            ->where('payment_status', 'paid')
            ->latest()
            ->take(10)
            ->get();

        return view('marketing.dashboard', compact(
            'visitStats',
            'paymentStats',
            'todayTransactions',
            'todayRevenue',
            'totalDiscount',
            'recentTransactions'
        ));
    }
}
