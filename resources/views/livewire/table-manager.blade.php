<div class="container-fluid py-4">

{{-- ===================== CSS ===================== --}}
<style>
    /* === GRID QR (Card View) === */
    .qr-grid-container {
        width: 100%;
        max-width: 220px;
        aspect-ratio: 1 / 1;
        margin: 0 auto;
        padding: 12px;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    /* SVG hanya untuk GRID */
    .qr-grid-container svg {
        width: 100% !important;
        height: auto !important;
        display: block;
    }

    /* === MODAL / PRINT QR (PNG ONLY) === */
    .qr-modal-container {
        width: 100%;
        max-width: 420px;
        aspect-ratio: 1 / 1;
        margin: 0 auto;
        padding: 10px;
        background: #fff;
        border: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qr-modal-container img {
        width: 100%;
        height: auto;
        display: block;
    }
</style>

{{-- ===================== NOTIFICATION ===================== --}}
@if (session()->has('message'))
<div class="alert alert-success text-white px-4 py-2 mb-4">
    <i class="material-icons text-sm me-2">check_circle</i>
    {{ session('message') }}
</div>
@endif

{{-- ===================== HEADER ===================== --}}
<div class="row mb-4">
<div class="col-12">
<div class="card my-4">

<div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
<div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-4">
    <h6 class="text-white mb-0">Manajemen Meja</h6>
    <button class="btn bg-white text-primary mb-0"
        data-bs-toggle="modal"
        data-bs-target="#tableFormModal"
        wire:click="$set('isEditingTable', false)">
        <i class="material-icons text-sm">add</i> Tambah
    </button>
</div>
</div>

<div class="card-body px-4 pb-2">

{{-- ===================== GRID MEJA ===================== --}}
<div class="row">
@forelse($tables as $table)
<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
<div class="card h-100 shadow-sm">
<div class="card-body text-center d-flex flex-column">

<h5 class="mb-1">Meja {{ $table->table_number }}</h5>
<p class="text-xs text-muted mb-3">{{ $table->tableArea->name ?? '-' }}</p>

{{-- ===== GRID QR (SVG) ===== --}}
<div class="qr-grid-container mb-3"
     wire:click="showQR('{{ $table->id }}')"
     title="Klik untuk memperbesar">

@php
    $code = $table->qr_code ?: $table->id;
    $url  = route('table.login', ['code' => $code]);
@endphp

{!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->margin(1)->generate($url) !!}
</div>

<div class="mt-auto d-flex gap-2">
    <button wire:click="edit('{{ $table->id }}')" class="btn btn-sm btn-info w-100">Edit</button>
    <button wire:click="deleteTable('{{ $table->id }}')"
        class="btn btn-sm btn-outline-danger w-100"
        onclick="confirm('Hapus?') || event.stopImmediatePropagation()">
        Hapus
    </button>
</div>

<p class="text-xs text-muted mt-2 mb-0">ID: {{ $table->qr_code ?? '-' }}</p>

</div>
</div>
</div>
@empty
<div class="col-12 text-center py-5">
    <p class="text-secondary">Belum ada data meja.</p>
</div>
@endforelse
</div>

</div>
</div>
</div>
</div>

{{-- ===================== MODAL PREVIEW QR ===================== --}}
@if($showQRModal)
<div class="modal fade show d-block" style="background: rgba(0,0,0,.5)">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">Scan Menu - Meja {{ $viewingTable->table_number }}</h5>
    <button type="button" class="btn-close" wire:click="$set('showQRModal', false)"></button>
</div>

<div class="modal-body text-center">
<div id="printableArea" class="p-4 bg-white border rounded mx-auto" style="max-width:480px">

<h4 class="fw-bold mb-3">SCAN UNTUK PESAN</h4>

{{-- ===== MODAL QR (PNG – FIXED) ===== --}}
<div class="qr-modal-container mb-3">
@php
    echo '<img src="data:image/png;base64,' .
        base64_encode(
            SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(700)
            ->margin(1)
            ->generate($qrCodeUrl)
        ) . '">';
@endphp
</div>

<p class="fw-bold mb-0">
    {{ $viewingTable->tableArea->name ?? '' }} - Meja {{ $viewingTable->table_number }}
</p>
<p class="text-xs text-muted">{{ $qrCodeUrl }}</p>

</div>
</div>

<div class="modal-footer">
    <button class="btn bg-gradient-dark" onclick="printDiv('printableArea')">Print</button>
    <button class="btn btn-secondary" wire:click="$set('showQRModal', false)">Tutup</button>
</div>

</div>
</div>
</div>
@endif

</div>

{{-- ===================== JS ===================== --}}
@push('js')
<script>
function printDiv(id) {
    let content = document.getElementById(id).innerHTML;
    let original = document.body.innerHTML;
    document.body.innerHTML =
        '<div style="display:flex;justify-content:center;align-items:center;height:100vh">' +
        content + '</div>';
    window.print();
    document.body.innerHTML = original;
    location.reload();
}
</script>
@endpush
