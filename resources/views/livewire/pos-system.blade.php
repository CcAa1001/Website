<div>
<div class="pos-modern-container">
    {{-- Header Bar --}}
    <div class="pos-header">
        <div class="pos-header-left">
            <h4 class="pos-title">
                <i class="material-icons">point_of_sale</i>
                Point of Sale
            </h4>
            <div class="order-type-selector">
                <button 
                    wire:click="setOrderType('dine_in')"
                    class="order-type-btn {{ $orderType === 'dine_in' ? 'active' : '' }}"
                >
                    <i class="material-icons">restaurant</i>
                    Dine In
                </button>
                <button 
                    wire:click="setOrderType('takeaway')"
                    class="order-type-btn {{ $orderType === 'takeaway' ? 'active' : '' }}"
                >
                    <i class="material-icons">shopping_bag</i>
                    Takeaway
                </button>
            </div>
        </div>
        <div class="pos-header-right">
            @if(count($parkedOrders) > 0)
                <button 
                    wire:click="$toggle('showParkedOrders')"
                    class="btn-parked-orders"
                >
                    <i class="material-icons">bookmark</i>
                    Parked ({{ count($parkedOrders) }})
                </button>
            @endif
        </div>
    </div>

    {{-- Main Content --}}
    <div class="pos-main-content">
        {{-- Left Side: Products --}}
        <div class="pos-products-section">
            {{-- Search & Filters --}}
            <div class="products-controls">
                <div class="search-box">
                    <i class="material-icons">search</i>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari produk..."
                        class="search-input"
                    >
                </div>
            </div>

            {{-- Category Tabs --}}
            <div class="category-tabs">
                <button 
                    wire:click="filterCategory(null)"
                    class="category-tab {{ $selectedCategory === null ? 'active' : '' }}"
                >
                    <i class="material-icons">apps</i>
                    Semua
                </button>
                @foreach($categories as $category)
                    <button 
                        wire:click="filterCategory('{{ $category->id }}')"
                        class="category-tab {{ $selectedCategory === $category->id ? 'active' : '' }}"
                    >
                        {{ $category->name }}
                        @if($category->products_count > 0)
                            <span class="badge">{{ $category->products_count }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Products Grid --}}
            <div class="products-grid" wire:loading.class="loading">
                @forelse($products as $product)
                    <div 
                        class="product-card"
                        wire:click="selectProduct('{{ $product->id }}')"
                    >
                        <div class="product-image">
                            <img 
                                src="{{ $product->medium_image }}" 
                                alt="{{ $product->name }}"
                                onerror="this.src='https://via.placeholder.com/200?text=No+Image'"
                            >
                            @if($product->modifierGroups->isNotEmpty())
                                <span class="badge-customizable">
                                    <i class="material-icons">tune</i>
                                </span>
                            @endif
                        </div>
                        <div class="product-info">
                            <h6 class="product-name">{{ $product->name }}</h6>
                            <p class="product-price">Rp {{ number_format($product->base_price, 0, ',', '.') }}</p>
                            @if($product->sku)
                                <span class="product-sku">{{ $product->sku }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-products">
                        <i class="material-icons">inventory_2</i>
                        <p>Tidak ada produk</p>
                    </div>
                @endforelse
            </div>

            {{-- Loading Overlay --}}
            <div wire:loading class="loading-overlay">
                <div class="spinner"></div>
            </div>
        </div>

        {{-- Right Side: Cart --}}
        <div class="pos-cart-section">
            <div class="cart-card">
                {{-- Cart Header --}}
                <div class="cart-header">
                    <h5>Order Saat Ini</h5>
                    @if(!empty($cart))
                        <button wire:click="clearCart" class="btn-clear-cart">
                            <i class="material-icons">delete_sweep</i>
                        </button>
                    @endif
                </div>

                {{-- Order Details --}}
                @if($orderType === 'dine_in')
                    <div class="order-details {{ !$selectedTable ? 'warning-border' : '' }}">
                        <div class="detail-row">
                            <label>
                                <i class="material-icons">table_restaurant</i>
                                Meja:
                            </label>
                            @if($selectedTable)
                                <button wire:click="openTableSelector" class="btn-change-table">
                                    {{ $tables->firstWhere('id', $selectedTable)->table_number ?? 'Pilih Meja' }}
                                    <i class="material-icons">edit</i>
                                </button>
                            @else
                                <button wire:click="openTableSelector" class="btn-select-table pulse">
                                    <i class="material-icons">warning</i>
                                    Pilih Meja
                                </button>
                            @endif
                        </div>
                        <div class="detail-row">
                            <label>
                                <i class="material-icons">people</i>
                                Tamu:
                            </label>
                            <input 
                                type="number" 
                                wire:model="guestCount"
                                min="1"
                                class="input-guests"
                            >
                        </div>
                    </div>
                @endif

                {{-- Customer Info --}}
                <div class="customer-info">
                    <input 
                        type="text" 
                        wire:model="customerName"
                        placeholder="Nama pelanggan (opsional)"
                        class="input-customer"
                    >
                </div>

                {{-- Cart Items --}}
                <div class="cart-items">
                    @if(empty($cart))
                        <div class="empty-cart">
                            <i class="material-icons">shopping_cart</i>
                            <p>Keranjang kosong</p>
                            <small>Pilih produk untuk memulai</small>
                        </div>
                    @else
                        @foreach($cart as $key => $item)
                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="{{ $item['product_image'] }}" alt="{{ $item['product_name'] }}">
                                </div>
                                <div class="item-details">
                                    <h6 class="item-name">{{ $item['product_name'] }}</h6>
                                    
                                    {{-- Modifiers --}}
                                    @if(!empty($item['modifiers']))
                                        <div class="item-modifiers">
                                            @foreach($item['modifiers'] as $modifier)
                                                <span class="modifier-tag">
                                                    + {{ $modifier['name'] }}
                                                    @if($modifier['price'] > 0)
                                                        (Rp {{ number_format($modifier['price'], 0, ',', '.') }})
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Special Instructions --}}
                                    @if($item['special_instructions'])
                                        <div class="item-notes">
                                            <i class="material-icons">note</i>
                                            {{ $item['special_instructions'] }}
                                        </div>
                                    @endif

                                    {{-- Price & Quantity --}}
                                    <div class="item-footer">
                                        <div class="item-price">
                                            Rp {{ number_format($item['item_total'], 0, ',', '.') }}
                                        </div>
                                        <div class="quantity-controls">
                                            <button 
                                                wire:click="updateQuantity('{{ $key }}', 'decrease')"
                                                class="qty-btn"
                                            >
                                                <i class="material-icons">remove</i>
                                            </button>
                                            <span class="qty-value">{{ $item['quantity'] }}</span>
                                            <button 
                                                wire:click="updateQuantity('{{ $key }}', 'increase')"
                                                class="qty-btn"
                                            >
                                                <i class="material-icons">add</i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button 
                                    wire:click="removeFromCart('{{ $key }}')"
                                    class="btn-remove-item"
                                >
                                    <i class="material-icons">close</i>
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Cart Summary --}}
                @if(!empty($cart))
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($applyServiceCharge && $serviceCharge > 0)
                            <div class="summary-row">
                                <span>Service ({{ $serviceChargeRate * 100 }}%)</span>
                                <span>Rp {{ number_format($serviceCharge, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="summary-row">
                            <span>Pajak ({{ $taxRate * 100 }}%)</span>
                            <span>Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                        </div>
                        @if($discountAmount > 0)
                            <div class="summary-row discount">
                                <span>Diskon</span>
                                <span>- Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="summary-row total">
                            <span>TOTAL</span>
                            <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="kitchen-toggle-row" wire:click="$toggle('trackInKitchen')">
                        <div class="kitchen-toggle-left">
                            <div class="kitchen-toggle-icon {{ $trackInKitchen ? 'active' : '' }}">
                                <i class="material-icons">restaurant</i>
                            </div>
                            <div>
                                <span class="kitchen-toggle-label">Send to Kitchen Board</span>
                                <span class="kitchen-toggle-desc {{ $trackInKitchen ? 'active' : '' }}">
                                    {{ $trackInKitchen ? 'Tampil di Kanban Dashboard' : 'Tidak dikirim ke dapur' }}
                                </span>
                            </div>
                        </div>
                        <div class="kitchen-toggle-switch {{ $trackInKitchen ? 'on' : '' }}">
                            <div class="kitchen-toggle-knob"></div>
                        </div>
                    </div>

                    <div class="cart-actions">
                        <button 
                            wire:click="parkOrder"
                            class="btn-secondary"
                        >
                            <i class="material-icons">bookmark</i>
                            Park
                        </button>
                        <button 
                            wire:click="proceedToPayment"
                            class="btn-primary"
                        >
                            <i class="material-icons">payment</i>
                            Bayar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modifier Modal --}}
    @if($showModifierModal && $currentProductId)
        @php
            $currentProduct = \App\Models\Product::with(['modifierGroups.modifiers'])->find($currentProductId);
        @endphp
        
        @if($currentProduct)
        <div class="modal-overlay" wire:click="closeModifierModal">
            <div class="modal-container modifier-modal" wire:click.stop>
                {{-- Modal Header --}}
                <div class="modal-header">
                    <div>
                        <h5>{{ $currentProduct->name }}</h5>
                        <p class="modal-subtitle">Rp {{ number_format($currentProduct->base_price, 0, ',', '.') }}</p>
                    </div>
                    <button wire:click="closeModifierModal" class="modal-close">
                        <i class="material-icons">close</i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body">
                    {{-- Product Image --}}
                    <div class="modal-product-image">
                        <img src="{{ $currentProduct->medium_image }}" alt="{{ $currentProduct->name }}">
                    </div>

                    {{-- Modifier Groups --}}
                    @foreach($currentProduct->modifierGroups as $group)
                        <div class="modifier-group">
                            <div class="group-header">
                                <h6>{{ $group->name }}</h6>
                                @if($group->is_required)
                                    <span class="badge-required">Wajib</span>
                                @endif
                                @if($group->min_selections > 0 || $group->max_selections)
                                    <span class="badge-info">
                                        @if($group->selection_type === 'single')
                                            Pilih 1
                                        @else
                                            Pilih {{ $group->min_selections }}-{{ $group->max_selections ?? '∞' }}
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <div class="modifiers-list">
                                @foreach($group->modifiers as $modifier)
                                    <label class="modifier-item">
                                        <input 
                                            type="{{ $group->selection_type === 'single' ? 'radio' : 'checkbox' }}"
                                            wire:model="selectedModifiers.{{ $modifier->id }}"
                                            name="group_{{ $group->id }}"
                                            value="true"
                                        >
                                        <span class="modifier-name">{{ $modifier->name }}</span>
                                        @if($modifier->price > 0)
                                            <span class="modifier-price">+ Rp {{ number_format($modifier->price, 0, ',', '.') }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    {{-- Special Instructions --}}
                    <div class="special-instructions">
                        <label>
                            <i class="material-icons">note</i>
                            Catatan Khusus (opsional)
                        </label>
                        <textarea 
                            wire:model="specialInstructions"
                            placeholder="Contoh: Tidak pedas, tanpa bawang..."
                            rows="3"
                        ></textarea>
                    </div>

                    {{-- Quantity Selector --}}
                    <div class="quantity-selector">
                        <label>Jumlah:</label>
                        <div class="quantity-controls large">
                            <button 
                                wire:click="$set('itemQuantity', {{ max(1, $itemQuantity - 1) }})"
                                class="qty-btn"
                            >
                                <i class="material-icons">remove</i>
                            </button>
                            <span class="qty-value">{{ $itemQuantity }}</span>
                            <button 
                                wire:click="$set('itemQuantity', {{ $itemQuantity + 1 }})"
                                class="qty-btn"
                            >
                                <i class="material-icons">add</i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer">
                    <button wire:click="closeModifierModal" class="btn-secondary">
                        Batal
                    </button>
                    <button wire:click="addWithModifiers" class="btn-primary">
                        <i class="material-icons">add_shopping_cart</i>
                        Tambah ke Keranjang
                    </button>
                </div>
            </div>
        </div>
        @endif
    @endif

    {{-- Table Selector Modal --}}
    @if($showTableSelector)
        <div class="modal-overlay" wire:click="closeTableSelector">
            <div class="modal-container table-selector-modal" wire:click.stop>
                <div class="modal-header">
                    <h5>Pilih Meja</h5>
                    <button wire:click="closeTableSelector" class="modal-close">
                        <i class="material-icons">close</i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="tables-grid">
                        @forelse($tables as $table)
                            <button 
                                wire:click="selectTable('{{ $table->id }}')"
                                class="table-card {{ $table->status !== 'available' ? 'occupied' : '' }} {{ $selectedTable === $table->id ? 'selected' : '' }}"
                                {{ $table->status !== 'available' ? 'disabled' : '' }}
                            >
                                <i class="material-icons">table_restaurant</i>
                                <span class="table-number">{{ $table->table_number }}</span>
                                <span class="table-status">{{ $table->status_label }}</span>
                            </button>
                        @empty
                            <p class="text-center">Tidak ada meja tersedia</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Payment Modal (Continuing in next part...) --}}

    {{-- Payment Modal --}}
    @if($showPaymentModal)
        <div class="modal-overlay">
            <div class="modal-container payment-modal" wire:click.stop>
                <div class="modal-header">
                    <h5>Pembayaran</h5>
                    <button wire:click="closePaymentModal" class="modal-close">
                        <i class="material-icons">close</i>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Order Summary --}}
                    <div class="payment-summary">
                        <h6>Ringkasan Order</h6>
                        <div class="summary-details">
                            <div class="detail-row">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if($serviceCharge > 0)
                                <div class="detail-row">
                                    <span>Service</span>
                                    <span>Rp {{ number_format($serviceCharge, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="detail-row">
                                <span>Pajak</span>
                                <span>Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                            </div>
                            <div class="detail-row total">
                                <span>TOTAL</span>
                                <span class="total-amount">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Methods --}}
                    <div class="payment-methods">
                        <h6>Metode Pembayaran</h6>
                        <div class="payment-method-grid">
                            @foreach($paymentMethods as $method)
                                <button 
                                    wire:click="$set('selectedPayments.0.method_id', '{{ $method->id }}')"
                                    class="payment-method-btn {{ isset($selectedPayments[0]) && $selectedPayments[0]['method_id'] === $method->id ? 'active' : '' }}"
                                >
                                    @if($method->payment_type === 'cash')
                                        <i class="material-icons">payments</i>
                                    @elseif($method->payment_type === 'qr')
                                        <i class="material-icons">qr_code_scanner</i>
                                    @elseif($method->payment_type === 'card')
                                        <i class="material-icons">credit_card</i>
                                    @else
                                        <i class="material-icons">payment</i>
                                    @endif
                                    <span>{{ $method->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Cash Payment Input --}}
                    @php
                        $selectedMethod = isset($selectedPayments[0]['method_id']) 
                            ? $paymentMethods->firstWhere('id', $selectedPayments[0]['method_id']) 
                            : null;
                    @endphp
                    
                    @if($selectedMethod && $selectedMethod->payment_type === 'cash')
                        <div class="cash-payment-section">
                            <div class="form-group">
                                <label>Uang Diterima</label>
                                <div class="input-with-prefix">
                                    <span class="prefix">Rp</span>
                                    <input 
                                        type="number" 
                                        wire:model.live="selectedPayments.0.cash_received"
                                        placeholder="0"
                                        class="form-control"
                                    >
                                </div>
                            </div>

                            @if(isset($selectedPayments[0]['cash_received']) && $selectedPayments[0]['cash_received'] >= $grandTotal)
                                <div class="change-display">
                                    <span>Kembalian:</span>
                                    <span class="change-amount">
                                        Rp {{ number_format($selectedPayments[0]['cash_received'] - $grandTotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif

                            {{-- Quick Amount Buttons --}}
                            <div class="quick-amounts">
                                @php
                                    $amounts = [50000, 100000, 150000, 200000, 500000];
                                @endphp
                                @foreach($amounts as $amount)
                                    <button 
                                        wire:click="$set('selectedPayments.0.cash_received', {{ $amount }})"
                                        class="quick-amount-btn"
                                    >
                                        Rp {{ number_format($amount, 0, ',', '.') }}
                                    </button>
                                @endforeach
                                <button 
                                    wire:click="$set('selectedPayments.0.cash_received', {{ ceil($grandTotal / 1000) * 1000 }})"
                                    class="quick-amount-btn"
                                >
                                    Pas
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button wire:click="closePaymentModal" class="btn-secondary">
                        Batal
                    </button>
                    <button 
                        wire:click="completeOrder" 
                        class="btn-primary btn-lg"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                    >
                        <span wire:loading.remove wire:target="completeOrder">
                            <i class="material-icons">check_circle</i>
                            Selesaikan Pembayaran
                        </span>
                        <span wire:loading wire:target="completeOrder">
                            <i class="material-icons">hourglass_empty</i>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Parked Orders Modal --}}
    @if($showParkedOrders)
        <div class="modal-overlay" wire:click="$set('showParkedOrders', false)">
            <div class="modal-container parked-orders-modal" wire:click.stop>
                <div class="modal-header">
                    <h5>Parked Orders</h5>
                    <button wire:click="$set('showParkedOrders', false)" class="modal-close">
                        <i class="material-icons">close</i>
                    </button>
                </div>
                <div class="modal-body">
                    @forelse($parkedOrders as $index => $parked)
                        <div class="parked-order-card">
                            <div class="parked-order-header">
                                <div>
                                    <strong>{{ $parked['customer_name'] ?: 'Guest' }}</strong>
                                    @if($parked['order_type'] === 'dine_in' && isset($parked['table_number']))
                                        <span class="badge">Meja {{ $parked['table_number'] }}</span>
                                    @else
                                        <span class="badge">Takeaway</span>
                                    @endif
                                </div>
                                <small>{{ \Carbon\Carbon::parse($parked['parked_at'])->diffForHumans() }}</small>
                            </div>
                            <div class="parked-order-body">
                                <p class="items-count">{{ count($parked['cart']) }} item(s)</p>
                                <p class="total-amount">Rp {{ number_format($parked['grand_total'], 0, ',', '.') }}</p>
                            </div>
                            <div class="parked-order-actions">
                                <button 
                                    wire:click="loadParkedOrder({{ $index }})"
                                    class="btn-load"
                                >
                                    <i class="material-icons">restore</i>
                                    Muat
                                </button>
                                <button 
                                    wire:click="deleteParkedOrder({{ $index }})"
                                    class="btn-delete"
                                >
                                    <i class="material-icons">delete</i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="material-icons">bookmark_border</i>
                            <p>Tidak ada parked orders</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Styles --}}
<style>
:root {
    --primary: #ff6b6b;
    --primary-dark: #ee5252;
    --primary-light: #ff8787;
    --success: #51cf66;
    --warning: #ffd43b;
    --danger: #ff6b6b;
    --info: #339af0;
    --dark: #212529;
    --light: #f8f9fa;
    --border: #dee2e6;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.pos-modern-container {
    background: #f5f6fa;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Header */
.pos-header {
    background: white;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.pos-header-left {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.pos-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--dark);
    margin: 0;
}

.pos-title i {
    color: var(--primary);
}

.order-type-selector {
    display: flex;
    gap: 0.5rem;
    background: var(--light);
    padding: 0.25rem;
    border-radius: 8px;
}

.order-type-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
    color: #666;
}

.order-type-btn:hover {
    background: white;
}

.order-type-btn.active {
    background: var(--primary);
    color: white;
    box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
}

.btn-parked-orders {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--warning);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    color: var(--dark);
}

/* Main Content */
.pos-main-content {
    flex: 1;
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 1rem;
    padding: 1rem;
}

@media (min-width: 1400px) {
    .pos-main-content {
        grid-template-columns: 1fr 450px;
        gap: 1.5rem;
        padding: 1.5rem;
    }
}

@media (max-width: 1200px) {
    .pos-main-content {
        grid-template-columns: 1fr 350px;
        gap: 0.75rem;
        padding: 1rem;
    }
}

/* Products Section */
.pos-products-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.products-controls {
    background: white;
    padding: 1rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.search-box {
    position: relative;
    width: 100%;
}

.search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 3rem;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
}

/* Category Tabs */
.category-tabs {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    background: white;
    padding: 1rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.category-tab {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--light);
    border: 2px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
    color: #666;
}

.category-tab:hover {
    background: #e9ecef;
}

.category-tab.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary-dark);
}

.category-tab .badge {
    background: rgba(255,255,255,0.3);
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
    background: white;
    padding: 1rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    max-height: calc(100vh - 320px);
    overflow-y: auto;
}

.product-card {
    background: white;
    border: 2px solid var(--border);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    overflow: hidden;
}

.product-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.2);
}

.product-image {
    position: relative;
    width: 100%;
    height: 140px;
    background: var(--light);
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.badge-customizable {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: var(--primary);
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.badge-customizable i {
    font-size: 14px;
}

.product-info {
    padding: 0.75rem;
}

.product-name {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: var(--dark);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-price {
    font-size: 1rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.25rem;
}

.product-sku {
    font-size: 0.75rem;
    color: #999;
}

.empty-products {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem;
    color: #999;
}

.empty-products i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

/* Cart Section */
.pos-cart-section {
    position: sticky;
    top: 90px;
    height: calc(100vh - 110px);
}

.cart-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.cart-header {
    padding: 1.5rem;
    border-bottom: 2px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-header h5 {
    margin: 0;
    font-size: 1.25rem;
}

.btn-clear-cart {
    background: var(--danger);
    color: white;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-clear-cart:hover {
    transform: scale(1.1);
}

.order-details {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
}

.order-details.warning-border {
    background: rgba(255, 193, 7, 0.1);
    border: 2px solid var(--warning);
    border-radius: 8px;
    margin: 0.5rem 1rem;
    padding: 1rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.detail-row label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #666;
}

.btn-select-table,
.btn-change-table {
    padding: 0.5rem 1rem;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-select-table.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
    }
}

.input-guests {
    width: 80px;
    padding: 0.5rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    text-align: center;
}

.customer-info {
    padding: 0.5rem 1.5rem 1rem;
    border-bottom: 1px solid var(--border);
}

.input-customer {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 6px;
}

/* Cart Items */
.cart-items {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.5rem;
}

.empty-cart {
    text-align: center;
    padding: 3rem 1rem;
    color: #999;
}

.empty-cart i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.cart-item {
    display: flex;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--light);
    border-radius: 8px;
    margin-bottom: 0.75rem;
    position: relative;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-name {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.item-modifiers {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    margin-bottom: 0.5rem;
}

.modifier-tag {
    font-size: 0.75rem;
    background: white;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    color: #666;
}

.item-notes {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: #666;
    margin-bottom: 0.5rem;
}

.item-notes i {
    font-size: 14px;
}

.item-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.item-price {
    font-weight: 700;
    color: var(--primary);
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: white;
    border-radius: 6px;
    padding: 0.25rem;
}

.qty-btn {
    width: 28px;
    height: 28px;
    border: none;
    background: var(--primary);
    color: white;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.qty-btn:hover {
    transform: scale(1.1);
}

.qty-value {
    font-weight: 600;
    min-width: 24px;
    text-align: center;
}

.btn-remove-item {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 24px;
    height: 24px;
    border: none;
    background: var(--danger);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

/* Cart Summary */
.cart-summary {
    padding: 1rem 1.5rem;
    border-top: 2px solid var(--border);
    background: var(--light);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.summary-row.total {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 2px solid var(--border);
}

/* Cart Actions */
.cart-actions {
    padding: 1rem 1.5rem;
    display: flex;
    gap: 0.75rem;
}

.btn-secondary {
    flex: 1;
    padding: 1rem;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.btn-primary {
    flex: 2;
    padding: 1rem;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
}

/* Loading */
.loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid var(--light);
    border-top-color: var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* CONTINUING WITH MODALS IN NEXT PART... */

/* Modal Styles */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-container {
    background: white;
    border-radius: 16px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 2px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h5 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.modal-subtitle {
    color: var(--primary);
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 0.25rem;
}

.modal-close {
    width: 36px;
    height: 36px;
    border: none;
    background: var(--light);
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.modal-close:hover {
    background: var(--danger);
    color: white;
}

.modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 2px solid var(--border);
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

/* Modifier Modal */
.modifier-modal {
    max-width: 700px;
}

.modal-product-image {
    width: 100%;
    height: 200px;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.modal-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.modifier-group {
    margin-bottom: 2rem;
}

.group-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.group-header h6 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.badge-required {
    background: var(--danger);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-info {
    background: var(--info);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
}

.modifiers-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.modifier-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    background: var(--light);
    border: 2px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.modifier-item:hover {
    background: #e9ecef;
}

.modifier-item input {
    margin-right: 0.75rem;
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.modifier-item input:checked ~ .modifier-name {
    font-weight: 600;
}

.modifier-item:has(input:checked) {
    background: rgba(255, 107, 107, 0.1);
    border-color: var(--primary);
}

.modifier-name {
    flex: 1;
}

.modifier-price {
    color: var(--primary);
    font-weight: 600;
}

.special-instructions {
    margin-top: 1.5rem;
}

.special-instructions label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.special-instructions textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    resize: vertical;
    font-family: inherit;
}

.quantity-selector {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.quantity-selector label {
    font-weight: 600;
}

.quantity-controls.large {
    padding: 0.5rem;
}

.quantity-controls.large .qty-btn {
    width: 40px;
    height: 40px;
}

.quantity-controls.large .qty-value {
    font-size: 1.25rem;
    min-width: 40px;
}

/* Table Selector Modal */
.table-selector-modal {
    max-width: 800px;
}

.tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
}

.table-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    background: var(--light);
    border: 2px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
}

.table-card:hover:not(.occupied):not(:disabled) {
    background: #e9ecef;
    border-color: var(--primary);
}

.table-card.selected {
    background: var(--primary);
    color: white;
    border-color: var(--primary-dark);
}

.table-card.occupied {
    background: #ffebee;
    color: #999;
    cursor: not-allowed;
}

.table-card i {
    font-size: 2rem;
}

.table-number {
    font-size: 1.25rem;
    font-weight: 700;
}

.table-status {
    font-size: 0.75rem;
    text-transform: uppercase;
}

/* Payment Modal */
.payment-modal {
    max-width: 600px;
}

.payment-summary {
    background: var(--light);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.payment-summary h6 {
    margin-bottom: 1rem;
}

.summary-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.payment-summary .detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0;
}

.payment-summary .detail-row.total {
    font-size: 1.5rem;
    font-weight: 700;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 2px solid var(--border);
}

.total-amount {
    color: var(--primary);
}

.payment-methods h6 {
    margin-bottom: 1rem;
}

.payment-method-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.payment-method-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    background: var(--light);
    border: 2px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-method-btn:hover {
    background: #e9ecef;
}

.payment-method-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary-dark);
}

.payment-method-btn i {
    font-size: 2rem;
}

.cash-payment-section {
    background: var(--light);
    padding: 1.5rem;
    border-radius: 12px;
    margin-top: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.input-with-prefix {
    display: flex;
    border: 2px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
}

.input-with-prefix .prefix {
    padding: 0.75rem 1rem;
    background: #e9ecef;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.input-with-prefix .form-control {
    flex: 1;
    border: none;
    padding: 0.75rem;
    font-size: 1.25rem;
    font-weight: 600;
}

.input-with-prefix .form-control:focus {
    outline: none;
}

.change-display {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--success);
    color: white;
    border-radius: 8px;
    margin-top: 1rem;
    font-size: 1.25rem;
    font-weight: 600;
}

.quick-amounts {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
    margin-top: 1rem;
}

.quick-amount-btn {
    padding: 0.75rem;
    background: white;
    border: 2px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.quick-amount-btn:hover {
    border-color: var(--primary);
    background: rgba(255, 107, 107, 0.1);
}

/* Parked Orders Modal */
.parked-orders-modal {
    max-width: 600px;
}

.parked-order-card {
    background: var(--light);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 0.75rem;
}

.parked-order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.parked-order-header strong {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.parked-order-header .badge {
    background: var(--primary);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.parked-order-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
}

.items-count {
    color: #666;
}

.parked-order-body .total-amount {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
}

.parked-order-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.btn-load {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-delete {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    background: var(--danger);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #999;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

/* Kitchen Board Toggle */
.kitchen-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 1.5rem;
    margin: 0;
    cursor: pointer;
    border-top: 1px solid var(--border);
    transition: background 0.2s;
    user-select: none;
}

.kitchen-toggle-row:hover {
    background: rgba(255, 107, 107, 0.03);
}

.kitchen-toggle-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.kitchen-toggle-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--border);
    transition: background 0.3s;
    flex-shrink: 0;
}

.kitchen-toggle-icon.active {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
}

.kitchen-toggle-icon i {
    color: white;
    font-size: 18px;
}

.kitchen-toggle-label {
    display: block;
    font-size: 0.813rem;
    font-weight: 600;
    color: var(--dark);
    line-height: 1.2;
}

.kitchen-toggle-desc {
    display: block;
    font-size: 0.7rem;
    color: #999;
    font-weight: 500;
    transition: color 0.3s;
}

.kitchen-toggle-desc.active {
    color: var(--primary);
}

.kitchen-toggle-switch {
    position: relative;
    width: 40px;
    height: 22px;
    background: #c1c1c1;
    border-radius: 22px;
    transition: background 0.3s;
    flex-shrink: 0;
}

.kitchen-toggle-switch.on {
    background: var(--primary);
}

.kitchen-toggle-knob {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 18px;
    height: 18px;
    background: white;
    border-radius: 50%;
    transition: left 0.3s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.kitchen-toggle-switch.on .kitchen-toggle-knob {
    left: 20px;
}

/* Responsive */
@media (max-width: 1200px) {
    .pos-main-content {
        grid-template-columns: 1fr 400px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }
}

/* Tablet Optimization (iPad/Android Tablet) */
@media (min-width: 768px) and (max-width: 1024px) {
    .pos-header {
        padding: 0.75rem 1rem;
    }
    
    .pos-title {
        font-size: 1.1rem;
    }
    
    .pos-main-content {
        grid-template-columns: 1fr 380px;
        padding: 1rem;
        gap: 1rem;
    }
    
    .pos-cart-section {
        top: 80px;
        height: calc(100vh - 100px);
    }
    
    /* Smaller product cards */
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
        padding: 0.75rem;
    }
    
    .product-card {
        border-radius: 8px;
    }
    
    .product-image {
        height: 120px;
    }
    
    .product-info {
        padding: 0.5rem;
    }
    
    .product-name {
        font-size: 0.813rem;
        -webkit-line-clamp: 2;
    }
    
    .product-price {
        font-size: 0.9rem;
    }
    
    /* Compact category tabs */
    .category-tabs {
        padding: 0.75rem;
        gap: 0.375rem;
    }
    
    .category-tab {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    /* Compact cart */
    .cart-header {
        padding: 1rem;
    }
    
    .cart-header h5 {
        font-size: 1.1rem;
    }
    
    .order-details {
        padding: 0.75rem 1rem;
    }
    
    .cart-items {
        padding: 0.75rem 1rem;
    }
    
    .cart-item {
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }
    
    .item-image {
        width: 50px;
        height: 50px;
    }
    
    .item-name {
        font-size: 0.85rem;
    }
    
    .modifier-tag {
        font-size: 0.7rem;
    }
    
    /* Compact cart summary */
    .cart-summary {
        padding: 0.75rem 1rem;
    }
    
    .summary-row {
        font-size: 0.85rem;
        margin-bottom: 0.375rem;
    }
    
    .summary-row.total {
        font-size: 1.1rem;
    }
    
    /* Compact buttons */
    .cart-actions {
        padding: 0.75rem 1rem;
    }
    
    .btn-primary,
    .btn-secondary {
        padding: 0.75rem;
        font-size: 0.95rem;
    }
    
    /* Order type selector */
    .order-type-btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    /* Stats badges */
    .stat-badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
}

@media (max-width: 992px) {
    .pos-main-content {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .pos-cart-section {
        position: relative;
        height: auto;
        top: 0;
    }
    
    .cart-card {
        height: auto;
        max-height: 600px;
    }
    
    .products-grid {
        max-height: 500px;
    }
}

@media (max-width: 768px) {
    .pos-header-left {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .order-type-selector {
        width: 100%;
    }
    
    .order-type-btn {
        flex: 1;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }
    
    .modal-container {
        width: 95%;
    }
}
</style>

{{-- Scripts --}}
@push('scripts')
<script>
    // Toast notification system
    window.addEventListener('show-toast', event => {
        const { message, type } = event.detail;
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="material-icons">${type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info'}</i>
                <span>${message}</span>
            </div>
        `;
        
        // Add to body
        document.body.appendChild(toast);
        
        // Show toast
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Remove toast after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // ESC to close modals
        if (e.key === 'Escape') {
            @this.call('closeModifierModal');
            @this.call('closePaymentModal');
            @this.call('closeTableSelector');
        }
    });
</script>

{{-- Toast CSS --}}
<style>
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    z-index: 9999;
    transform: translateX(400px);
    transition: transform 0.3s;
}

.toast.show {
    transform: translateX(0);
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.toast-success { border-left: 4px solid var(--success); }
.toast-error { border-left: 4px solid var(--danger); }
.toast-info { border-left: 4px solid var(--info); }

.toast-success .material-icons { color: var(--success); }
.toast-error .material-icons { color: var(--danger); }
.toast-info .material-icons { color: var(--info); }
</style>
@endpush
</div>