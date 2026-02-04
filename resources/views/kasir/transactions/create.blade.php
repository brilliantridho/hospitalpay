<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transaksi Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Info Box -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">✅ Validasi Harga Real-time Aktif</h3>
                        <p class="text-xs text-green-700 mt-1">
                            Harga tindakan medis diambil langsung dari sistem RS Delta Surya (tanggal: {{ now()->format('d/m/Y') }})
                        </p>
                        <p class="text-xs text-green-600 mt-1">
                            📊 Data Asuransi: <strong>{{ $insurances->count() }}</strong> | 
                            Tindakan: <strong>{{ $medicalServices->count() }}</strong>
                        </p>
                        <p class="text-xs text-red-600 mt-1">
                            ⚠️ Transaksi akan DITOLAK jika harga tidak tersedia
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('kasir.transactions.store') }}" method="POST" id="transactionForm">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="patient_name">
                                Nama Pasien *
                            </label>
                            <input type="text" name="patient_name" id="patient_name" value="{{ old('patient_name') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('patient_name') border-red-500 @enderror" required>
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
                                    <option value="{{ $insurance->id }}" 
                                            data-discount="{{ $insurance->discount_percentage }}"
                                            data-max-discount="{{ $insurance->max_discount_amount }}"
                                            data-terms="{{ $insurance->terms }}"
                                            data-limit="{{ $insurance->coverage_limit }}"
                                            {{ old('insurance_id') == $insurance->id ? 'selected' : '' }}>
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
                            <p class="text-xs text-gray-500 mt-1">💰 Pilih asuransi untuk mendapatkan diskon otomatis</p>
                            <div id="insurance-info" class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded hidden">
                                <div class="text-sm">
                                    <div class="font-semibold text-blue-800 mb-2" id="insurance-discount"></div>
                                    <div class="text-xs text-blue-700 mb-1" id="insurance-limit"></div>
                                    <div class="mt-2 text-xs text-gray-700 whitespace-pre-line" id="insurance-terms"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Layanan Medis *
                            </label>
                            <div id="services-container">
                                <div class="service-row mb-3 p-4 border rounded">
                                    <div class="flex gap-2 items-start">
                                        <div class="flex-1">
                                            <select name="services[0][medical_service_id]" class="service-select shadow border rounded w-full py-2 px-3 text-gray-700" required>
                                                <option value="">Pilih Layanan</option>
                                                @foreach($medicalServices as $service)
                                                    <option value="{{ $service->id }}" 
                                                            data-price="{{ $service->price }}"
                                                            data-code="{{ $service->code }}"
                                                            data-source="{{ $service->price_source ?? 'manual' }}"
                                                            data-updated="{{ $service->price_updated_at ? $service->price_updated_at->diffForHumans() : 'tidak pernah' }}">
                                                        {{ $service->name }} - Rp {{ number_format($service->price, 0, ',', '.') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="price-info text-xs mt-1 text-gray-600 hidden"></div>
                                        </div>
                                        <input type="hidden" name="services[0][quantity]" value="1" class="quantity-input">
                                        <button type="button" class="remove-service bg-red-500 text-white px-3 py-2 rounded hover:bg-red-700" style="display:none;">✕</button>
                                    </div>
                                    <div class="subtotal-display text-sm font-semibold text-gray-700 mt-2 hidden"></div>
                                </div>
                            </div>
                            <button type="button" id="add-service" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-2">
                                + Tambah Layanan
                            </button>
                        </div>

                        <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 text-sm">Subtotal Layanan:</span>
                                    <span class="font-semibold text-gray-800" id="display-subtotal">Rp 0</span>
                                </div>
                                <div class="flex justify-between items-center text-green-600" id="discount-row" style="display: none;">
                                    <span class="text-sm">Diskon Asuransi:</span>
                                    <span class="font-semibold" id="display-discount">Rp 0</span>
                                </div>
                                <div class="border-t border-blue-300 pt-2 mt-2"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-800 font-bold">Total Pembayaran:</span>
                                    <span class="font-bold text-2xl text-blue-600" id="display-total">Rp 0</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Proses Transaksi
                            </button>
                            <a href="{{ route('kasir.transactions.index') }}" class="text-gray-600 hover:text-gray-900">
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
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Transaction form script loaded - DOM ready');
            let serviceIndex = 1;
            
            // Show insurance info when selected
            const insuranceSelectEl = document.getElementById('insurance_id');
            if (insuranceSelectEl) {
                insuranceSelectEl.addEventListener('change', function() {
                    console.log('Insurance changed:', this.value);
                    const insuranceInfo = document.getElementById('insurance-info');
                    const discountEl = document.getElementById('insurance-discount');
                    const limitEl = document.getElementById('insurance-limit');
                    const termsEl = document.getElementById('insurance-terms');
                    
                    if (this.value) {
                        const selectedOption = this.options[this.selectedIndex];
                        const discount = parseFloat(selectedOption.dataset.discount) || 0;
                        const maxDiscount = parseFloat(selectedOption.dataset.maxDiscount);
                        const terms = selectedOption.dataset.terms || '';
                        const limit = selectedOption.dataset.limit;
                        
                        if (discount > 0) {
                            let discountText = `💰 Diskon Asuransi: ${discount}%`;
                            if (maxDiscount) {
                                discountText += ` (maksimal Rp ${maxDiscount.toLocaleString('id-ID')})`;
                            }
                            discountEl.textContent = discountText;
                            discountEl.classList.remove('hidden');
                        } else {
                            discountEl.textContent = '⚠️ Tidak ada diskon untuk asuransi ini';
                            discountEl.classList.remove('hidden');
                        }
                        
                        if (limit) {
                            const limitFormatted = parseFloat(limit).toLocaleString('id-ID');
                            limitEl.textContent = `📊 Limit Tanggungan: Rp ${limitFormatted} per tahun`;
                        } else {
                            limitEl.textContent = '📊 Limit Tanggungan: Unlimited (sesuai ketentuan)';
                        }
                        
                        if (terms) {
                            termsEl.textContent = `Ketentuan & Syarat:\n${terms}`;
                        } else {
                            termsEl.textContent = 'Tidak ada ketentuan khusus.';
                        }
                        
                        insuranceInfo.classList.remove('hidden');
                    } else {
                        insuranceInfo.classList.add('hidden');
                    }
                    
                    calculateTotals();
                });
            } else {
                console.error('Insurance select not found!');
            }
            
            // Calculate and display totals with insurance discount
            function calculateTotals() {
                let totalSubtotal = 0;
                
                document.querySelectorAll('.service-row').forEach(row => {
                    const select = row.querySelector('.service-select');
                    const subtotalDisplay = row.querySelector('.subtotal-display');
                    
                    if (select && select.value) {
                        const selectedOption = select.options[select.selectedIndex];
                        const price = parseFloat(selectedOption.dataset.price) || 0;
                        const quantity = 1; // Quantity always 1
                        const subtotal = price * quantity;
                        
                        totalSubtotal += subtotal;
                        
                        if (subtotal > 0) {
                            subtotalDisplay.textContent = `Harga: Rp ${subtotal.toLocaleString('id-ID')}`;
                            subtotalDisplay.classList.remove('hidden');
                        } else {
                            subtotalDisplay.classList.add('hidden');
                        }
                    }
                });
                
                // Calculate discount from insurance
                const insuranceSelect = document.getElementById('insurance_id');
                let discountAmount = 0;
                let finalTotal = totalSubtotal;
                
                if (insuranceSelect.value) {
                    const selectedInsurance = insuranceSelect.options[insuranceSelect.selectedIndex];
                    const discountPercentage = parseFloat(selectedInsurance.dataset.discount) || 0;
                    
                    if (discountPercentage > 0) {
                        discountAmount = (totalSubtotal * discountPercentage) / 100;
                        
                        // Apply max discount amount limit if set (takes priority)
                        const maxDiscountAmount = parseFloat(selectedInsurance.dataset.maxDiscount);
                        if (maxDiscountAmount && discountAmount > maxDiscountAmount) {
                            discountAmount = maxDiscountAmount;
                        }
                        
                        // Apply coverage limit if set
                        const coverageLimit = parseFloat(selectedInsurance.dataset.limit);
                        if (coverageLimit) {
                            discountAmount = Math.min(discountAmount, coverageLimit);
                        }
                    }
                }
                
                finalTotal = totalSubtotal - discountAmount;
                
                // Update display
                document.getElementById('display-subtotal').textContent = `Rp ${totalSubtotal.toLocaleString('id-ID')}`;
                
                if (discountAmount > 0) {
                    document.getElementById('display-discount').textContent = `- Rp ${discountAmount.toLocaleString('id-ID')}`;
                    document.getElementById('discount-row').style.display = 'flex';
                } else {
                    document.getElementById('discount-row').style.display = 'none';
                }
                
                document.getElementById('display-total').textContent = `Rp ${finalTotal.toLocaleString('id-ID')}`;
            }
            
            // Show price info when service is selected
            function updatePriceInfo(selectElement) {
                const row = selectElement.closest('.service-row');
                const priceInfo = row.querySelector('.price-info');
                
                if (selectElement.value) {
                    const selectedOption = selectElement.options[selectElement.selectedIndex];
                    const source = selectedOption.dataset.source;
                    const updated = selectedOption.dataset.updated;
                    
                    let infoText = '';
                    if (source === 'api') {
                        infoText = `✅ Harga dari API RS Delta Surya (diupdate ${updated})`;
                        priceInfo.className = 'price-info text-xs mt-1 text-green-600';
                    } else {
                        infoText = `⚠️ Harga manual (belum sync dari API)`;
                        priceInfo.className = 'price-info text-xs mt-1 text-orange-600';
                    }
                    
                    priceInfo.textContent = infoText;
                    priceInfo.classList.remove('hidden');
                } else {
                    priceInfo.classList.add('hidden');
                }
            }
            
            // Event listeners for service select
            document.getElementById('services-container').addEventListener('change', function(e) {
                if (e.target.classList.contains('service-select')) {
                    updatePriceInfo(e.target);
                    calculateTotals();
                }
            });
            
            // Store medical services data for dynamic rows
            const medicalServicesData = [
                @foreach($medicalServices as $service)
                {
                    id: '{{ $service->id }}',
                    name: '{{ addslashes($service->name) }}',
                    price: '{{ $service->price }}',
                    code: '{{ $service->code }}',
                    source: '{{ $service->price_source ?? 'manual' }}',
                    updated: '{{ $service->price_updated_at ? $service->price_updated_at->diffForHumans() : 'tidak pernah' }}'
                },
                @endforeach
            ];

            // Add service button
            const addServiceBtn = document.getElementById('add-service');
            console.log('Add service button found:', addServiceBtn);
            if (addServiceBtn) {
                addServiceBtn.addEventListener('click', function() {
                    console.log('Add service button clicked!');
                    const container = document.getElementById('services-container');
                    console.log('Container found:', container);
                    const serviceRow = document.createElement('div');
                    serviceRow.className = 'service-row mb-3 p-4 border rounded';
                    
                    // Build options HTML
                    let optionsHTML = '<option value="">Pilih Layanan</option>';
                    medicalServicesData.forEach(service => {
                        const priceFormatted = parseFloat(service.price).toLocaleString('id-ID');
                        optionsHTML += `<option value="${service.id}" 
                                        data-price="${service.price}"
                                        data-code="${service.code}"
                                        data-source="${service.source}"
                                        data-updated="${service.updated}">
                                    ${service.name} - Rp ${priceFormatted}
                                </option>`;
                    });
                    
                    serviceRow.innerHTML = `
                        <div class="flex gap-2 items-start">
                            <div class="flex-1">
                                <select name="services[${serviceIndex}][medical_service_id]" class="service-select shadow border rounded w-full py-2 px-3 text-gray-700" required>
                                    ${optionsHTML}
                                </select>
                                <div class="price-info text-xs mt-1 text-gray-600 hidden"></div>
                            </div>
                            <input type="hidden" name="services[${serviceIndex}][quantity]" value="1" class="quantity-input">
                            <button type="button" class="remove-service bg-red-500 text-white px-3 py-2 rounded hover:bg-red-700">✕</button>
                        </div>
                        <div class="subtotal-display text-sm font-semibold text-gray-700 mt-2 hidden"></div>
                    `;
                    container.appendChild(serviceRow);
                    console.log('New service row added, index:', serviceIndex);
                    serviceIndex++;
                    updateRemoveButtons();
                    calculateTotals();
                });
            } else {
                console.error('Add service button NOT found!');
            }

            document.getElementById('services-container').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-service')) {
                    e.target.closest('.service-row').remove();
                    updateRemoveButtons();
                    calculateTotals();
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
            
            // Initialize on page load
            calculateTotals();
            console.log('All event listeners initialized successfully');
            
        }); // End DOMContentLoaded
    </script>
    @endpush
</x-app-layout>
