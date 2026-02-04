<!DOCTYPE html>
<html>
<head>
    <title>Test Voucher Check</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .result { margin-top: 20px; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; }
        input, button { padding: 10px; margin: 5px; }
        button { background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; }
        button:hover { background: #0056b3; }
        pre { background: #f4f4f4; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Test Voucher Check Function</h1>
    
    <div>
        <h3>Available Vouchers:</h3>
        <ul>
            <li>NEWYEAR2026</li>
            <li>WELCOME100</li>
            <li>RAMADAN50</li>
            <li>ASURAN2026</li>
        </ul>
    </div>

    <div>
        <label>Voucher Code:</label>
        <input type="text" id="voucherCode" value="NEWYEAR2026" style="text-transform: uppercase;">
    </div>
    
    <div>
        <label>Subtotal:</label>
        <input type="number" id="subtotal" value="100000">
    </div>
    
    <div>
        <label>Insurance ID (optional):</label>
        <input type="text" id="insuranceId" value="">
    </div>
    
    <div>
        <button onclick="testVoucher()">Check Voucher</button>
        <button onclick="testWithConsole()">Check with Console Log</button>
    </div>
    
    <div id="result"></div>

    <script>
        function getCsrfToken() {
            // Try to get from meta tag
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) return meta.getAttribute('content');
            
            // Try to get from cookie
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'XSRF-TOKEN') {
                    return decodeURIComponent(value);
                }
            }
            return null;
        }

        async function testVoucher() {
            const resultDiv = document.getElementById('result');
            const voucherCode = document.getElementById('voucherCode').value.trim().toUpperCase();
            const subtotal = parseFloat(document.getElementById('subtotal').value);
            const insuranceId = document.getElementById('insuranceId').value || null;
            
            resultDiv.innerHTML = '<div class="info">🔄 Checking voucher...</div>';
            
            try {
                const response = await fetch('/kasir/transactions/check-voucher', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken() || ''
                    },
                    body: JSON.stringify({
                        code: voucherCode,
                        subtotal: subtotal,
                        insurance_id: insuranceId
                    })
                });
                
                const data = await response.json();
                
                if (data.valid) {
                    resultDiv.innerHTML = `
                        <div class="success">
                            <h3>✅ Voucher Valid!</h3>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error">
                            <h3>❌ ${data.message}</h3>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error">
                        <h3>❌ Error</h3>
                        <p>${error.message}</p>
                        <p>Make sure you are logged in as 'kasir' user</p>
                    </div>
                `;
                console.error('Error:', error);
            }
        }
        
        function testWithConsole() {
            console.log('Testing voucher check...');
            testVoucher();
        }
        
        // Auto-uppercase voucher code
        document.getElementById('voucherCode').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>
