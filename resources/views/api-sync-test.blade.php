<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API Sync</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-2xl font-bold mb-4">API External Sync - Test Page</h1>
            
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-2">Current Configuration:</h2>
                <div class="bg-gray-50 p-4 rounded">
                    <p><strong>Base URL:</strong> {{ config('services.external_api.base_url') }}</p>
                    <p><strong>Email:</strong> {{ config('services.external_api.email') }}</p>
                    <p><strong>Password:</strong> {{ config('services.external_api.password') ? '••••••••' : '(not set)' }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold mb-2">1. Test Authentication</h3>
                    <button onclick="testAuth()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        Test Auth
                    </button>
                    <div id="auth-result" class="mt-2"></div>
                </div>

                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold mb-2">2. Sync Insurances</h3>
                    <form method="POST" action="{{ route('api-sync.sync-insurances') }}" onsubmit="return confirm('Sync data asuransi?')">
                        @csrf
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            Sync Insurances
                        </button>
                    </form>
                </div>

                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold mb-2">3. Sync Medical Services</h3>
                    <form method="POST" action="{{ route('api-sync.sync-medical-services') }}" onsubmit="return confirm('Sync data tindakan medis?')">
                        @csrf
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            Sync Medical Services
                        </button>
                    </form>
                </div>

                <div class="pb-4">
                    <h3 class="text-lg font-semibold mb-2">4. Sync All Data</h3>
                    <form method="POST" action="{{ route('api-sync.sync-all') }}" onsubmit="return confirm('Sync semua data?')">
                        @csrf
                        <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                            Sync All
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                {!! nl2br(session('warning')) !!}
            </div>
        @endif
    </div>

    <script>
        async function testAuth() {
            const resultDiv = document.getElementById('auth-result');
            resultDiv.innerHTML = '<p class="text-blue-500">Testing authentication...</p>';
            
            try {
                const response = await fetch('{{ route('api-sync.test-auth') }}');
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mt-2">
                            <p><strong>✓ Authentication Successful!</strong></p>
                            <p>Token: ${data.token_preview}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-2">
                            <p><strong>✗ Authentication Failed</strong></p>
                            <p>${data.message}</p>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-2">
                        <p><strong>✗ Error:</strong> ${error.message}</p>
                    </div>
                `;
            }
        }
    </script>
</body>
</html>
