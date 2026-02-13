<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menuju Pembayaran...</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .redirect-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 { color: #333; margin-bottom: 15px; font-size: 24px; }
        p { color: #666; margin-bottom: 30px; line-height: 1.6; }
        .order-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .order-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .order-row:last-child { border-bottom: none; }
        .order-label { color: #666; font-size: 14px; }
        .order-value { color: #333; font-weight: 600; }
        .countdown { 
            font-size: 48px; 
            font-weight: bold; 
            color: #667eea; 
            margin: 20px 0;
        }
        .btn-cancel {
            background: #f5f5f5;
            color: #666;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-cancel:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="redirect-container">
        <div class="spinner"></div>
        <h2>Mengarahkan ke Halaman Pembayaran</h2>
        <p>Anda akan diarahkan ke halaman pembayaran Nusandana dalam</p>
        <div class="countdown" id="countdown">3</div>

        <div class="order-info">
            <div class="order-row">
                <span class="order-label">No. Pesanan:</span>
                <span class="order-value">{{ $order->order_number }}</span>
            </div>
            <div class="order-row">
                <span class="order-label">Total Pembayaran:</span>
                <span class="order-value">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
            </div>
            <div class="order-row">
                <span class="order-label">Metode:</span>
                <span class="order-value">{{ $paymentMethod->name }}</span>
            </div>
        </div>

        <p style="font-size: 13px; color: #999; margin-bottom: 15px;">
            Jika tidak diarahkan otomatis, klik tombol di bawah
        </p>

        <a href="{{ $paymentUrl }}" class="btn-cancel" style="display: inline-block; text-decoration: none; background: #667eea; color: white;">
            Lanjut ke Pembayaran
        </a>
    </div>

    <script>
        let countdown = 3;
        const countdownEl = document.getElementById('countdown');
        const paymentUrl = "{{ $paymentUrl }}";

        const timer = setInterval(() => {
            countdown--;
            countdownEl.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = paymentUrl;
            }
        }, 1000);
    </script>
</body>
</html>