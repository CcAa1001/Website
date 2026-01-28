<div>
    {{-- Refund Request Modal --}}
    @if($showRequestModal && $order)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🔄 Request Refund - {{ $order->order_number }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showRequestModal', false)"></button>
                </div>
                <div class="modal-body">
                    {{-- Order Summary --}}
                    <div class="alert alert-info">
                        <strong>Order Total:</strong> Rp {{ number_format($order->grand_total, 0, ',', '.') }}<br>
                        <strong>Customer:</strong> {{ $order->customer_name ?? '-' }}
                    </div>

                    {{-- Refund Type --}}
                    <div class="mb-3">
                        <label class="form-label">Refund Type</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" wire:model.live="refundType" value="full" id="typeFull">
                                <label class="form-check-label" for="typeFull">Full Refund</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" wire:model.live="refundType" value="partial" id="typePartial">
                                <label class="form-check-label" for="typePartial">Partial Refund</label>
                            </div>
                        </div>
                    </div>

                    {{-- Items Selection (for partial refund) --}}
                    @if($refundType === 'partial')
                        <div class="mb-3">
                            <label class="form-label">Select Items to Refund</label>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-center">Refund</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td>{{ $item->product_name }}</td>
                                                <td class="text-center">{{ $item->quantity }}</td>
                                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    <input type="checkbox" 
                                                           wire:model.live="selectedItems.{{ $item->id }}.selected"
                                                           class="form-check-input">
                                                    @if($selectedItems[$item->id]['selected'] ?? false)
                                                        <input type="number" 
                                                               wire:model.live="selectedItems.{{ $item->id }}.quantity" 
                                                               min="1" 
                                                               max="{{ $item->quantity }}"
                                                               class="form-control form-control-sm d-inline-block" 
                                                               style="width: 60px;">
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Reason --}}
                    <div class="mb-3">
                        <label class="form-label">Reason *</label>
                        <select class="form-control" wire:model="refundReason">
                            <option value="wrong_order">Wrong Order</option>
                            <option value="quality_issue">Food Quality Issue</option>
                            <option value="customer_complaint">Customer Complaint</option>
                            <option value="staff_error">Staff Error</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" wire:model="refundNotes" rows="2" 
                                  placeholder="Additional details..."></textarea>
                    </div>

                    {{-- Refund Amount Calculation --}}
                    <div class="alert alert-warning">
                        <strong>Refund Amount:</strong> 
                        Rp {{ number_format($this->calculateRefundAmounts()['total'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showRequestModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="submitRefundRequest" wire:loading.attr="disabled">
                        <span wire:loading.remove>Submit Refund Request</span>
                        <span wire:loading>Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Supervisor Approval Modal --}}
    @if($showApprovalModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-gradient-success">
                    <h5 class="modal-title text-white">🔐 Supervisor Approval</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showApprovalModal', false)"></button>
                </div>
                <div class="modal-body">
                    @if(session()->has('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <p class="text-center mb-3">Enter your 4-digit supervisor PIN to approve this refund</p>

                    {{-- PIN Input --}}
                    <div class="mb-3">
                        <input type="password" 
                               class="form-control form-control-lg text-center" 
                               wire:model="supervisorPin" 
                               placeholder="****"
                               maxlength="4"
                               autofocus>
                    </div>

                    {{-- Refund Method --}}
                    <div class="mb-3">
                        <label class="form-label">Refund Method</label>
                        <select class="form-control" wire:model="refundMethod">
                            <option value="cash">Cash</option>
                            <option value="card_reversal">Card Reversal</option>
                            <option value="credit_note">Credit Note</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showApprovalModal', false)">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="approveRefund" wire:loading.attr="disabled">
                        <i class="material-icons text-sm">check_circle</i>
                        <span wire:loading.remove>Approve Refund</span>
                        <span wire:loading>Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('openRefundRequest', (data) => {
            @this.openRefundRequest(data.orderId);
        });
    });
</script>
@endpush