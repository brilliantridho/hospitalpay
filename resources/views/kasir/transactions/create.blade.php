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
                                Asuransi (opsional)
                            </label>
                            <select name="insurance_id" id="insurance_id" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                <option value="">Tidak Pakai Asuransi</option>
                                @foreach($insurances as $insurance)
                                    <option value="{{ $insurance->id }}" 
                                            data-discount="{{ $insurance->discount_percentage }}"
                                            data-terms="{{ $insurance->terms }}"
                                            data-limit="{{ $insurance->coverage_limit }}"
                                            {{ old('insurance_id') == $insurance->id ? 'selected' : '' }}>
                                        {{ $insurance->name }}
                                        @if($insurance->discount_percentage > 0)
                                            (Diskon {{ number_format($insurance->discount_percentage, 0) }}%)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div id="insurance-info" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded hidden">
                                <div class="text-sm">
                                    <div class="font-semibold text-blue-800 mb-1" id="insurance-discount"></div>
                                    <div class="text-xs text-blue-700" id="insurance-limit"></div>
                                    <div class="mt-2 text-xs text-gray-700 whitespace-pre-line" id="insurance-terms"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="voucher_code">
                                Kode Voucher (opsional)
                            </label>
                            <div class="flex gap-2">
                                <input type="text" 
                                       name="voucher_code" 
                                       id="voucher_code" 
                                       value="{{ old('voucher_code') }}" 
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 uppercase"
                                       placeholder="Masukkan kode voucher">
                                <button type="button" 
                                        id="check-voucher-btn"
                                        class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                    Cek Voucher
                                </button>
                            </div>
                            <div id="voucher-info" class="mt-2 hidden">
                                <!-- Will be populated by JavaScript -->
                            </div>
                            <p class="text-xs text-gray-500 mt-1">💡 Tip: Masukkan kode voucher untuk mendapatkan diskon tambahan</p>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Layanan Medis *
                            </label>
                            <div id="services-container">
                                <div class="service-row mb-3 p-4 border rounded">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="md:col-span-2">
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
                                        <div class="flex gap-2">
                                            <input type="number" name="services[0][quantity]" min="1" value="1" class="quantity-input shadow border rounded w-full py-2 px-3 text-gray-700" placeholder="Qty" required>
                                            <button type="button" class="remove-service bg-red-500 text-white px-3 py-2 rounded hover:bg-red-700" style="display:none;">X</button>
                                        </div>
                                    </div>
                                    <div class="subtotal-display text-sm font-semibold text-gray-700 mt-2 hidden"></div>
                                </div>
                            </div>
                            <!-- <button type="button" id="add-service" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-2">
                                Tambah Layanan
                            </button> -->
                        </div>

                        <div class="mb-6 p-4 bg-gray-50 rounded">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-700">Subtotal:</span>
                                <span class="font-semibold text-gray-900" id="total-subtotal">Rp 0</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Simpan
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
        let serviceIndex = 1;
        let currentVoucher = null;
        
        // Check voucher via AJAX
        document.getElementById('check-voucher-btn').addEventListener('click', function() {
            const voucherCode = document.getElementById('voucher_code').value.trim();
            const insuranceId = document.getElementById('insurance_id').value;
            const voucherInfo = document.getElementById('voucher-info');
            
            if (!voucherCode) {
                voucherInfo.innerHTML = `
                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-700">
                        ⚠️ Masukkan kode voucher terlebih dahulu
                    </div>
                `;
                voucherInfo.classList.remove('hidden');
                return;
            }
            
            // Calculate current subtotal
            let subtotal = 0;
            document.querySelectorAll('.service-row').forEach(row => {
                const select = row.querySelector('.service-select');
                const quantityInput = row.querySelector('.quantity-input');
                if (select && select.value && quantityInput) {
                    const price = parseFloat(select.options[select.selectedIndex].dataset.price) || 0;
                    const quantity = parseInt(quantityInput.value) || 0;
                    subtotal += (price * quantity);
                }
            });
            
            if (subtotal <= 0) {
                voucherInfo.innerHTML = `
                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-700">
                        ⚠️ Pilih tindakan medis terlebih dahulu untuk mengecek voucher
                    </div>
                `;
                voucherInfo.classList.remove('hidden');
                return;
            }
            
            // Show loading
            voucherInfo.innerHTML = `
                <div class="p-3 bg-gray-50 border border-gray-200 rounded text-sm text-gray-700">
                    🔄 Memeriksa voucher...
                </div>
            `;
            voucherInfo.classList.remove('hidden');
            
            // AJAX request
            fetch('{{ route("kasir.transactions.check-voucher") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    code: voucherCode,
                    subtotal: subtotal,
                    insurance_id: insuranceId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    currentVoucher = data.voucher;
                    
                    let usageInfo = '';
                    if (data.voucher.usage_remaining !== null) {
                        usageInfo = `<div class="text-xs text-purple-600 mt-1">📊 Sisa penggunaan: ${data.voucher.usage_remaining}x</div>`;
                    }
                    
                    voucherInfo.innerHTML = `
                        <div class="p-3 bg-green-50 border border-green-200 rounded">
                            <div class="text-sm font-semibold text-green-800 mb-1">✅ ${data.message}</div>
                            <div class="text-sm text-green-700">
                                <div class="font-bold">Kode: ${data.voucher.code}</div>
                                ${data.voucher.description ? `<div class="text-xs">${data.voucher.description}</div>` : ''}
                                <div class="mt-2 text-xs">
                                    <div>💰 Diskon: ${data.voucher.discount_text}</div>
                                    <div>💵 Potongan: Rp ${data.voucher.discount_amount.toLocaleString('id-ID')}</div>
                                    ${data.voucher.min_transaction ? `<div>📊 Min. Transaksi: Rp ${parseFloat(data.voucher.min_transaction).toLocaleString('id-ID')}</div>` : ''}
                                    ${usageInfo}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Auto select insurance from voucher
                    if (data.voucher.insurance_id && !insuranceId) {
                        document.getElementById('insurance_id').value = data.voucher.insurance_id;
                        document.getElementById('insurance_id').dispatchEvent(new Event('change'));
                    }
                    
                    calculateTotals();
                } else {
                    currentVoucher = null;
                    voucherInfo.innerHTML = `
                        <div class="p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                            ❌ ${data.message}
                        </div>
                    `;
                }
                voucherInfo.classList.remove('hidden');
            })
            .catch(error => {
                currentVoucher = null;
                voucherInfo.innerHTML = `
                    <div class="p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                        ❌ Terjadi kesalahan saat memeriksa voucher
                    </div>
                `;
                voucherInfo.classList.remove('hidden');
                console.error('Error:', error);
            });
        });
        
        // Clear voucher info when code changes
        document.getElementById('voucher_code').addEventListener('input', function() {
            currentVoucher = null;
            document.getElementById('voucher-info').classList.add('hidden');
        });
        
        // Convert voucher code to uppercase
        document.getElementById('voucher_code').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Show insurance info when selected
        document.getElementById('insurance_id').addEventListener('change', function() {
            const insuranceInfo = document.getElementById('insurance-info');
            const discountEl = document.getElementById('insurance-discount');
            const limitEl = document.getElementById('insurance-limit');
            const termsEl = document.getElementById('insurance-terms');
            
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const discount = parseFloat(selectedOption.dataset.discount) || 0;
                const terms = selectedOption.dataset.terms || '';
                const limit = selectedOption.dataset.limit;
                
                if (discount > 0) {
                    discountEl.textContent = `💰 Diskon Asuransi: ${discount}%`;
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
                calculateTotals();
            } else {
                insuranceInfo.classList.add('hidden');
                calculateTotals();
            }
        });
        
        // Calculate and display totals with insurance discount and voucher
        function calculateTotals() {
            let totalSubtotal = 0;
            
            document.querySelectorAll('.service-row').forEach(row => {
                const select = row.querySelector('.service-select');
                const quantityInput = row.querySelector('.quantity-input');
                const subtotalDisplay = row.querySelector('.subtotal-display');
                
                if (select && select.value && quantityInput) {
                    const selectedOption = select.options[select.selectedIndex];
                    const price = parseFloat(selectedOption.dataset.price) || 0;
                    const quantity = parseInt(quantityInput.value) || 0;
                    const subtotal = price * quantity;
                    
                    totalSubtotal += subtotal;
                    
                    if (subtotal > 0) {
                        subtotalDisplay.textContent = `Subtotal: Rp ${subtotal.toLocaleString('id-ID')}`;
                        subtotalDisplay.classList.remove('hidden');
                    } else {
                        subtotalDisplay.classList.add('hidden');
                    }
                }
            });
            
            // Calculate discount from insurance or voucher
            const insuranceSelect = document.getElementById('insurance_id');
            const voucherCodeInput = document.getElementById('voucher_code');
            let discountAmount = 0;
            let discountSource = '';
            let finalTotal = totalSubtotal;
            
            // Priority 1: Manual voucher code (if entered and validated)
            if (voucherCodeInput.value && currentVoucher) {
                // Calculate voucher discount based on type
                if (currentVoucher.discount_type === 'percentage') {
                    let voucherDiscount = (totalSubtotal * currentVoucher.discount_value) / 100;
                    if (currentVoucher.max_discount) {
                        voucherDiscount = Math.min(voucherDiscount, currentVoucher.max_discount);
                    }
                    discountAmount = voucherDiscount;
                } else {
                    discountAmount = currentVoucher.discount_value;
                }
                discountSource = `voucher ${currentVoucher.code}`;
            }
            // Priority 2: Insurance discount (if no voucher code)
            else if (insuranceSelect.value) {
                const selectedInsurance = insuranceSelect.options[insuranceSelect.selectedIndex];
                const discountPercentage = parseFloat(selectedInsurance.dataset.discount) || 0;
                
                if (discountPercentage > 0) {
                    discountAmount = (totalSubtotal * discountPercentage) / 100;
                    
                    // Apply coverage limit if set
                    const coverageLimit = parseFloat(selectedInsurance.dataset.limit);
                    if (coverageLimit) {
                        discountAmount = Math.min(discountAmount, coverageLimit);
                    }
                    
                    discountSource = 'asuransi';
                }
            }
            
            finalTotal = totalSubtotal - discountAmount;
            
            // Update display
            let displayText = `Rp ${totalSubtotal.toLocaleString('id-ID')}`;
            if (discountAmount > 0) {
                displayText += ` - Rp ${discountAmount.toLocaleString('id-ID')} (diskon ${discountSource})`;
                displayText += ` = Rp ${finalTotal.toLocaleString('id-ID')}`;
            }
            
            document.getElementById('total-subtotal').textContent = displayText;
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
        
        // Event listeners for all selects and quantity inputs
        document.getElementById('services-container').addEventListener('change', function(e) {
            if (e.target.classList.contains('service-select')) {
                updatePriceInfo(e.target);
                calculateTotals();
            } else if (e.target.classList.contains('quantity-input')) {
                calculateTotals();
            }
        });
        
        document.getElementById('services-container').addEventListener('input', function(e) {
            if (e.target.classList.contains('quantity-input')) {
                calculateTotals();
            }
        });
        
        document.getElementById('add-service').addEventListener('click', function() {
            const container = document.getElementById('services-container');
            const serviceRow = document.createElement('div');
            serviceRow.className = 'service-row mb-3 p-4 border rounded';
            serviceRow.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <select name="services[${serviceIndex}][medical_service_id]" class="service-select shadow border rounded w-full py-2 px-3 text-gray-700" required>
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
                    <div class="flex gap-2">
                        <input type="number" name="services[${serviceIndex}][quantity]" min="1" value="1" class="quantity-input shadow border rounded w-full py-2 px-3 text-gray-700" placeholder="Qty" required>
                        <button type="button" class="remove-service bg-red-500 text-white px-3 py-2 rounded hover:bg-red-700">X</button>
                    </div>
                </div>
                <div class="subtotal-display text-sm font-semibold text-gray-700 mt-2 hidden"></div>
            `;
            container.appendChild(serviceRow);
            serviceIndex++;
            updateRemoveButtons();
        });

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
    </script>
    @endpush
</x-app-layout>
