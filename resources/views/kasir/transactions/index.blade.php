<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Transaksi Pembayaran') }}
            </h2>
            <a href="{{ route('kasir.transactions.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Transaksi Baru
            </a>
        </div>
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

            <!-- Data Sync Section -->
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-purple-900">Sinkronisasi Data Rumah Sakit</h3>
                        <p class="text-sm text-purple-700">Data asuransi dan tindakan medis dari API RS Delta Surya</p>
                        <p class="text-xs text-purple-600 mt-1">
                            📊 Asuransi: <strong>{{ \App\Models\Insurance::count() }} item</strong> | 
                            Tindakan: <strong>{{ \App\Models\MedicalService::count() }} item</strong>
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <form action="{{ route('api-sync.sync-all') }}" method="POST" class="inline" onsubmit="return confirm('Sync semua data dari API eksternal?')">
                            @csrf
                            <button type="submit" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                🔄 Sync Data
                            </button>
                        </form>
                        <a href="{{ route('api-sync.test-page') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            ⚙️ Test API
                        </a>
                    </div>
                </div>
            </div>

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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pasien</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asuransi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4">{{ $transaction->transaction_code }}</td>
                                        <td class="px-6 py-4">{{ $transaction->patient_name }}</td>
                                        <td class="px-6 py-4">{{ $transaction->insurance->name ?? '-' }}</td>
                                        <td class="px-6 py-4">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4">
                                            @if($transaction->payment_status === 'paid')
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded">Lunas</span>
                                            @else
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <a href="{{ route('kasir.transactions.show', $transaction) }}" class="text-blue-600 hover:text-blue-900 mr-2">Detail</a>
                                            @if($transaction->payment_status === 'pending')
                                                <a href="{{ route('kasir.transactions.edit', $transaction) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                                <form action="{{ route('kasir.transactions.destroy', $transaction) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                                                </form>
                                            @else
                                                <a href="{{ route('kasir.transactions.print', $transaction) }}" class="text-green-600 hover:text-green-900">Cetak</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
