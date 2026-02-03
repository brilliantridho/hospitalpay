<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\TelegramNotificationService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $telegram;

    public function __construct(TelegramNotificationService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Send daily transaction report manually (for testing)
     */
    public function sendDailyReport(Request $request)
    {
        // Ambil tanggal dari request atau default ke kemarin
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::yesterday();
        
        // Cek apakah ada transaksi pada tanggal tersebut
        $transactionCount = Transaction::whereDate('paid_at', $date)
            ->where('payment_status', 'paid')
            ->count();

        if ($transactionCount === 0) {
            return back()->with('error', 'Tidak ada transaksi pada tanggal ' . $date->format('d/m/Y'));
        }

        // Generate Excel file
        $fileName = 'transactions_' . $date->format('Y-m-d') . '.xlsx';
        $filePath = 'reports/' . $fileName;
        
        try {
            // Ensure reports directory exists
            if (!Storage::exists('reports')) {
                Storage::makeDirectory('reports');
            }
            
            // Generate Excel file
            Excel::store(new TransactionsExport($date), $filePath, 'local');
            
            // Verify file was created
            if (!Storage::exists($filePath)) {
                throw new \Exception('Gagal membuat file Excel');
            }
            
            // Send to Telegram
            $this->telegram->sendDailyReport(
                $date->format('d F Y'),
                $transactionCount,
                $filePath
            );

            // Hapus file setelah dikirim
            Storage::delete($filePath);
            
            return back()->with('success', 'Laporan transaksi berhasil dikirim ke Telegram! Total: ' . $transactionCount . ' transaksi');
        } catch (\Exception $e) {
            // Clean up file if exists
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
            return back()->with('error', 'Gagal mengirim ke Telegram: ' . $e->getMessage());
        }
    }

    /**
     * Test Telegram connection
     */
    public function testTelegram()
    {
        try {
            $result = $this->telegram->testConnection();
            
            if ($result['success']) {
                return back()->with('success', 
                    'Koneksi Telegram berhasil! Bot: @' . $result['bot']['username'] . 
                    '. Pesan test telah dikirim ke chat.'
                );
            } else {
                return back()->with('error', 'Koneksi Telegram gagal: ' . $result['error']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Download daily report as Excel (for testing)
     */
    public function downloadDailyReport(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::yesterday();
        
        $transactionCount = Transaction::whereDate('paid_at', $date)
            ->where('payment_status', 'paid')
            ->count();

        if ($transactionCount === 0) {
            return back()->with('error', 'Tidak ada transaksi pada tanggal ' . $date->format('d/m/Y'));
        }

        $fileName = 'transactions_' . $date->format('Y-m-d') . '.xlsx';
        
        return Excel::download(new TransactionsExport($date), $fileName);
    }
}
