<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nusandana Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-3xl font-bold mb-2">🧪 Nusandana Payment Gateway Test</h1>
            <p class="text-gray-600">Test your Nusandana integration</p>
        </div>

        <div class="bg-blue-50 rounded-lg p-6 mb-6">
            <h3 class="font-bold mb-2">Configuration:</h3>
            <p><strong>Merchant:</strong> {{ env('NUSANDANA_MERCHANT_NO') }}</p>
            <p><strong>API:</strong> {{ env('NUSANDANA_API_BASE_URL') }}</p>
            <p><strong>Signature Key:</strong> {{ substr(env('NUSANDANA_SIGNATURE_KEY'), 0, 10) }}...</p>
        </div>

        <!-- Test 1: Balance -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">📊 Test 1: Query Balance</h2>
            <p class="text-gray-600 mb-4">Tests API connection, authentication, and signature generation</p>
            
            <button 
                onclick="testBalance()"
                id="balance-btn"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition"
            >
                Run Balance Test
            </button>

            <div id="balance-result" class="mt-4 hidden">
                <div id="balance-success" class="hidden p-4 bg-green-50 border-l-4 border-green-500 rounded mb-4">
                    <h4 class="font-bold text-green-900 mb-2">✅ Success!</h4>
                    <p class="text-green-800"><strong>Merchant:</strong> <span id="balance-merchant"></span></p>
                    <p class="text-green-800"><strong>Balance:</strong> Rp <span id="balance-amount"></span></p>
                </div>

                <div id="balance-error" class="hidden p-4 bg-red-50 border-l-4 border-red-500 rounded mb-4">
                    <h4 class="font-bold text-red-900 mb-2">❌ Error</h4>
                    <p class="text-red-800" id="balance-error-msg"></p>
                </div>

                <details class="mt-4">
                    <summary class="cursor-pointer font-semibold text-gray-700 hover:text-gray-900">View Raw Response</summary>
                    <pre id="balance-output" class="mt-2 bg-gray-50 p-4 rounded text-sm overflow-x-auto"></pre>
                </details>
            </div>
        </div>

        <!-- Test 2: Payment -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">💳 Test 2: Create Payment</h2>
            <p class="text-gray-600 mb-4">Creates a test payment and generates QR code</p>
            
            <form id="payment-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Amount (IDR)</label>
                    <input 
                        type="number" 
                        name="amount" 
                        value="10000"
                        min="1000"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Payment Method</label>
                    <select 
                        name="payment_method"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                        onchange="toggleBankCode(this.value)"
                    >
                        <option value="qrcode">QRIS (Recommended)</option>
                        <option value="emoney">E-Money / E-Wallet</option>
                        <option value="static">Static QRIS</option>
                    </select>
                </div>

                <div id="bank-code-section" class="hidden">
                    <label class="block text-sm font-medium mb-2">E-Wallet</label>
                    <select 
                        name="bank_code"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Choose...</option>
                        <option value="DANA">DANA</option>
                        <option value="OVO">OVO</option>
                        <option value="GOPAY">GoPay</option>
                        <option value="SHOPEE">ShopeePay</option>
                        <option value="LINKAJA">LinkAja</option>
                    </select>
                </div>

                <button 
                    type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition"
                >
                    Create Test Payment
                </button>
            </form>

            <div id="payment-result" class="mt-4 hidden">
                <div id="payment-success" class="hidden p-4 bg-green-50 border-l-4 border-green-500 rounded mb-4">
                    <h4 class="font-bold text-green-900 mb-2">✅ Payment Created!</h4>
                    <p class="text-green-800"><strong>Order:</strong> <span id="payment-order"></span></p>
                    <p class="text-green-800"><strong>Amount:</strong> Rp <span id="payment-amount"></span></p>
                </div>

                <div id="payment-error" class="hidden p-4 bg-red-50 border-l-4 border-red-500 rounded mb-4">
                    <h4 class="font-bold text-red-900 mb-2">❌ Error</h4>
                    <p class="text-red-800" id="payment-error-msg"></p>
                </div>

                <div id="payment-details" class="hidden">
                    <div id="qr-section" class="mb-4 text-center p-4 bg-white border rounded hidden">
                        <p class="text-sm text-gray-600 mb-2 font-semibold">Scan QR Code to Pay:</p>
                        <img id="qr-code" src="" alt="QR Code" class="mx-auto border-2 border-gray-300 rounded-lg shadow" style="max-width: 300px;">
                        <p class="text-xs text-gray-500 mt-2">Scan with your e-wallet app</p>
                    </div>

                    <div id="url-section" class="hidden">
                        <a id="payment-link" href="" target="_blank" 
                           class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-3 px-6 rounded-lg transition">
                            Open Payment Page →
                        </a>
                    </div>
                </div>

                <details class="mt-4">
                    <summary class="cursor-pointer font-semibold text-gray-700 hover:text-gray-900">View Raw Response</summary>
                    <pre id="payment-output" class="mt-2 bg-gray-50 p-4 rounded text-sm overflow-x-auto"></pre>
                </details>
            </div>
        </div>

        <!-- Warning -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-6">
            <h3 class="font-bold text-yellow-900 mb-2">⚠️ Important Notes:</h3>
            <ul class="list-disc list-inside text-yellow-800 space-y-1 text-sm">
                <li>Test payments are REAL transactions</li>
                <li>Minimum amount is usually Rp 1,000</li>
                <li>QR codes expire after a certain time</li>
                <li>Complete the payment to test webhooks</li>
            </ul>
        </div>

    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function toggleBankCode(method) {
            const section = document.getElementById('bank-code-section');
            section.classList.toggle('hidden', method !== 'emoney');
        }

        async function testBalance() {
            const resultDiv = document.getElementById('balance-result');
            const successDiv = document.getElementById('balance-success');
            const errorDiv = document.getElementById('balance-error');
            const outputPre = document.getElementById('balance-output');
            const btn = document.getElementById('balance-btn');
            
            // Show loading
            btn.disabled = true;
            btn.textContent = 'Testing...';
            
            resultDiv.classList.remove('hidden');
            successDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');
            outputPre.textContent = 'Loading...';

            try {
                const response = await fetch('/test-nusandana/balance', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response. Check Laravel logs.');
                }

                const result = await response.json();
                outputPre.textContent = JSON.stringify(result, null, 2);

                if (result.success) {
                    successDiv.classList.remove('hidden');
                    document.getElementById('balance-merchant').textContent = result.merchant_no;
                    document.getElementById('balance-amount').textContent = 
                        new Intl.NumberFormat('id-ID').format(result.balance);
                } else {
                    errorDiv.classList.remove('hidden');
                    document.getElementById('balance-error-msg').textContent = 
                        result.message + (result.code ? ` (Code: ${result.code})` : '');
                }

            } catch (error) {
                errorDiv.classList.remove('hidden');
                document.getElementById('balance-error-msg').textContent = error.message;
                outputPre.textContent = 'Error: ' + error.message;
            } finally {
                btn.disabled = false;
                btn.textContent = 'Run Balance Test';
            }
        }

        document.getElementById('payment-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const resultDiv = document.getElementById('payment-result');
            const successDiv = document.getElementById('payment-success');
            const errorDiv = document.getElementById('payment-error');
            const detailsDiv = document.getElementById('payment-details');
            const outputPre = document.getElementById('payment-output');
            
            resultDiv.classList.remove('hidden');
            successDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');
            detailsDiv.classList.add('hidden');
            outputPre.textContent = 'Creating payment...';

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('/test-nusandana/payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response. Check Laravel logs.');
                }

                const result = await response.json();
                outputPre.textContent = JSON.stringify(result, null, 2);

                if (result.success) {
                    successDiv.classList.remove('hidden');
                    detailsDiv.classList.remove('hidden');
                    
                    document.getElementById('payment-order').textContent = result.platform_order_no || 'N/A';
                    document.getElementById('payment-amount').textContent = 
                        new Intl.NumberFormat('id-ID').format(result.amount);

                    if (result.qr_code) {
                        document.getElementById('qr-section').classList.remove('hidden');
                        document.getElementById('qr-code').src = result.qr_code;
                    }

                    if (result.payment_url) {
                        document.getElementById('url-section').classList.remove('hidden');
                        document.getElementById('payment-link').href = result.payment_url;
                    }
                } else {
                    errorDiv.classList.remove('hidden');
                    document.getElementById('payment-error-msg').textContent = 
                        result.message + (result.code ? ` (Code: ${result.code})` : '');
                }

            } catch (error) {
                errorDiv.classList.remove('hidden');
                document.getElementById('payment-error-msg').textContent = error.message;
                outputPre.textContent = 'Error: ' + error.message;
            }
        });
    </script>
</body>
</html>