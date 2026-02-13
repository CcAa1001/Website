<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Kadaluarsa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
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

        .expired-info {
            background: #fffaf0;
            border: 2px solid #fed7aa;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }

        .expired-info h3 {
            color: #c05621;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .expired-info p {
            color: #744210;
            font-size: 14px;
            margin: 5px 0;
        }

        .order-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
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

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⏰</div>
        <h1>Pembayaran Kadaluarsa</h1>
        <p>Link pembayaran ini sudah tidak berlaku.</p>

        <div class="expired-info">
            <h3>⚠️ Waktu Habis</h3>
            <p>Link pembayaran hanya berlaku selama 15 menit.</p>
            @if(isset($payment))
            <p style="margin-top: 10px;">
                <strong>Kadaluarsa pada:</strong><br>
                {{ $payment->payment_expired_at->format('d M Y, H:i') }}
            </p>
            @endif
        </div>

        @if(isset($order))
        <div class="order-info">
            <strong>No. Pesanan:</strong>
            <code>{{ $order->order_number }}</code>
            <br><br>
            <strong>Total:</strong>
            <code>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</code>
        </div>

        <p style="font-size: 14px; color: #999; margin-bottom: 20px;">
            Anda dapat membuat link pembayaran baru dengan mengklik tombol di bawah.
        </p>

        <a href="{{ route('order.retry-payment', $order) }}" class="btn">
            Buat Link Pembayaran Baru
        </a>
        @else
        <a href="{{ url('/') }}" class="btn">Kembali ke Beranda</a>
        @endif
    </div>
</body>
</html>
