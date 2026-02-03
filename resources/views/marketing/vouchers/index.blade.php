<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kelola Voucher Diskon') }}
            </h2>
            <a href="{{ route('marketing.vouchers.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Tambah Voucher
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

            <!-- Data Sync Section -->
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-purple-900">Sinkronisasi Data Rumah Sakit</h3>
                        <p class="text-sm text-purple-700">Data asuransi dari API RS Delta Surya</p>
                        <p class="text-xs text-purple-600 mt-1">
                            📊 Asuransi terdaftar: <strong>{{ \App\Models\Insurance::count() }} item</strong>
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <form action="{{ route('api-sync.sync-insurances') }}" method="POST" class="inline" onsubmit="return confirm('Sync data asuransi dari API eksternal?')">
                            @csrf
                            <button type="submit" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                🔄 Sync Asuransi
                            </button>
                        </form>
                        <a href="{{ route('api-sync.test-page') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            ⚙️ Test API
                        </a>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asuransi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diskon</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min. Transaksi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penggunaan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($vouchers as $voucher)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <span class="font-mono font-bold text-purple-600">{{ $voucher->code }}</span>
                                            @if($voucher->description)
                                                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($voucher->description, 40) }}</p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $voucher->insurance?->name ?? '<span class="text-gray-500 italic">Voucher Umum</span>' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($voucher->discount_type === 'percentage')
                                                <span class="text-blue-600 font-semibold">{{ $voucher->discount_value }}%</span>
                                                @if($voucher->max_discount)
                                                    <p class="text-xs text-gray-500">Maks: Rp {{ number_format($voucher->max_discount, 0, ',', '.') }}</p>
                                                @endif
                                            @else
                                                <span class="text-green-600 font-semibold">Rp {{ number_format($voucher->discount_value, 0, ',', '.') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($voucher->min_transaction)
                                                Rp {{ number_format($voucher->min_transaction, 0, ',', '.') }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $voucher->used_count }} / {{ $voucher->usage_limit ?? '∞' }}
                                            @if($voucher->usage_limit)
                                                <div class="w-20 bg-gray-200 rounded-full h-2 mt-1">
                                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(($voucher->used_count / $voucher->usage_limit) * 100, 100) }}%"></div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-xs">
                                            @if($voucher->valid_from || $voucher->valid_until)
                                                {{ $voucher->valid_from?->format('d/m/Y') ?? '-' }}<br>s/d<br>{{ $voucher->valid_until?->format('d/m/Y') ?? '-' }}
                                            @else
                                                <span class="text-gray-400">Tidak terbatas</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($voucher->is_active)
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Aktif</span>
                                            @else
                                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium">
                                            <a href="{{ route('marketing.vouchers.edit', $voucher) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            <form action="{{ route('marketing.vouchers.destroy', $voucher) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">Belum ada voucher</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $vouchers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
