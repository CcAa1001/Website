<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentRedirectController extends Controller
{
    /**
     * Show payment redirect page with countdown
     */
    public function redirect(Request $request)
    {
        $orderId = $request->input('order_id');
        $paymentId = $request->input('payment_id');

        // Add small delay to ensure database commit (if needed)
        if (!$orderId || !$paymentId) {
            abort(400, 'Missing order_id or payment_id');
        }

        // Try to find payment, with retry logic
        $payment = null;
        $maxAttempts = 3;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $payment = Payment::with(['order', 'paymentMethod'])->find($paymentId);
            
            if ($payment) {
                break; // Found it!
            }
            
            // Wait a bit before retry (only if not last attempt)
            if ($attempt < $maxAttempts) {
                usleep(100000); // 100ms delay
            }
        }

        if (!$payment) {
            // Payment not found - show helpful error
            return view('errors.payment-not-found', [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
            ]);
        }

        if (!$payment->payment_url) {
            // Payment exists but no URL - something went wrong
            return view('errors.payment-url-missing', [
                'payment' => $payment,
                'order' => $payment->order,
            ]);
        }

        // Check if payment already expired
        if ($payment->payment_expired_at && now()->gt($payment->payment_expired_at)) {
            return view('errors.payment-expired', [
                'payment' => $payment,
                'order' => $payment->order,
            ]);
        }

        return view('payment.redirect', [
            'payment' => $payment,
            'order' => $payment->order,
            'paymentMethod' => $payment->paymentMethod,
            'paymentUrl' => $payment->payment_url,
        ]);
    }

    /**
     * Retry failed payment
     */
    public function retryPayment(Order $order)
    {
        if ($order->payment_status === 'paid') {
            return redirect()->route('table.menu', ['token' => request()->cookie('table_session_token')])
                           ->with('error', 'Pesanan sudah dibayar');
        }

        if (!$order->allow_retry_payment) {
            return redirect()->route('table.menu', ['token' => request()->cookie('table_session_token')])
                           ->with('error', 'Pembayaran tidak dapat diulang');
        }

        // Find the latest payment
        $payment = $order->payments()
                        ->whereIn('status', ['failed', 'pending'])
                        ->latest()
                        ->first();

        if (!$payment || !$payment->payment_url) {
            return redirect()->route('table.menu', ['token' => request()->cookie('table_session_token')])
                           ->with('error', 'Link pembayaran tidak ditemukan');
        }

        // Redirect directly to payment URL
        return redirect()->away($payment->payment_url);
    }
}