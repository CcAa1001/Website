{{-- Cart Component with Floating Button & Sidebar + Payment Modal --}}
<div class="cart_component_wrapper">
    {{-- Floating Cart Button --}}
    @if ($itemCount > 0)
        <div class="cart_floating_wrapper">
            <button 
                type="button"
                wire:click="openCart"
                class="cart_floating_btn"
                aria-label="Open shopping cart"
            >
                <div class="cart_icon_wrapper">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart_badge">{{ $itemCount }}</span>
                </div>
                <span class="cart_total">
                    {{ $this->formatPrice($grandTotal) }}
                </span>
            </button>
        </div>
    @endif

    {{-- Cart Sidebar Overlay --}}
    <div 
        class="cart_overlay {{ $isOpen ? 'active' : '' }}"
        wire:click="closeCart"
    ></div>

    {{-- Cart Sidebar --}}
    <div class="cart_sidebar {{ $isOpen ? 'open' : '' }}">
        {{-- Sidebar Header --}}
        <div class="cart_header">
            <div class="cart_header_left">
                <i class="fas fa-shopping-cart"></i>
                <h3>Keranjang</h3>
                <span class="cart_count_badge">{{ $itemCount }}</span>
            </div>
            <button type="button" wire:click="closeCart" class="cart_close_btn">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Cart Content --}}
        <div class="cart_content">
            @if(count($items) > 0)
                {{-- Cart Items List --}}
                <div class="cart_items">
                    @foreach($items as $cartKey => $item)
                        <div class="cart_item" wire:key="cart-{{ $cartKey }}">
                            {{-- Item Image --}}
                            <div class="cart_item_image">
                                @if($item['image'])
                                    <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}">
                                @else
                                    <div class="cart_item_placeholder">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Item Details --}}
                            <div class="cart_item_details">
                                <h4 class="cart_item_name">{{ $item['name'] }}</h4>
                                
                                @if($item['variant_name'])
                                    <span class="cart_item_variant">{{ $item['variant_name'] }}</span>
                                @endif

                                @if(!empty($item['modifier_names']))
                                    <span class="cart_item_modifiers">
                                        {{ implode(', ', array_filter($item['modifier_names'])) }}
                                    </span>
                                @endif

                                @if($item['notes'])
                                    <span class="cart_item_notes">
                                        <i class="fas fa-sticky-note"></i> {{ $item['notes'] }}
                                    </span>
                                @endif

                                <div class="cart_item_price">
                                    {{ $this->formatPrice($item['price']) }}
                                </div>
                            </div>

                            {{-- Quantity Controls --}}
                            <div class="cart_item_actions">
                                <div class="quantity_control">
                                    <button 
                                        type="button" 
                                        wire:click="decrementQuantity('{{ $cartKey }}')"
                                        class="qty_btn qty_minus"
                                    >
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="qty_value">{{ $item['quantity'] }}</span>
                                    <button 
                                        type="button" 
                                        wire:click="incrementQuantity('{{ $cartKey }}')"
                                        class="qty_btn qty_plus"
                                    >
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>

                                <div class="cart_item_subtotal">
                                    {{ $this->formatPrice($item['price'] * $item['quantity']) }}
                                </div>

                                <button 
                                    type="button" 
                                    wire:click="removeItem('{{ $cartKey }}')"
                                    class="cart_item_remove"
                                    title="Remove item"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Clear Cart Button --}}
                <div class="cart_clear">
                    <button type="button" wire:click="clearCart" class="clear_cart_btn">
                        <i class="fas fa-trash"></i> Kosongkan Keranjang
                    </button>
                </div>
            @else
                {{-- Empty Cart --}}
                <div class="cart_empty">
                    <div class="cart_empty_icon">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <h4>Keranjang Kosong</h4>
                    <p>Belum ada produk di keranjang belanja Anda</p>
                    <button type="button" wire:click="closeCart" class="btn_continue_shopping">
                        Mulai Belanja
                    </button>
                </div>
            @endif
        </div>

        {{-- Cart Footer with Totals --}}
        @if(count($items) > 0)
            <div class="cart_footer">
                {{-- Order Notes --}}
                @if($hasTableSession)
                    <div class="order_notes_section">
                        <label for="orderNotes">
                            <i class="fas fa-comment-alt"></i> Catatan Pesanan
                        </label>
                        <textarea 
                            id="orderNotes"
                            class="order_notes_input"
                            placeholder="Contoh: Tidak pakai sambal, minta es batu terpisah..."
                            rows="2"
                        ></textarea>
                    </div>
                @endif

                <div class="cart_summary">
                    <div class="summary_row">
                        <span>Subtotal</span>
                        <span class="summary_value">{{ $this->formatPrice($subtotal) }}</span>
                    </div>
                    
                    @if($hasTableSession && $taxAmount > 0)
                        <div class="summary_row">
                            <span>Pajak ({{ $tableInfo['tax_rate'] ?? 11 }}%)</span>
                            <span class="summary_value">{{ $this->formatPrice($taxAmount) }}</span>
                        </div>
                    @endif
                    
                    @if($hasTableSession && $serviceCharge > 0)
                        <div class="summary_row">
                            <span>Service Charge ({{ $tableInfo['service_charge_rate'] ?? 0 }}%)</span>
                            <span class="summary_value">{{ $this->formatPrice($serviceCharge) }}</span>
                        </div>
                    @endif

                    <div class="summary_row summary_total">
                        <span>Total</span>
                        <span class="summary_value">{{ $this->formatPrice($grandTotal) }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if($hasTableSession)
                    @if($hasPendingOrder)
                        <div class="pending_order_alert">
                            <i class="fas fa-info-circle"></i>
                            <span>Anda memiliki pesanan yang belum dibayar</span>
                        </div>
                        
                        <button 
                            type="button"
                            wire:click="payPendingOrder"
                            wire:loading.attr="disabled"
                            class="btn_pay_now_pending"
                        >
                            <i class="fas fa-credit-card"></i>
                            Bayar Pesanan Saya
                        </button>
                        
                        <button 
                            type="button"
                            onclick="submitOrderWithNotes()"
                            wire:loading.attr="disabled"
                            class="btn_order_more"
                        >
                            <i class="fas fa-plus"></i>
                            Pesan Lagi
                        </button>
                    @else
                        <button 
                            type="button"
                            wire:click="proceedToPayment"
                            wire:loading.attr="disabled"
                            class="btn_pay_now"
                        >
                            <i class="fas fa-credit-card"></i>
                            Bayar Sekarang
                        </button>
                        
                        <button 
                            type="button"
                            onclick="submitOrderWithNotes()"
                            wire:loading.attr="disabled"
                            wire:loading.class="btn_loading"
                            class="btn_submit_order"
                            id="btnSubmitOrder"
                        >
                            <span wire:loading.remove wire:target="submitOrder">
                                <i class="fas fa-paper-plane"></i>
                                Pesan Dulu, Bayar Nanti
                            </span>
                            <span wire:loading wire:target="submitOrder">
                                <i class="fas fa-spinner fa-spin"></i>
                                Mengirim...
                            </span>
                        </button>
                    @endif
                @else
                    <a href="{{ route('checkout.index') ?? '#' }}" class="btn_checkout">
                        <span>Checkout</span>
                        <span class="checkout_total">{{ $this->formatPrice($grandTotal) }}</span>
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- Payment Modal --}}
    @if($showPaymentModal)
    <div class="payment_modal_wrapper" wire:click="closePaymentModal">
        <div class="payment_modal_content" onclick="event.stopPropagation()">
            <div class="payment_modal_header">
                <h3><i class="fas fa-credit-card"></i> Pilih Metode Pembayaran</h3>
                <button type="button" wire:click="closePaymentModal" class="payment_close_btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="payment_modal_body">
                <div class="payment_form_group">
                    <label><i class="fas fa-user"></i> Nama (Opsional)</label>
                    <input type="text" wire:model="customerName" class="payment_input" placeholder="Masukkan nama Anda">
                </div>
                
                <div class="payment_form_group">
                    <label><i class="fas fa-phone"></i> No. HP (Opsional)</label>
                    <input type="tel" wire:model="customerPhone" class="payment_input" placeholder="08xx xxxx xxxx">
                </div>

                <div class="payment_methods_section">
                    <label class="payment_section_label">
                        <i class="fas fa-wallet"></i> Metode Pembayaran <span class="required">*</span>
                    </label>
                    
                    @if(empty($availablePaymentMethods))
                        <div class="payment_alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            Tidak ada metode pembayaran tersedia. Hubungi staff untuk bantuan.
                        </div>
                    @else
                        <div class="payment_methods_grid">
                            @foreach($availablePaymentMethods as $method)
                                <label class="payment_method_card {{ $selectedPaymentMethod === $method['id'] ? 'selected' : '' }}">
                                    <input 
                                        type="radio" 
                                        wire:model.live="selectedPaymentMethod" 
                                        value="{{ $method['id'] }}"
                                        class="payment_radio"
                                    >
                                    <div class="payment_method_content">
                                        <span class="payment_method_icon">{{ $method['icon'] }}</span>
                                        <div class="payment_method_info">
                                            <strong>{{ $method['name'] }}</strong>
                                            <small>{{ ucfirst(str_replace('_', ' ', $method['type'])) }}</small>
                                        </div>
                                        <span class="payment_check">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="payment_order_summary">
                    <h4><i class="fas fa-receipt"></i> Ringkasan Pesanan</h4>
                    <div class="payment_summary_items">
                        @foreach($items as $item)
                            <div class="payment_summary_item">
                                <span>{{ $item['quantity'] }}x {{ $item['name'] }}</span>
                                <span>{{ $this->formatPrice($item['price'] * $item['quantity']) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="payment_summary_total">
                        <strong>Total Pembayaran:</strong>
                        <strong class="total_amount">{{ $this->formatPrice($grandTotal) }}</strong>
                    </div>
                </div>
            </div>

            <div class="payment_modal_footer">
                <button type="button" wire:click="closePaymentModal" class="payment_btn_cancel">
                    Batal
                </button>
                <button 
                    type="button" 
                    onclick="confirmPayment()"
                    wire:loading.attr="disabled"
                    class="payment_btn_confirm {{ !$selectedPaymentMethod ? 'disabled' : '' }}"
                    {{ !$selectedPaymentMethod ? 'disabled' : '' }}
                >
                    <span wire:loading.remove wire:target="submitOrderWithPayment">
                        <i class="fas fa-check-circle"></i> Konfirmasi Pembayaran
                    </span>
                    <span wire:loading wire:target="submitOrderWithPayment">
                        <i class="fas fa-spinner fa-spin"></i> Memproses...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Order Success Modal --}}
    <div id="orderSuccessModal" class="order_success_modal" style="display: none;">
        <div class="order_success_overlay" onclick="closeSuccessModal()"></div>
        <div class="order_success_content">
            <div class="success_icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 id="successTitle">Pesanan Dikirim!</h3>
            <p id="successMessage">Pesanan Anda sedang diproses di dapur</p>
            <div class="order_number">
                <span>No. Pesanan:</span>
                <strong id="orderNumberDisplay"></strong>
            </div>
            <button type="button" onclick="closeSuccessModal()" class="btn_close_modal">
                Lanjut Pesan
            </button>
        </div>
    </div>

    {{-- Order Error Toast --}}
    <div id="orderErrorToast" class="order_error_toast" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <span id="orderErrorMessage"></span>
    </div>

    {{-- Scripts - FIXED WITH 500MS DELAY --}}
    <script>
        console.log('Cart script loaded');

        function submitOrderWithNotes() {
            const notes = document.getElementById('orderNotes')?.value || null;
            @this.submitOrder(notes);
        }

        function confirmPayment() {
            const notes = document.getElementById('orderNotes')?.value || null;
            console.log('Confirming payment...');
            @this.submitOrderWithPayment(notes);
        }

        function payPendingOrderFromModal() {
            closeSuccessModal();
            @this.call('payPendingOrder');
        }

        // CRITICAL FIX: Add 500ms delay to ensure DB commit
        function handlePaymentRedirect(url) {
            console.log('=== REDIRECT CALLED ===');
            console.log('URL:', url);
            
            if (!url) {
                console.error('No URL provided!');
                return;
            }
            
            const toast = document.getElementById('orderErrorToast');
            const messageEl = document.getElementById('orderErrorMessage');
            if (toast && messageEl) {
                messageEl.textContent = 'Mengarahkan ke halaman pembayaran...';
                toast.style.display = 'flex';
                toast.style.background = '#4CAF50';
            }
            
            // IMPORTANT: 500ms delay ensures database commit completes
            setTimeout(() => {
                console.log('Redirecting now to:', url);
                window.location.href = url;
            }, 500);
        }

        function showSuccessModal(data) {
            const modal = document.getElementById('orderSuccessModal');
            const title = document.getElementById('successTitle');
            const message = document.getElementById('successMessage');
            const display = document.getElementById('orderNumberDisplay');

            if (modal && display) {
                if (data.payment_method) {
                    title.textContent = 'Pembayaran Berhasil!';
                    message.textContent = 'Pesanan Anda sudah dibayar dengan ' + data.payment_method;
                } else if (data.show_pay_now_button) {
                    title.textContent = 'Pesanan Dikirim!';
                    message.textContent = 'Pesanan telah diterima. Silakan lakukan pembayaran.';

                    const modalContent = modal.querySelector('.order_success_content');
                    const existingPayBtn = modalContent.querySelector('.btn_pay_now_modal');

                    if (!existingPayBtn) {
                        const payBtn = document.createElement('button');
                        payBtn.className = 'btn_pay_now_modal';
                        payBtn.innerHTML = '<i class="fas fa-credit-card"></i> Bayar Sekarang';
                        payBtn.onclick = payPendingOrderFromModal;
                        modalContent.insertBefore(payBtn, modalContent.querySelector('.btn_close_modal'));
                    }
                } else {
                    title.textContent = 'Pesanan Dikirim!';
                    message.textContent = 'Pesanan Anda sedang diproses di dapur';
                }

                display.textContent = data.order_number;
                modal.style.display = 'block';
            }
        }

        function closeSuccessModal() {
            const modal = document.getElementById('orderSuccessModal');
            if (modal) modal.style.display = 'none';
        }

        function showErrorToast(message) {
            const toast = document.getElementById('orderErrorToast');
            const messageEl = document.getElementById('orderErrorMessage');
            if (toast && messageEl) {
                messageEl.textContent = message;
                toast.style.display = 'flex';
                toast.style.background = '#f44336';
                setTimeout(() => toast.style.display = 'none', 4000);
            }
        }

        // Livewire v3 events
        document.addEventListener('livewire:init', () => {
            console.log('Livewire initialized');
            
            Livewire.on('order-success', (event) => {
                console.log('Order success:', event);
                showSuccessModal(event);
            });

            Livewire.on('order-error', (event) => {
                console.log('Order error:', event);
                showErrorToast(event.message);
            });

            Livewire.on('payment-redirect', (event) => {
                console.log('=== PAYMENT REDIRECT EVENT ===', event);
                let url;
                if (Array.isArray(event)) {
                    url = event[0];  // Livewire v3 passes as array
                } else if (typeof event === 'string') {
                    url = event;  // Direct string
                } else {
                    url = event.url;  // Object with url property
                }
                
                handlePaymentRedirect(url);
            });
        });

        // Livewire v2 fallback
        document.addEventListener('livewire:load', () => {
            console.log('Livewire loaded (v2)');
            
            Livewire.on('order-success', (data) => {
                showSuccessModal(data[0] || data);
            });

            Livewire.on('order-error', (data) => {
                showErrorToast((data[0] && data[0].message) || data.message);
            });

            Livewire.on('payment-redirect', (data) => {
                const url = (data[0] && data[0].url) || data.url;
                if (url) handlePaymentRedirect(url);
            });
        });

        console.log('Cart script complete');
    </script>

    <style>
        .btn_pay_now_modal {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .btn_pay_now_modal:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</div>