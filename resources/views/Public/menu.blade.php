@extends('layouts.public')

@section('title', 'Menu - Meja ' . $table->table_number)

@section('content')
{{-- Table Header Bar (Fixed on top) --}}
<div class="table_header_bar">
    <div class="table_header_left">
        <div class="table_info">
            <div class="table_icon">
                <i class="fas fa-utensils"></i>
            </div>
            <div class="table_details">
                <h4>Meja {{ $table->table_number }}</h4>
                <span>{{ $table->tableArea?->name ?? 'Main Area' }}</span>
            </div>
        </div>
        <span class="outlet_badge">{{ $outlet->name }}</span>
    </div>
    <div class="table_header_actions">
        <button type="button" class="action_btn_header" onclick="toggleSessionInfo()" title="Info Sesi">
            <i class="fas fa-info-circle"></i>
        </button>
        <button type="button" class="action_btn_header" id="btnViewOrders" title="Pesanan Saya">
            <i class="fas fa-receipt"></i>
            @if($session->order_count > 0)
                <span class="badge">{{ $session->order_count }}</span>
            @endif
        </button>
    </div>
</div>

{{-- Collapsible Session Info Card --}}
<div class="session_info_card" id="sessionInfoCard">
    <div class="session_stats">
        <div class="stat_item">
            <div class="stat_value">{{ $session->order_count }}</div>
            <div class="stat_label">Pesanan</div>
        </div>
        <div class="stat_item">
            <div class="stat_value">Rp {{ number_format($session->total_amount, 0, ',', '.') }}</div>
            <div class="stat_label">Total</div>
        </div>
        <div class="stat_item">
            <div class="stat_value">{{ $session->guest_count }}</div>
            <div class="stat_label">Tamu</div>
        </div>
        <div class="stat_item">
            <div class="stat_value session_timer">{{ $session->formatted_duration }}</div>
            <div class="stat_label">Durasi</div>
        </div>
    </div>
    <div class="quick_actions">
        <button type="button" class="quick_action_btn btn_waiter" onclick="callWaiter()">
            <i class="fas fa-bell"></i> Panggil Pelayan
        </button>
        <button type="button" class="quick_action_btn btn_bill" onclick="requestBill()">
            <i class="fas fa-file-invoice"></i> Minta Tagihan
        </button>
    </div>
</div>

 {{-- Category Slider (GrabFood Style) --}}
    @include('public.shop.partials.category-slider', [
        'sliderCategories' => $sliderCategories ?? collect(),
        'category' => $category ?? null
    ])
{{-- Original Shop Products Component --}}
@livewire('public.shop-products')
@endsection

@push('styles')
<style>
/* ==========================================
   HIDE MAIN NAVBAR ON THIS PAGE
   ========================================== */
header.fp_header,
.fp_header,
nav.navbar,
.main-header,
.site-header,
header {
    display: none !important;
}

/* Reset body padding if navbar adds it */
body {
    padding-top: 0 !important;
    margin-top: 0 !important;
}

/* ==========================================
   TABLE HEADER BAR
   ========================================== */
.table_header_bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #fff;
    padding: 12px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    min-height: 56px;
}

.table_header_left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.table_info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.table_icon {
    width: 38px;
    height: 38px;
    background: rgba(255,255,255,0.15);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.table_details h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    line-height: 1.2;
    color: #fff;
}

.table_details span {
    font-size: 11px;
    opacity: 0.8;
}

.outlet_badge {
    background: rgba(255,255,255,0.15);
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 11px;
    display: none;
}

@media (min-width: 576px) {
    .outlet_badge { display: inline-block; }
}

.table_header_actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.action_btn_header {
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,0.15);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    position: relative;
}

.action_btn_header:hover {
    background: rgba(255,255,255,0.25);
}

.action_btn_header .badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 16px;
    height: 16px;
    background: #ff6b6b;
    border-radius: 8px;
    font-size: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

/* ==========================================
   SESSION INFO CARD (Collapsible)
   ========================================== */
.session_info_card {
    position: fixed;
    top: 56px;
    left: 0;
    right: 0;
    z-index: 9998;
    background: #fff;
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    display: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.session_info_card.show {
    display: block;
    animation: slideDown 0.2s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.session_stats {
    display: flex;
    justify-content: space-around;
    margin-bottom: 12px;
}

.stat_item {
    text-align: center;
}

.stat_value {
    font-size: 16px;
    font-weight: 700;
    color: #333;
}

.stat_label {
    font-size: 10px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.quick_actions {
    display: flex;
    gap: 8px;
}

.quick_action_btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 12px;
    background: #f5f5f5;
    border: none;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    color: #555;
    cursor: pointer;
    transition: all 0.2s;
}

.quick_action_btn i {
    font-size: 12px;
}

.quick_action_btn.btn_waiter {
    background: #fff3cd;
    color: #856404;
}

.quick_action_btn.btn_waiter:hover {
    background: #ffe69c;
}

.quick_action_btn.btn_bill {
    background: #d4edda;
    color: #155724;
}

.quick_action_btn.btn_bill:hover {
    background: #b8dabc;
}

/* ==========================================
   RECENT ORDERS BAR
   ========================================== */
.recent_orders_bar {
    margin-top: 56px; /* Space for fixed header */
    background: #f8f9fa;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.recent_orders_scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 0 15px;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}

.recent_orders_scroll::-webkit-scrollbar {
    display: none;
}

.order_chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #fff;
    border-radius: 20px;
    font-size: 11px;
    white-space: nowrap;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.order_chip .order_num {
    font-weight: 600;
    color: #333;
}

.order_chip .order_status {
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 500;
    text-transform: uppercase;
}

.order_chip.pending .order_status {
    background: #fff3cd;
    color: #856404;
}

.order_chip.confirmed .order_status,
.order_chip.preparing .order_status {
    background: #cce5ff;
    color: #004085;
}

.order_chip.ready .order_status {
    background: #d4edda;
    color: #155724;
}

.order_chip.served .order_status,
.order_chip.completed .order_status {
    background: #e2e3e5;
    color: #383d41;
}

/* ==========================================
   ADJUSTMENTS FOR SHOP CONTENT
   ========================================== */
/* Add top margin for fixed header */
.shop_livewire_wrapper {
    margin-top: 56px; /* Height of table header bar */
}

/* If recent orders bar exists, shop doesn't need margin */
.recent_orders_bar + .shop_livewire_wrapper {
    margin-top: 0;
}

/* Hide the mobile page header since we have table header */
.shop_livewire_wrapper .mobile_page_header {
    display: none !important;
}

/* Make category slider sticky below table header */
.shop_livewire_wrapper .fp_category_slider_section {
    position: sticky;
    top: 56px; /* Height of table header bar */
    z-index: 100;
}

/* Adjust cart floating button position */
.cart_floating_btn {
    bottom: 20px !important;
    z-index: 1000 !important;
}
</style>
@endpush

@push('scripts')
<script>
// Toggle Session Info Card
function toggleSessionInfo() {
    const card = document.getElementById('sessionInfoCard');
    card.classList.toggle('show');
}

// Call Waiter
function callWaiter() {
    if (confirm('Panggil pelayan ke meja Anda?')) {
        fetch('/api/table/call-waiter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Pelayan akan segera datang');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
}

// Request Bill
function requestBill() {
    if (confirm('Minta tagihan untuk pembayaran?')) {
        fetch('/api/table/request-bill', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Permintaan tagihan dikirim');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
}
</script>
@endpush