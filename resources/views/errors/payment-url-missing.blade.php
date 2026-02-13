<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Pembayaran Tidak Tersedia</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        p {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-details {
            background: #fff5f5;
            border: 2px solid #feb2b2;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }

        .error-details h3 {
            color: #c53030;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .error-details p {
            color: #744210;
            font-size: 14px;
            margin: 0;
        }

        .order-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin: 20px 0;
        }

        .order-info strong {
            display: block;
            color: #333;
            margin-bottom: 5px;
        }

        .order-info code {
            color: #667eea;
            font-family: monospace;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            display: inline-block;
            padding: 14px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #333;
        }

        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Link Pembayaran Tidak Tersedia</h1>
        <p>Pembayaran ditemukan, tetapi link pembayaran tidak tersedia.</p>

        <div class="error-details">
            <h3>Kemungkinan Penyebab:</h3>
            <p>• Gateway pembayaran sedang mengalami gangguan<br>
               • Koneksi ke Nusandana terputus<br>
               • Konfigurasi pembayaran tidak lengkap</p>
        </div>

        @if(isset($payment) && isset($order))
        <div class="order-info">
            <strong>No. Pesanan:</strong>
            <code>{{ $order->order_number }}</code>
            <br><br>
            <strong>Total:</strong>
            <code>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</code>
            <br><br>
            <strong>Status:</strong>
            <code>{{ ucfirst($payment->status) }}</code>
        </div>
        @endif

        <p style="font-size: 14px; color: #999;">
            Silakan hubungi staff atau coba lagi dalam beberapa saat.
        </p>

        <div class="btn-group">
            <a href="{{ url('/') }}" class="btn btn-secondary">Kembali</a>
            @if(isset($order))
            <a href="{{ route('order.retry-payment', $order) }}" class="btn btn-primary">Coba Lagi</a>
            @endif
        </div>
    </div>
</body>
</html>
