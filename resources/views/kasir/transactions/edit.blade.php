<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Transaksi - {{ $transaction->transaction_code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('kasir.transactions.update', $transaction) }}" method="POST" id="transactionForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="patient_name">
                                Nama Pasien *
                            </label>
                            <input type="text" name="patient_name" id="patient_name" value="{{ old('patient_name', $transaction->patient_name) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('patient_name') border-red-500 @enderror" required>
                            @error('patient_name')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="insurance_id">
                                Asuransi *
                            </label>
                            <select name="insurance_id" id="insurance_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('insurance_id') border-red-500 @enderror" required>
                                <option value="">-- Pilih Asuransi --</option>
                                @foreach($insurances as $insurance)
                                    <option value="{{ $insurance->id }}" {{ old('insurance_id', $transaction->insurance_id) == $insurance->id ? 'selected' : '' }}>
                                        {{ $insurance->name }}
                                        @if($insurance->discount_percentage > 0)
                                            - Diskon {{ number_format($insurance->discount_percentage, 0) }}%
                                            @if($insurance->max_discount_amount)
                                                (maks Rp {{ number_format($insurance->max_discount_amount, 0, ',', '.') }})
                                            @endif
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('insurance_id')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Layanan Medis *
                            </label>
                            <div id="services-container">
                                @foreach($transaction->details as $index => $detail)
                                <div class="service-row mb-3 p-4 border rounded">
                                    <div class="flex gap-2 items-start">
                                        <div class="flex-1">
                                            <select name="services[{{ $index }}][medical_service_id]" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                                                <option value="">Pilih Layanan</option>
                                                @foreach($medicalServices as $service)
                                                    <option value="{{ $service->id }}" data-price="{{ $service->price }}" {{ $detail->medical_service_id == $service->id ? 'selected' : '' }}>
                                                        {{ $service->name }} - Rp {{ number_format($service->price, 0, ',', '.') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="hidden" name="services[{{ $index }}][quantity]" value="1">
                                        <button type="button" class="remove-service bg-red-500 text-white px-3 py-2 rounded hover:bg-red-700" style="{{ count($transaction->details) > 1 ? '' : 'display:none;' }}">✕</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" id="add-service" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-2">
                                + Tambah Layanan
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update
                            </button>
                            <a href="{{ route('kasir.transactions.show', $transaction) }}" class="text-gray-600 hover:text-gray-900">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let serviceIndex = {{ count($transaction->details) }};
        
        // Store medical services data for dynamic rows
        const medicalServicesData = [
            @foreach($medicalServices as $service)
            {
                id: '{{ $service->id }}',
                name: '{{ addslashes($service->name) }}',
                price: '{{ $service->price }}'
            },
            @endforeach
        ];
        
        document.getElementById('add-service').addEventListener('click', function() {
            const container = document.getElementById('services-container');
            const serviceRow = document.createElement('div');
            serviceRow.className = 'service-row mb-3 p-4 border rounded';
            
            // Build options HTML
            let optionsHTML = '<option value="">Pilih Layanan</option>';
            medicalServicesData.forEach(service => {
                const priceFormatted = parseFloat(service.price).toLocaleString('id-ID');
                optionsHTML += `<option value="${service.id}" data-price="${service.price}">
                            ${service.name} - Rp ${priceFormatted}
                        </option>`;
            });
            
            serviceRow.innerHTML = `
                <div class="flex gap-2 items-start">
                    <div class="flex-1">
                        <select name="services[${serviceIndex}][medical_service_id]" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                            ${optionsHTML}
                        </select>
                    </div>
                    <input type="hidden" name="services[${serviceIndex}][quantity]" value="1">
                    <button type="button" class="remove-service bg-red-500 text-white px-3 py-2 rounded hover:bg-red-700">✕</button>
                </div>
            `;
            container.appendChild(serviceRow);
            serviceIndex++;
            updateRemoveButtons();
        });

        document.getElementById('services-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-service')) {
                e.target.closest('.service-row').remove();
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            const serviceRows = document.querySelectorAll('.service-row');
            serviceRows.forEach((row, index) => {
                const removeBtn = row.querySelector('.remove-service');
                if (serviceRows.length > 1) {
                    removeBtn.style.display = 'block';
                } else {
                    removeBtn.style.display = 'none';
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
