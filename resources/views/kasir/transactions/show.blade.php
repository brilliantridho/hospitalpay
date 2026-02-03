<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Transaksi - {{ $transaction->transaction_code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-gray-600">Kode Transaksi</p>
                            <p class="font-bold">{{ $transaction->transaction_code }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Status</p>
                            <p class="font-bold">
                                @if($transaction->payment_status === 'paid')
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded">Lunas</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Pending</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-600">Nama Pasien</p>
                            <p class="font-bold">{{ $transaction->patient_name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Asuransi</p>
                            <p class="font-bold">{{ $transaction->insurance->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Kasir</p>
                            <p class="font-bold">{{ $transaction->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Tanggal</p>
                            <p class="font-bold">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold mb-4">Detail Layanan</h3>
                    <table class="min-w-full divide-y divide-gray-200 mb-6">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Layanan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diskon/Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($transaction->details as $detail)
                                <tr>
                                    <td class="px-6 py-4">{{ $detail->medicalService->name }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">{{ $detail->quantity }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($detail->discount_per_item, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="border-t pt-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700">Subtotal:</span>
                            <span class="font-bold">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700">Diskon:</span>
                            <span class="font-bold text-red-600">- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold border-t pt-2">
                            <span>Total:</span>
                            <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-2">
                        @if($transaction->payment_status === 'pending')
                            <form action="{{ route('kasir.transactions.pay', $transaction) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Bayar Sekarang
                                </button>
                            </form>
                            <a href="{{ route('kasir.transactions.edit', $transaction) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Edit
                            </a>
                        @else
                            <a href="{{ route('kasir.transactions.print', $transaction) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Cetak PDF
                            </a>
                        @endif
                        <a href="{{ route('kasir.transactions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
