<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class RefundManager extends Component
{
    public $orderId;
    public $order;
    public $refundType = 'full';
    public $selectedItems = [];
    public $refundReason = 'quality_issue';
    public $refundNotes;
    public $refundId;
    public $supervisorPin;
    public $rejectionReason;
    public $refundMethod = 'cash';
    public $showRequestModal = false;
    public $showApprovalModal = false;

    #[On('openRefundModal')]
    public function openRefundRequest($orderId)
    {
        $this->orderId = $orderId;
        $this->order = Order::with(['items', 'payments.paymentMethod'])->find($orderId);
        
        if (!$this->order || !in_array($this->order->payment_status, ['paid', 'partial'])) {
            session()->flash('error', 'Order cannot be refunded');
            return;
        }

        $this->selectedItems = [];
        foreach ($this->order->items as $item) {
            $this->selectedItems[$item->id] = [
                'selected' => true, 
                'quantity' => $item->quantity
            ];
        }

        $this->showRequestModal = true;
    }

    public function submitRefundRequest()
    {
        $user = auth()->user();
        $amounts = $this->calculateRefundAmounts();

        try {
            DB::beginTransaction();

            $refund = Refund::create([
                'tenant_id' => $user->tenant_id,
                'outlet_id' => $user->outlet_id,
                'order_id' => $this->order->id,
                'payment_id' => $this->order->payments()->first()?->id,
                'refund_type' => $this->refundType,
                'subtotal_refund' => $amounts['subtotal'],
                'tax_refund' => $amounts['tax'],
                'service_charge_refund' => $amounts['service_charge'],
                'total_refund_amount' => $amounts['total'],
                'reason' => $this->refundReason,
                'notes' => $this->refundNotes,
                'requested_by' => $user->id,
                'requested_at' => now(),
                'status' => 'pending',
            ]);

            if ($this->refundType === 'partial') {
                foreach ($this->selectedItems as $itemId => $data) {
                    if ($data['selected'] ?? false) {
                        $orderItem = $this->order->items->find($itemId);
                        if ($orderItem) {
                            RefundItem::create([
                                'refund_id' => $refund->id,
                                'order_item_id' => $orderItem->id,
                                'quantity' => $data['quantity'],
                                'unit_price' => $orderItem->unit_price,
                                'subtotal' => $orderItem->unit_price * $data['quantity'],
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            
            $this->showRequestModal = false;
            $this->reset(['orderId', 'order', 'refundType', 'selectedItems', 'refundReason', 'refundNotes']);
            
            session()->flash('message', 'Refund request submitted successfully');
            
            $this->openApprovalModal($refund->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed: ' . $e->getMessage());
        }
    }

    protected function calculateRefundAmounts()
    {
        if ($this->refundType === 'full') {
            return [
                'subtotal' => $this->order->subtotal,
                'tax' => $this->order->tax_amount,
                'service_charge' => $this->order->service_charge,
                'total' => $this->order->grand_total,
            ];
        }

        $subtotal = 0;
        foreach ($this->selectedItems as $itemId => $data) {
            if ($data['selected'] ?? false) {
                $orderItem = $this->order->items->find($itemId);
                if ($orderItem) {
                    $subtotal += $orderItem->unit_price * ($data['quantity'] ?? 0);
                }
            }
        }

        $taxRate = $this->order->subtotal > 0 ? ($this->order->tax_amount / $this->order->subtotal) : 0;
        $serviceRate = $this->order->subtotal > 0 ? ($this->order->service_charge / $this->order->subtotal) : 0;

        return [
            'subtotal' => $subtotal,
            'tax' => $subtotal * $taxRate,
            'service_charge' => $subtotal * $serviceRate,
            'total' => $subtotal * (1 + $taxRate + $serviceRate),
        ];
    }

    public function openApprovalModal($refundId)
    {
        $this->refundId = $refundId;
        $this->supervisorPin = '';
        $this->showApprovalModal = true;
    }

    public function approveRefund()
    {
        $user = auth()->user();
        $refund = Refund::with('order.payments.paymentMethod')->find($this->refundId);

        if (!$refund) {
            session()->flash('error', 'Refund not found');
            return;
        }

        if (!$this->verifySupervisorPin($user, $this->supervisorPin)) {
            session()->flash('error', 'Invalid PIN or insufficient permissions');
            return;
        }

        try {
            DB::beginTransaction();
            
            // Update refund status
            $refund->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'refund_method' => $this->refundMethod,
            ]);

            // Get payment_method_id
            $paymentMethodId = $this->getPaymentMethodId($refund, $user);

            if (!$paymentMethodId) {
                throw new \Exception('No payment method available. Please contact administrator.');
            }

            // Create negative payment record
            Payment::create([
                'tenant_id' => $refund->tenant_id,
                'outlet_id' => $refund->outlet_id,
                'order_id' => $refund->order_id,
                'payment_method_id' => $paymentMethodId,
                'user_id' => $user->id,
                'payment_number' => 'REF-' . now()->format('YmdHis'),
                'transaction_type' => 'refund',
                'amount' => -$refund->total_refund_amount,
                'net_amount' => -$refund->total_refund_amount,
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            // Update order payment status
            $refund->order->update([
                'payment_status' => $refund->refund_type === 'full' ? 'refunded' : 'partial'
            ]);

            // Mark refund as completed
            $refund->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            DB::commit();
            
            $this->showApprovalModal = false;
            $this->reset(['refundId', 'supervisorPin', 'refundMethod']);
            
            session()->flash('message', 'Refund approved successfully');
            
            $this->dispatch('refund-completed');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to approve refund: ' . $e->getMessage());
        }
    }

    /**
     * Get payment method ID for refund
     * Priority: Original payment method > Cash method > Any active method
     */
    protected function getPaymentMethodId($refund, $user)
    {
        // Try to get original payment method
        $originalPayment = $refund->order->payments()
            ->where('transaction_type', 'payment')
            ->whereNotNull('payment_method_id')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($originalPayment && $originalPayment->payment_method_id) {
            return $originalPayment->payment_method_id;
        }

        // Fallback to cash payment method
        $cashMethod = PaymentMethod::where('tenant_id', $user->tenant_id)
            ->where('payment_type', 'cash')
            ->where('is_active', true)
            ->first();

        if ($cashMethod) {
            return $cashMethod->id;
        }

        // Last resort: any active payment method
        $anyMethod = PaymentMethod::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->first();

        return $anyMethod?->id;
    }

    protected function verifySupervisorPin(User $user, ?string $pin): bool
    {
        if (empty($pin)) {
            return false;
        }

        if (!$user->role) {
            return false;
        }

        $roleName = strtolower($user->role->name);
        $allowedRoles = ['supervisor', 'manager', 'admin'];

        if (!in_array($roleName, $allowedRoles)) {
            return false;
        }

        return $user->pin_code === $pin;
    }

    public function closeRequestModal()
    {
        $this->showRequestModal = false;
        $this->reset(['orderId', 'order', 'refundType', 'selectedItems', 'refundReason', 'refundNotes']);
    }

    public function closeApprovalModal()
    {
        $this->showApprovalModal = false;
        $this->reset(['refundId', 'supervisorPin', 'refundMethod']);
    }

    public function render()
    {
        return view('livewire.refund-manager');
    }
}