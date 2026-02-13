<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $status === 'completed' ? 'Pembayaran Berhasil' : 'Status Pembayaran' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .status-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        .status-icon.success {
            background: #d4edda;
            color: #28a745;
        }
        .status-icon.pending {
            background: #fff3cd;
            color: #ffc107;
        }
        .status-icon.failed {
            background: #f8d7da;
            color: #dc3545;
        }
        h1 { font-size: 24px; margin-bottom: 10px; color: #333; }
        .status-message { color: #666; margin-bottom: 30px; }
        
        .receipt {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .receipt-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px dashed #e0e0e0;
            margin-bottom: 20px;
        }
        .receipt-header h2 { font-size: 18px; margin-bottom: 5px; }
        .receipt-header p { color: #666; font-size: 14px; }
        
        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-label { color: #666; font-size: 14px; }
        .receipt-value { font-weight: 600; color: #333; text-align: right; }
        
        .receipt-items {
            margin: 20px 0;
            padding: 20px 0;
            border-top: 2px dashed #e0e0e0;
            border-bottom: 2px dashed #e0e0e0;
        }
        .receipt-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        .item-name { color: #333; flex: 1; }
        .item-qty { color: #666; margin: 0 15px; }
        .item-price { font-weight: 600; color: #333; }
        
        .receipt-total {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .receipt-total .receipt-row {
            border: none;
            padding: 8px 0;
        }
        .receipt-total .total-row {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #f5f5f5;
            color: #666;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .btn-retry {
            background: #ffc107;
            color: #333;
        }
        .btn-retry:hover {
            background: #e0a800;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Status Card --}}
        <div class="status-card">
            @if($status === 'completed')
                <div class="status-icon success">✓</div>
                <h1>Pembayaran Berhasil!</h1>
                <p class="status-message">Terima kasih, pembayaran Anda telah diterima</p>
            @elseif($status === 'pending')
                <div class="status-icon pending">⏱</div>
                <h1>Menunggu Pembayaran</h1>
                <p class="status-message">Pembayaran Anda sedang diproses</p>
            @elseif($status === 'failed')
                <div class="status-icon failed">✕</div>
                <h1>Pembayaran Gagal</h1>
                <p class="status-message">Pembayaran tidak dapat diproses</p>
            @else
                <div class="status-icon pending">?</div>
                <h1>Status Tidak Diketahui</h1>
                <p class="status-message">Silakan hubungi staff untuk bantuan</p>
            @endif
        </div>

        {{-- Receipt --}}
        @if(isset($order) && isset($payment))
        <div class="receipt">
            <div class="receipt-header">
                <h2>{{ $order->outlet->name ?? 'Restaurant' }}</h2>
                <p>{{ $order->table ? 'Meja ' . $order->table->table_number : '' }}</p>
                <p style="margin-top: 10px; font-size: 12px;">
                    {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}
                </p>
            </div>

            <div class="receipt-row">
                <span class="receipt-label">No. Pesanan:</span>
                <span class="receipt-value">{{ $order->order_number }}</span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">No. Pembayaran:</span>
                <span class="receipt-value">{{ $payment->payment_number }}</span>
            </div>
            @if($payment->gateway_transaction_id)
            <div class="receipt-row">
                <span class="receipt-label">ID Transaksi:</span>
                <span class="receipt-value" style="font-size: 11px;">{{ $payment->gateway_transaction_id }}</span>
            </div>
            @endif
            <div class="receipt-row">
                <span class="receipt-label">Metode Pembayaran:</span>
                <span class="receipt-value">{{ $payment->paymentMethod->name ?? 'QRIS' }}</span>
            </div>

            {{-- Order Items --}}
            <div class="receipt-items">
                @foreach($order->orderItems as $item)
                <div class="receipt-item">
                    <span class="item-name">{{ $item->product_name }}</span>
                    <span class="item-qty">x{{ $item->quantity }}</span>
                    <span class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div class="receipt-total">
                <div class="receipt-row">
                    <span class="receipt-label">Subtotal:</span>
                    <span class="receipt-value">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($order->tax_amount > 0)
                <div class="receipt-row">
                    <span class="receipt-label">Pajak:</span>
                    <span class="receipt-value">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($order->service_charge > 0)
                <div class="receipt-row">
                    <span class="receipt-label">Service:</span>
                    <span class="receipt-value">Rp {{ number_format($order->service_charge, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="receipt-row total-row">
                    <span class="receipt-label">TOTAL:</span>
                    <span class="receipt-value">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="action-buttons">
            @if($status === 'completed')
                <a href="{{ route('table.menu', ['token' => request()->cookie('table_session_token')]) }}" class="btn btn-primary">
                    Kembali ke Menu
                </a>
            @elseif($status === 'failed' && isset($order) && $order->allow_retry_payment)
                <a href="{{ route('order.retry-payment', $order->id) }}" class="btn btn-retry">
                    Coba Lagi
                </a>
                <a href="{{ route('table.menu', ['token' => request()->cookie('table_session_token')]) }}" class="btn btn-secondary">
                    Kembali
                </a>
            @else
                <a href="{{ route('table.menu', ['token' => request()->cookie('table_session_token')]) }}" class="btn btn-primary">
                    Kembali ke Menu
                </a>
            @endif
        </div>
    </div>
</body>
</html>