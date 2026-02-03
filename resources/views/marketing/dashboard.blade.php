<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Marketing') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Testing Report Button -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-blue-900">📱 Notifikasi & Laporan Transaksi</h3>
                        <p class="text-sm text-blue-700">Kirim laporan transaksi ke Telegram atau download Excel</p>
                        <p class="text-xs text-blue-600 mt-1">
                            💡 Laporan otomatis dikirim setiap hari pukul 01:00 AM via Telegram
                        </p>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <form action="{{ route('reports.test-telegram') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                🔔 Test Telegram
                            </button>
                        </form>
                        <form action="{{ route('reports.send-daily') }}" method="POST" class="inline">
                            @csrf
                            <input type="date" name="date" value="{{ \Carbon\Carbon::yesterday()->format('Y-m-d') }}" class="border rounded px-3 py-2 text-sm">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                📱 Kirim Telegram
                            </button>
                        </form>
                        <form action="{{ route('reports.download-daily') }}" method="GET" class="inline">
                            <input type="date" name="date" value="{{ \Carbon\Carbon::yesterday()->format('Y-m-d') }}" class="border rounded px-3 py-2 text-sm">
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                📥 Download Excel
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-600">Transaksi Hari Ini</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $todayTransactions }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-600">Revenue Hari Ini</div>
                    <div class="text-3xl font-bold text-green-600">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-600">Total Diskon Diberikan</div>
                    <div class="text-3xl font-bold text-red-600">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</div>
                </div>
            </div>

            <!-- Visit Stats -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Kunjungan per Asuransi</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asuransi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Kunjungan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($visitStats as $stat)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $stat->insurance->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $stat->total_visits }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 text-center text-gray-500">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Stats -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Pembayaran per Asuransi</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asuransi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Transaksi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($paymentStats as $stat)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $stat->insurance->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $stat->total_transactions }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($stat->total_payment, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Transaksi Terbaru</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pasien</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asuransi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->transaction_code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->patient_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->insurance->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
