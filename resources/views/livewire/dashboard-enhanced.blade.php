<div class="dashboard-modern">
    {{-- Enhanced Statistics Cards --}}
    <div class="stats-grid">
        <div class="stat-card revenue">
            <div class="stat-icon">
                <i class="material-icons">payments</i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Pendapatan Hari Ini</span>
                <h3 class="stat-value">Rp {{ number_format($todaysEarnings, 0, ',', '.') }}</h3>
                <span class="stat-trend positive">
                    <i class="material-icons">trending_up</i> +12% vs kemarin
                </span>
            </div>
        </div>

        <div class="stat-card orders">
            <div class="stat-icon">
                <i class="material-icons">receipt_long</i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Order Hari Ini</span>
                <h3 class="stat-value">{{ $totalOrders }}</h3>
                <span class="stat-trend positive">
                    <i class="material-icons">trending_up</i> +8% vs kemarin
                </span>
            </div>
        </div>

        <div class="stat-card active">
            <div class="stat-icon">
                <i class="material-icons">restaurant</i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Order Aktif</span>
                <h3 class="stat-value">{{ $activeOrdersCount }}</h3>
                <span class="stat-subtitle">Perlu perhatian</span>
            </div>
        </div>

        <div class="stat-card tables">
            <div class="stat-icon">
                <i class="material-icons">table_restaurant</i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Meja Terisi</span>
                <h3 class="stat-value">{{ $tableStats['occupied'] }}/{{ $tableStats['total'] }}</h3>
                <span class="stat-subtitle">{{ round(($tableStats['occupied'] / max($tableStats['total'], 1)) * 100) }}% occupancy</span>
            </div>
        </div>
    </div>

    {{-- Kanban Order Board --}}
    <div class="kanban-container">
        <div class="kanban-header">
            <h5 class="kanban-title">
                <i class="material-icons">view_kanban</i>
                Order Tracking Board
            </h5>
            <div class="kanban-actions">
                <button class="btn-refresh" wire:click="$refresh">
                    <i class="material-icons">refresh</i>
                    Refresh
                </button>
                <a href="{{ route('orders') }}" class="btn-view-all">
                    Lihat Semua
                    <i class="material-icons">arrow_forward</i>
                </a>
            </div>
        </div>

        <div class="kanban-board">
            {{-- PENDING Column --}}
            <div class="kanban-column pending">
                <div class="column-header">
                    <div class="column-title">
                        <i class="material-icons">new_releases</i>
                        <span>Baru ( Bayar Nanti )</span>
                    </div>
                    <span class="column-count">{{ $ordersByStatus['pending']->count() }}</span>
                </div>
                <div class="column-content">
                    @forelse($ordersByStatus['pending'] as $order)
                        @include('partials.order-card', ['order' => $order, 'nextStatus' => 'confirmed', 'nextLabel' => 'Konfirmasi'])
                    @empty
                        <div class="empty-column">
                            <i class="material-icons">check_circle</i>
                            <p>Tidak ada order baru</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- CONFIRMED Column --}}
            <div class="kanban-column confirmed">
                <div class="column-header">
                    <div class="column-title">
                        <i class="material-icons">check_circle</i>
                        <span>Dikonfirmasi ( Sudah Bayar ) </span>
                    </div>
                    <span class="column-count">{{ $ordersByStatus['confirmed']->count() }}</span>
                </div>
                <div class="column-content">
                    @forelse($ordersByStatus['confirmed'] as $order)
                        @include('partials.order-card', ['order' => $order, 'nextStatus' => 'preparing', 'nextLabel' => 'Proses'])
                    @empty
                        <div class="empty-column">
                            <i class="material-icons">restaurant</i>
                            <p>Siap diproses</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- PREPARING Column --}}
            <div class="kanban-column preparing">
                <div class="column-header">
                    <div class="column-title">
                        <i class="material-icons">restaurant_menu</i>
                        <span>Diproses</span>
                    </div>
                    <span class="column-count">{{ $ordersByStatus['preparing']->count() }}</span>
                </div>
                <div class="column-content">
                    @forelse($ordersByStatus['preparing'] as $order)
                        @include('partials.order-card', ['order' => $order, 'nextStatus' => 'ready', 'nextLabel' => 'Siap'])
                    @empty
                        <div class="empty-column">
                            <i class="material-icons">schedule</i>
                            <p>Belum ada yang diproses</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- READY Column --}}
            <div class="kanban-column ready">
                <div class="column-header">
                    <div class="column-title">
                        <i class="material-icons">done_all</i>
                        <span>Siap Diantar</span>
                    </div>
                    <span class="column-count">{{ $ordersByStatus['ready']->count() }}</span>
                </div>
                <div class="column-content">
                    @forelse($ordersByStatus['ready'] as $order)
                        @include('partials.order-card', ['order' => $order, 'nextStatus' => 'served', 'nextLabel' => 'Served'])
                    @empty
                        <div class="empty-column">
                            <i class="material-icons">delivery_dining</i>
                            <p>Tidak ada order siap</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Active Table Sessions --}}
    @if($activeSessions->count() > 0)
    <div class="sessions-card">
        <div class="sessions-header">
            <h6>
                <i class="material-icons">wifi</i>
                Active Table Sessions 
                <span class="badge-count">{{ $activeSessions->count() }}</span>
            </h6>
        </div>
        <div class="sessions-table-wrapper">
            <table class="sessions-table">
                <thead>
                    <tr>
                        <th>Meja</th>
                        <th>Duration</th>
                        <th>Orders</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeSessions as $session)
                        <tr wire:key="session-{{ $session->id }}">
                            <td>
                                <div class="table-cell">
                                    <i class="material-icons">table_restaurant</i>
                                    <strong>Meja {{ $session->table->table_number }}</strong>
                                </div>
                            </td>
                            <td>
                                <div class="duration-cell">
                                    <span class="timer" data-started="{{ $session->started_at->timestamp }}">
                                        {{ $session->started_at->diffForHumans() }}
                                    </span>
                                    <small>{{ $session->guest_count }} tamu</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge-pill">{{ $session->order_count }}</span>
                            </td>
                            <td class="text-center">
                                <strong>Rp {{ number_format($session->total_amount, 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="status-badge {{ $session->status }}">
                                    {{ ucfirst($session->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- Order Card Partial Template --}}
@push('templates')
<template id="order-card-template">
    <!-- This will be the order card template -->
</template>
@endpush