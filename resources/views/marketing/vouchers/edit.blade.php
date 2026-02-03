<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Voucher Diskon') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('marketing.vouchers.update', $voucher) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="code">
                                Kode Voucher *
                            </label>
                            <input type="text" name="code" id="code" value="{{ old('code', $voucher->code) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 uppercase @error('code') border-red-500 @enderror" placeholder="Contoh: NEWYEAR2026" required maxlength="50">
                            <p class="text-xs text-gray-600 mt-1">Kode unik untuk voucher, akan diubah menjadi huruf besar otomatis</p>
                            @error('code')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                                Deskripsi
                            </label>
                            <textarea name="description" id="description" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('description') border-red-500 @enderror" placeholder="Deskripsi voucher">{{ old('description', $voucher->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="insurance_id">
                                Asuransi (opsional)
                            </label>
                            <select name="insurance_id" id="insurance_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('insurance_id') border-red-500 @enderror">
                                <option value="">Tidak terikat asuransi (voucher umum)</option>
                                @foreach($insurances as $insurance)
                                    <option value="{{ $insurance->id }}" {{ old('insurance_id', $voucher->insurance_id) == $insurance->id ? 'selected' : '' }}>
                                        {{ $insurance->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-600 mt-1">Kosongkan jika voucher dapat digunakan untuk semua asuransi</p>
                            @error('insurance_id')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="discount_type">
                                Tipe Diskon *
                            </label>
                            <select name="discount_type" id="discount_type" class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('discount_type') border-red-500 @enderror" required>
                                <option value="">Pilih Tipe</option>
                                <option value="percentage" {{ old('discount_type', $voucher->discount_type) == 'percentage' ? 'selected' : '' }}>Persentase</option>
                                <option value="fixed" {{ old('discount_type', $voucher->discount_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            </select>
                            @error('discount_type')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="discount_value">
                                Nilai Diskon *
                            </label>
                            <input type="number" step="0.01" name="discount_value" id="discount_value" value="{{ old('discount_value', $voucher->discount_value) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('discount_value') border-red-500 @enderror" placeholder="Masukkan nilai diskon" required>
                            <p class="text-xs text-gray-600 mt-1">Untuk persentase masukkan nilai 1-100, untuk fixed masukkan nominal rupiah</p>
                            @error('discount_value')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="max_discount">
                                Maksimal Diskon (opsional)
                            </label>
                            <input type="number" step="0.01" name="max_discount" id="max_discount" value="{{ old('max_discount', $voucher->max_discount) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('max_discount') border-red-500 @enderror" placeholder="Kosongkan jika tidak ada batas">
                            <p class="text-xs text-gray-600 mt-1">Hanya untuk tipe persentase</p>
                            @error('max_discount')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="min_transaction">
                                Minimum Transaksi (opsional)
                            </label>
                            <input type="number" step="0.01" name="min_transaction" id="min_transaction" value="{{ old('min_transaction', $voucher->min_transaction) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('min_transaction') border-red-500 @enderror" placeholder="Contoh: 100000">
                            <p class="text-xs text-gray-600 mt-1">Minimum nilai transaksi untuk menggunakan voucher ini</p>
                            @error('min_transaction')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="usage_limit">
                                Batas Penggunaan (opsional)
                            </label>
                            <input type="number" name="usage_limit" id="usage_limit" value="{{ old('usage_limit', $voucher->usage_limit) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('usage_limit') border-red-500 @enderror" placeholder="Contoh: 100">
                            <p class="text-xs text-gray-600 mt-1">Jumlah maksimal voucher dapat digunakan. Kosongkan jika tidak terbatas</p>
                            @error('usage_limit')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4 bg-gray-100 p-3 rounded">
                            <p class="text-sm text-gray-700"><strong>Total Penggunaan:</strong> {{ $voucher->used_count }} / {{ $voucher->usage_limit ?? 'Tidak Terbatas' }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="valid_from">
                                Berlaku Dari (opsional)
                            </label>
                            <input type="date" name="valid_from" id="valid_from" value="{{ old('valid_from', $voucher->valid_from?->format('Y-m-d')) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('valid_from') border-red-500 @enderror">
                            @error('valid_from')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="valid_until">
                                Berlaku Sampai (opsional)
                            </label>
                            <input type="date" name="valid_until" id="valid_until" value="{{ old('valid_until', $voucher->valid_until?->format('Y-m-d')) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('valid_until') border-red-500 @enderror">
                            @error('valid_until')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $voucher->is_active) ? 'checked' : '' }} class="rounded">
                                <span class="ml-2 text-gray-700">Aktif</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update
                            </button>
                            <a href="{{ route('marketing.vouchers.index') }}" class="text-gray-600 hover:text-gray-900">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
