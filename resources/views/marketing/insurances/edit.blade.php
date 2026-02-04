<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Pengaturan Diskon Asuransi') }}
            </h2>
            <a href="{{ route('marketing.insurances.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Insurance Info Header -->
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-bold text-blue-900 mb-1">{{ $insurance->name }}</h3>
                        @if($insurance->code)
                            <p class="text-sm text-blue-700">Kode: {{ $insurance->code }}</p>
                        @endif
                        @if($insurance->description)
                            <p class="text-sm text-blue-600 mt-2">{{ $insurance->description }}</p>
                        @endif
                    </div>

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('marketing.insurances.update', $insurance) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Hidden field for name (no change) -->
                        <input type="hidden" name="name" value="{{ $insurance->name }}">

                        <!-- Discount Percentage -->
                        <div class="mb-6">
                            <label for="discount_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                Persentase Diskon <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="discount_percentage" 
                                       id="discount_percentage" 
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       value="{{ old('discount_percentage', $insurance->discount_percentage) }}"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-8"
                                       required>
                                <span class="absolute right-3 top-2 text-gray-500 font-bold">%</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Masukkan nilai antara 0 - 100</p>
                            @error('discount_percentage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Max Discount Amount -->
                        <div class="mb-6">
                            <label for="max_discount_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Maksimal Nominal Diskon
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500 font-bold">Rp</span>
                                <input type="number" 
                                       name="max_discount_amount" 
                                       id="max_discount_amount" 
                                       step="0.01"
                                       min="0"
                                       value="{{ old('max_discount_amount', $insurance->max_discount_amount) }}"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-12"
                                       placeholder="Kosongkan jika tidak ada batas">
                                <span class="absolute right-3 top-2 text-gray-400 text-xs">.00</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Opsional. Contoh: 300000 untuk maksimal diskon Rp 300.000</p>
                            @error('max_discount_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Example Calculation -->
                        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-green-900 mb-2">💡 Contoh Perhitungan:</h4>
                            <div class="text-sm text-green-800">
                                <p class="mb-2">Jika diatur diskon <strong>30%</strong> dengan maksimal <strong>Rp 300.000</strong>:</p>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li>Total tagihan Rp 500.000 → Diskon 30% = Rp 150.000 ✓</li>
                                    <li>Total tagihan Rp 1.000.000 → Diskon 30% = Rp 300.000 ✓</li>
                                    <li>Total tagihan Rp 2.000.000 → Diskon 30% = Rp 600.000, <strong>dibatasi jadi Rp 300.000</strong> ✓</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Coverage Limit (readonly info) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Limit Tanggungan
                            </label>
                            <div class="block w-full rounded-md border-gray-200 bg-gray-50 shadow-sm p-2 text-gray-600">
                                @if($insurance->coverage_limit)
                                    Rp {{ number_format($insurance->coverage_limit, 0, ',', '.') }} / tahun
                                @else
                                    Unlimited (sesuai ketentuan)
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Untuk mengubah limit tanggungan, hubungi administrator sistem</p>
                        </div>

                        <!-- Terms (optional) -->
                        <div class="mb-6">
                            <label for="terms" class="block text-sm font-medium text-gray-700 mb-2">
                                Ketentuan
                            </label>
                            <textarea name="terms" 
                                      id="terms" 
                                      rows="4"
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Ketentuan penggunaan asuransi...">{{ old('terms', $insurance->terms) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Opsional. Syarat dan ketentuan penggunaan asuransi</p>
                            @error('terms')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Hidden field for coverage_limit (no change) -->
                        <input type="hidden" name="coverage_limit" value="{{ $insurance->coverage_limit }}">

                        <!-- Submit Buttons -->
                        <div class="flex gap-3">
                            <button type="submit"
                                    class="flex-1 bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                                💾 Simpan Perubahan
                            </button>
                            <a href="{{ route('marketing.insurances.index') }}"
                               class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition-colors text-center">
                                ✖ Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current Settings Display -->
            <div class="mt-6 bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Pengaturan Saat Ini:</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white p-3 rounded border border-gray-200">
                            <div class="text-xs text-gray-500">Persentase Diskon</div>
                            <div class="text-2xl font-bold text-blue-600">{{ number_format($insurance->discount_percentage, 1) }}%</div>
                        </div>
                        <div class="bg-white p-3 rounded border border-gray-200">
                            <div class="text-xs text-gray-500">Maks. Nominal Diskon</div>
                            <div class="text-2xl font-bold text-green-600">
                                @if($insurance->max_discount_amount)
                                    Rp {{ number_format($insurance->max_discount_amount, 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400 text-lg">Tidak dibatasi</span>
                                @endif
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded border border-gray-200">
                            <div class="text-xs text-gray-500">Limit Tanggungan</div>
                            <div class="text-lg font-bold text-purple-600">
                                @if($insurance->coverage_limit)
                                    Rp {{ number_format($insurance->coverage_limit / 1000000, 0) }} Jt
                                @else
                                    <span class="text-gray-400">Unlimited</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
