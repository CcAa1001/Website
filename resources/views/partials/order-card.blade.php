{{-- Order Card Component (partials/order-card.blade.php) --}}
@php
    $minutesAgo = $order->created_at->diffInMinutes(now());
    $urgency = $minutesAgo > 15 ? 'urgent' : ($minutesAgo > 5 ? 'warning' : 'fresh');
    $urgencyIcon = $minutesAgo > 15 ? '🔴' : ($minutesAgo > 5 ? '🟡' : '🟢');
@endphp

<div class="order-card {{ $urgency }}" wire:key="order-{{ $order->id }}" data-order-id="{{ $order->id }}">
    {{-- Card Header --}}
    <div class="card-header-section">
        <div class="card-header-top">
            <span class="urgency-indicator">{{ $urgencyIcon }}</span>
            <div class="table-info">
                <h4 class="table-number">MEJA {{ $order->table->table_number ?? '-' }}</h4>
                <span class="guest-count">
                    <i class="material-icons">people</i>
                    {{ $order->guest_count ?? '-' }} tamu
                </span>
            </div>
        </div>
        <div class="card-header-bottom">
            <span class="order-number">#{{ $order->order_number }}</span>
            <span class="order-time">
                <i class="material-icons">schedule</i>
                <span class="live-timer" data-created="{{ $order->created_at->timestamp }}">
                    {{ $minutesAgo }}m ago
                </span>
            </span>
        </div>
    </div>

    {{-- Items List --}}
    <div class="items-section">
        @foreach($order->items->take(5) as $item)
            <div class="item-row">
                <div class="item-info">
                    <span class="item-name">{{ $item->product_name }}</span>
                    @if($item->modifiers && is_array(json_decode($item->modifiers, true)))
                        <div class="item-modifiers">
                            @foreach(json_decode($item->modifiers, true) as $modifier)
                                <span class="modifier-tag">
                                    + {{ is_array($modifier) ? ($modifier['modifier_name'] ?? $modifier['name'] ?? '') : $modifier }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                    @if($item->notes)
                        <div class="item-notes" data-full-note="{{ $item->notes }}">
                            <i class="material-icons">chat</i>
                            <span class="note-text">
                                {{ Str::limit($item->notes, 50) }}
                            </span>
                            @if(strlen($item->notes) > 50)
                                <button class="note-expand-btn" onclick="toggleNote(this)">
                                    <i class="material-icons">expand_more</i>
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
                <span class="item-quantity">x{{ $item->quantity }}</span>
            </div>
        @endforeach

        @if($order->items->count() > 5)
            <div class="more-items">
                <i class="material-icons">more_horiz</i>
                +{{ $order->items->count() - 5 }} item lainnya
            </div>
        @endif
    </div>

    {{-- Card Footer --}}
    <div class="card-footer-section">
        <div class="total-price">
            <span class="label">Total:</span>
            <span class="amount">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
        </div>
        <div class="card-actions">
            <button 
                wire:click="quickUpdateStatus('{{ $order->id }}', '{{ $nextStatus }}')"
                class="btn-action primary"
                wire:loading.attr="disabled"
                wire:target="quickUpdateStatus('{{ $order->id }}', '{{ $nextStatus }}')"
            >
                <span wire:loading.remove wire:target="quickUpdateStatus('{{ $order->id }}', '{{ $nextStatus }}')">
                    <i class="material-icons">arrow_forward</i>
                    {{ $nextLabel }}
                </span>
                <span wire:loading wire:target="quickUpdateStatus('{{ $order->id }}', '{{ $nextStatus }}')">
                    <i class="material-icons spinning">hourglass_empty</i>
                </span>
            </button>
            <button 
                class="btn-action secondary"
                onclick="showOrderDetails('{{ $order->id }}')"
            >
                <i class="material-icons">visibility</i>
            </button>
        </div>
    </div>
</div>