<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Asuransi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Asuransi</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $insurances->count() }}</div>
                </div>
                <div class="bg-blue-50 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-blue-600 text-sm">Dengan Diskon</div>
                    <div class="text-3xl font-bold text-blue-800">{{ $insurances->filter(fn($i) => $i->discount_percentage > 0)->count() }}</div>
                </div>
                <div class="bg-green-50 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-green-600 text-sm">Rata-rata Diskon</div>
                    <div class="text-3xl font-bold text-green-800">{{ number_format($insurances->where('discount_percentage', '>', 0)->avg('discount_percentage'), 1) }}%</div>
                </div>
                <div class="bg-purple-50 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-purple-600 text-sm">Diskon Tertinggi</div>
                    <div class="text-3xl font-bold text-purple-800">{{ number_format($insurances->max('discount_percentage'), 0) }}%</div>
                </div>
            </div>

            <!-- Insurance Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($insurances as $insurance)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $insurance->name }}</h3>
                                    @if($insurance->description)
                                        <p class="text-xs text-gray-500 mt-1">{{ $insurance->description }}</p>
                                    @endif
                                </div>
                                @if($insurance->discount_percentage > 0)
                                    <div class="ml-3 px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-bold">
                                        {{ number_format($insurance->discount_percentage, 0) }}%
                                    </div>
                                @else
                                    <div class="ml-3 px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">
                                        Tanpa Diskon
                                    </div>
                                @endif
                            </div>

                            <!-- Coverage Limit -->
                            <div class="mb-3 p-2 bg-blue-50 rounded text-sm">
                                <div class="text-blue-600 font-semibold text-xs mb-1">Limit Tanggungan</div>
                                @if($insurance->coverage_limit)
                                    <div class="text-blue-900 font-bold">Rp {{ number_format($insurance->coverage_limit, 0, ',', '.') }}</div>
                                    <div class="text-blue-600 text-xs">per tahun</div>
                                @else
                                    <div class="text-blue-900 font-bold">Unlimited</div>
                                    <div class="text-blue-600 text-xs">sesuai ketentuan</div>
                                @endif
                            </div>

                            <!-- Terms Preview -->
                            @if($insurance->terms)
                                <div class="mb-4">
                                    <div class="text-xs text-gray-600 mb-1 font-semibold">Ketentuan:</div>
                                    <div class="text-xs text-gray-700 bg-gray-50 p-2 rounded max-h-24 overflow-y-auto">
                                        {{ Str::limit($insurance->terms, 150) }}
                                    </div>
                                </div>
                            @endif

                            <!-- Vouchers Count -->
                            @php
                                $activeVouchers = $insurance->vouchers()->where('is_active', true)->count();
                            @endphp
                            @if($activeVouchers > 0)
                                <div class="mb-3 flex items-center text-sm text-purple-600">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $activeVouchers }} voucher aktif
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <a href="{{ route('marketing.insurances.show', $insurance) }}" 
                                   class="flex-1 text-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                    Lihat Detail
                                </a>
                                <a href="{{ route('marketing.insurances.edit', $insurance) }}" 
                                   class="flex-1 text-center bg-amber-500 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                    Edit Diskon
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
