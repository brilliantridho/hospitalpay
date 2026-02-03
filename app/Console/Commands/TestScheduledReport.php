<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\TelegramNotificationService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TestScheduledReport extends Command
{
    protected $signature = 'test:scheduled-report {date?}';
    protected $description = 'Test scheduled report with custom date (default: yesterday)';

    protected $telegram;

    public function __construct(TelegramNotificationService $telegram)
    {
        parent::__construct();
        $this->telegram = $telegram;
    }

    public function handle()
    {
        $this->info('🕐 Testing Scheduled Report...');
        $this->newLine();
        
        // Use custom date or yesterday
        $date = $this->argument('date') 
            ? Carbon::parse($this->argument('date'))
            : Carbon::yesterday();
        
        $this->line("📅 Report Date: " . $date->format('d/m/Y'));
        
        // Cek transaksi
        $transactionCount = Transaction::whereDate('paid_at', $date)
            ->where('payment_status', 'paid')
            ->count();

        $this->line("📊 Transactions Found: {$transactionCount}");
        $this->newLine();

        if ($transactionCount === 0) {
            $this->warn('⚠️ No transactions found for this date.');
            
            // Kirim notifikasi ke Telegram tetap
            try {
                $this->telegram->sendDailyReport($date, 0);
                $this->info('✅ Empty report notification sent to Telegram!');
                return 0;
            } catch (\Exception $e) {
                $this->error('❌ Failed to send notification: ' . $e->getMessage());
                return 1;
            }
        }

        // Buat directory jika belum ada
        $reportDir = 'reports';
        if (!Storage::exists($reportDir)) {
            Storage::makeDirectory($reportDir);
            $this->line('📁 Created reports directory');
        }

        // Generate file Excel
        $fileName = 'transactions_' . $date->format('Y-m-d') . '.xlsx';
        $filePath = $reportDir . '/' . $fileName;

        try {
            $this->line('📝 Generating Excel file...');
            Excel::store(new TransactionsExport($date), $filePath, 'local');
            
            if (!Storage::exists($filePath)) {
                throw new \Exception('File Excel tidak berhasil dibuat');
            }
            
            $this->info('✅ Excel file created: ' . $fileName);
            $this->newLine();
            
            // Kirim ke Telegram
            $this->line('📱 Sending to Telegram...');
            $this->telegram->sendDailyReport($date, $transactionCount, $filePath);
            
            $this->newLine();
            $this->info('✅ SUCCESS! Daily report sent to Telegram!');
            $this->line('🔔 Check your Telegram for the report.');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            
            // Cleanup jika ada file
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
                $this->line('🗑️ Cleaned up temporary file');
            }
            
            return 1;
        }
    }
}
