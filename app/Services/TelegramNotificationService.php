<?php

namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramNotificationService
{
    protected $chatId;
    
    public function __construct()
    {
        $this->chatId = config('telegram.bots.default.chat_id');
    }
    
    /**
     * Send daily transaction report to Telegram
     */
    public function sendDailyReport($date, $transactionCount, $filePath = null)
    {
        try {
            $message = "📊 *Laporan Transaksi Harian*\n\n";
            $message .= "📅 Tanggal: *{$date}*\n";
            $message .= "💰 Total Transaksi: *{$transactionCount} transaksi*\n\n";
            $message .= "Laporan detail terlampir dalam file Excel.\n\n";
            $message .= "🏥 _HospitalPay by BrilliantRidho_";
            
            // Send message with file if exists
            if ($filePath && Storage::exists($filePath)) {
                // Use Storage::path() to get correct full path
                $fullPath = Storage::path($filePath);
                
                // Verify file exists before sending
                if (!file_exists($fullPath)) {
                    Log::error("File not found: {$fullPath}");
                    throw new \Exception("File Excel tidak ditemukan di: {$fullPath}");
                }
                
                Telegram::sendDocument([
                    'chat_id' => $this->chatId,
                    'document' => fopen($fullPath, 'r'),
                    'caption' => $message,
                    'parse_mode' => 'Markdown'
                ]);
            } else {
                // Send text only if no file
                Telegram::sendMessage([
                    'chat_id' => $this->chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown'
                ]);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send transaction notification (for testing)
     */
    public function sendTransactionNotification($transaction)
    {
        try {
            $status = $transaction->payment_status === 'paid' ? '✅ Lunas' : '⏳ Pending';
            
            $message = "🏥 *Notifikasi Transaksi*\n\n";
            $message .= "Kode: `{$transaction->transaction_code}`\n";
            $message .= "Pasien: *{$transaction->patient_name}*\n";
            $message .= "Asuransi: " . ($transaction->insurance ? $transaction->insurance->name : 'Tanpa Asuransi') . "\n";
            $message .= "Total: *Rp " . number_format($transaction->total, 0, ',', '.') . "*\n";
            $message .= "Status: {$status}\n";
            $message .= "Kasir: {$transaction->user->name}\n\n";
            $message .= "_" . $transaction->created_at->format('d/m/Y H:i') . "_";
            
            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Test connection to Telegram
     */
    public function testConnection()
    {
        try {
            $response = Telegram::getMe();
            $botInfo = $response->toArray();
            
            // Send test message
            $message = "✅ *Koneksi Telegram Berhasil!*\n\n";
            $message .= "Bot: @{$botInfo['username']}\n";
            $message .= "Nama: {$botInfo['first_name']}\n\n";
            $message .= "🏥 _Hospital Pay System Ready_";
            
            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);
            
            return [
                'success' => true,
                'bot' => $botInfo
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
