<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class GetTelegramChatId extends Command
{
    protected $signature = 'telegram:get-chat-id';
    protected $description = 'Get your Telegram Chat ID from recent messages';

    public function handle()
    {
        try {
            $this->info('Checking for recent messages...');
            $this->info('');
            
            // Get bot info
            $botInfo = Telegram::getMe();
            $this->info("Bot: @{$botInfo['username']} ({$botInfo['first_name']})");
            $this->info('');
            
            // Get updates
            $updates = Telegram::getUpdates();
            
            if (count($updates) > 0) {
                $this->info('Found messages:');
                $this->info('');
                
                $chatIds = [];
                foreach ($updates as $update) {
                    if (isset($update['message']['chat']['id'])) {
                        $chatId = $update['message']['chat']['id'];
                        $from = $update['message']['from']['first_name'] ?? 'Unknown';
                        $username = isset($update['message']['from']['username']) 
                            ? '@' . $update['message']['from']['username'] 
                            : '';
                        $text = $update['message']['text'] ?? '';
                        
                        if (!in_array($chatId, $chatIds)) {
                            $chatIds[] = $chatId;
                            $this->info("Chat ID: {$chatId}");
                            $this->info("From: {$from} {$username}");
                            $this->info("Message: {$text}");
                            $this->info('---');
                        }
                    }
                }
                
                if (count($chatIds) > 0) {
                    $this->info('');
                    $this->info('Copy Chat ID di atas dan update file .env:');
                    $this->info('TELEGRAM_CHAT_ID=' . $chatIds[0]);
                }
            } else {
                $this->warn('❌ No messages found!');
                $this->info('');
                $this->info('Cara mendapatkan Chat ID:');
                $this->info('');
                $this->info("1. Buka Telegram dan cari bot: @{$botInfo['username']}");
                $this->info('2. Klik Start');
                $this->info('3. Kirim pesan apa saja (contoh: "hello")');
                $this->info('4. Jalankan command ini lagi: php artisan telegram:get-chat-id');
                $this->info('');
                $this->info('ATAU gunakan @userinfobot:');
                $this->info('1. Cari @userinfobot di Telegram');
                $this->info('2. Klik Start');
                $this->info('3. Bot akan menampilkan Chat ID Anda');
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
