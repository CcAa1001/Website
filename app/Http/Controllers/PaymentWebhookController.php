<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentGateway\NusandanaGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Handle Nusandana payment callback
     */
    public function nusandanaCallback(Request $request)
    {
        Log::info('Nusandana Payment Callback', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {
            $gateway = new NusandanaGateway();

            // Verify signature
            if (!$gateway->verifyCallback($request->all())) {
                Log::warning('Nusandana Invalid Signature', [
                    'payload' => $request->all(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature'
                ], 403);
            }

            // Get callback data
            $merchantOrderNo = $request->input('morderno'); // Your order number
            $platformOrderNo = $request->input('orderno'); // Nusandana's order number
            $status = $request->input('status'); // 1=success, 0=failed
            $amount = $request->input('amount');

            // Find payment by merchant order number
            $payment = Payment::where('payment_number', $merchantOrderNo)->first();

            if (!$payment) {
                Log::error('Nusandana Payment Not Found', [
                    'merchant_order_no' => $merchantOrderNo,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            DB::beginTransaction();

            if ($status == 1) {
                // Payment success
                $payment->update([
                    'status' => 'completed',
                    'gateway_transaction_id' => $platformOrderNo,
                    'gateway_response' => json_encode($request->all()),
                    'paid_at' => now(),
                ]);

                $order = $payment->order;
                
                // Update order status
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                // Auto-confirm to kitchen if enabled
                $outlet = $order->outlet;
                if ($outlet && $outlet->auto_confirm_kitchen_after_payment) {
                    $order->orderItems()->update([
                        'kitchen_status' => 'pending', // Ready for kitchen
                    ]);
                }

                Log::info('Nusandana Payment Success', [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);

            } else {
                // Payment failed
                $payment->update([
                    'status' => 'failed',
                    'gateway_transaction_id' => $platformOrderNo,
                    'gateway_response' => json_encode($request->all()),
                ]);

                Log::warning('Nusandana Payment Failed', [
                    'payment_id' => $payment->id,
                    'order_number' => $merchantOrderNo,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Callback processed'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Nusandana Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal error'
            ], 500);
        }
    }

    /**
     * User returns after payment (success/failed page)
     */
    public function nusandanaReturn(Request $request)
    {
        $orderNumber = $request->input('order_number');
        $paymentNumber = $request->input('payment_number');

        $payment = Payment::where('payment_number', $paymentNumber)
                         ->with('order')
                         ->first();

        if (!$payment) {
            return view('payment.return', [
                'status' => 'not_found',
                'message' => 'Pembayaran tidak ditemukan',
            ]);
        }

        return view('payment.return', [
            'status' => $payment->status,
            'payment' => $payment,
            'order' => $payment->order,
        ]);
    }
}