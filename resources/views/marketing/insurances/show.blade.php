<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Asuransi') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('marketing.insurances.edit', $insurance) }}" 
                   class="text-sm px-4 py-2 bg-amber-500 hover:bg-amber-700 text-white rounded-lg transition-colors">
                    ✏️ Edit Diskon
                </a>
                <a href="{{ route('marketing.insurances.index') }}" 
                   class="text-sm px-4 py-2 bg-gray-500 hover:bg-gray-700 text-white rounded-lg transition-colors">
                    ← Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Main Insurance Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $insurance->name }}</h3>
                            @if($insurance->code)
                                <p class="text-sm text-gray-600">Kode: {{ $insurance->code }}</p>
                            @endif
                            @if($insurance->description)
                                <p class="text-gray-700 mt-2">{{ $insurance->description }}</p>
                            @endif
                        </div>
                        @if($insurance->is_active)
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                ✓ Aktif
                            </span>
                        @else
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">
                                ✗ Tidak Aktif
                            </span>
                        @endif
                    </div>

                    <!-- Discount Info Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                            <div class="text-xs text-blue-600 font-semibold mb-1">Persentase Diskon</div>
                            <div class="text-4xl font-bold text-blue-900">{{ number_format($insurance->discount_percentage, 1) }}%</div>
                            <div class="text-xs text-blue-700 mt-1">dari total tagihan</div>
                        </div>

                        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                            <div class="text-xs text-green-600 font-semibold mb-1">Maksimal Nominal Diskon</div>
                            @if($insurance->max_discount_amount)
                                <div class="text-3xl font-bold text-green-900">Rp {{ number_format($insurance->max_discount_amount, 0, ',', '.') }}</div>
                                <div class="text-xs text-green-700 mt-1">batas maksimal per transaksi</div>
                            @else
                                <div class="text-2xl font-bold text-green-900">Tidak dibatasi</div>
                                <div class="text-xs text-green-700 mt-1">sesuai persentase & coverage</div>
                            @endif
                        </div>

                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
                            <div class="text-xs text-purple-600 font-semibold mb-1">Limit Tanggungan</div>
                            @if($insurance->coverage_limit)
                                <div class="text-3xl font-bold text-purple-900">Rp {{ number_format($insurance->coverage_limit / 1000000, 0) }} Jt</div>
                                <div class="text-xs text-purple-700 mt-1">per tahun</div>
                            @else
                                <div class="text-2xl font-bold text-purple-900">Unlimited</div>
                                <div class="text-xs text-purple-700 mt-1">sesuai ketentuan</div>
                            @endif
                        </div>
                    </div>

                    <!-- Example Calculation -->
                    @if($insurance->discount_percentage > 0)
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                            <h4 class="text-sm font-semibold text-amber-900 mb-3">💡 Contoh Perhitungan Diskon:</h4>
                            <div class="space-y-2 text-sm text-amber-800">
                                @php
                                    $examples = [
                                        500000,
                                        1000000,
                                        2000000,
                                        5000000
                                    ];
                                @endphp
                                @foreach($examples as $total)
                                    @php
                                        $discount = ($total * $insurance->discount_percentage) / 100;
                                        if ($insurance->max_discount_amount && $discount > $insurance->max_discount_amount) {
                                            $discount = $insurance->max_discount_amount;
                                            $capped = true;
                                        } else {
                                            $capped = false;
                                        }
                                        $final = $total - $discount;
                                    @endphp
                                    <div class="flex items-center justify-between bg-white rounded p-2 border border-amber-200">
                                        <div>
                                            <span class="font-semibold">Total Rp {{ number_format($total, 0, ',', '.') }}</span>
                                            → Diskon: <strong class="text-green-600">Rp {{ number_format($discount, 0, ',', '.') }}</strong>
                                            @if($capped)
                                                <span class="text-red-600 text-xs">(dibatasi)</span>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs text-gray-600">Bayar:</span>
                                            <strong class="text-blue-600">Rp {{ number_format($final, 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Terms & Conditions -->
                    @if($insurance->terms)
                        <div class="border-t pt-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Syarat & Ketentuan:</h4>
                            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-line">
                                {{ $insurance->terms }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Vouchers Section -->
            @if($insurance->vouchers->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Voucher Terkait ({{ $insurance->vouchers->count() }})</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($insurance->vouchers as $voucher)
                                <div class="border rounded-lg p-4 {{ $voucher->is_active ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $voucher->code }}</h4>
                                            <p class="text-xs text-gray-600">{{ $voucher->description ?? 'No description' }}</p>
                                        </div>
                                        @if($voucher->is_active)
                                            <span class="px-2 py-1 bg-green-200 text-green-800 rounded text-xs font-semibold">Aktif</span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-300 text-gray-700 rounded text-xs">Nonaktif</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-700 mb-1">
                                        Diskon: <strong>{{ number_format($voucher->discount_percentage, 0) }}%</strong>
                                    </div>
                                    @if($voucher->valid_from && $voucher->valid_until)
                                        <div class="text-xs text-gray-600">
                                            Valid: {{ $voucher->valid_from->format('d/m/Y') }} - {{ $voucher->valid_until->format('d/m/Y') }}
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500 italic">
                                            Periode tidak ditentukan
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p>Tidak ada voucher terkait dengan asuransi ini</p>
                    </div>
                </div>
            @endif

            <!-- Metadata -->
            <div class="bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                        <div>
                            <span class="font-semibold">Dibuat:</span> {{ $insurance->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div>
                            <span class="font-semibold">Terakhir Diupdate:</span> {{ $insurance->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
