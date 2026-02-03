<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\TelegramNotificationService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SendDailyTransactionReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:send-daily-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily transaction report via Telegram at 01:00 AM';

    protected $telegram;

    public function __construct(TelegramNotificationService $telegram)
    {
        parent::__construct();
        $this->telegram = $telegram;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday();
        
        // Cek apakah ada transaksi kemarin
        $transactionCount = Transaction::whereDate('paid_at', $yesterday)
            ->where('payment_status', 'paid')
            ->count();

        if ($transactionCount === 0) {
            $this->info('No transactions found for yesterday.');
            return 0;
        }

        // Generate Excel file
        $fileName = 'transactions_' . $yesterday->format('Y-m-d') . '.xlsx';
        $filePath = 'reports/' . $fileName;
        
        // Ensure reports directory exists
        if (!Storage::exists('reports')) {
            Storage::makeDirectory('reports');
        }
        
        Excel::store(new TransactionsExport($yesterday), $filePath, 'local');
        
        // Verify file was created
        if (!Storage::exists($filePath)) {
            $this->error('Failed to generate Excel file');
            return 1;
        }

        // Send to Telegram
        try {
            $this->telegram->sendDailyReport(
                $yesterday->format('d F Y'),
                $transactionCount,
                $filePath
            );

            $this->info('Daily transaction report sent successfully to Telegram!');
            
            // Hapus file setelah dikirim
            Storage::delete($filePath);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to send Telegram notification: ' . $e->getMessage());
            
            // Clean up file if exists
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
            
            return 1;
        }
    }
}
