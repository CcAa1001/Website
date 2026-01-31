<div class="container-fluid py-4">

    {{-- ===================== STYLES ===================== --}}
    <style>
        .qr-grid-container {
            width: 100%; max-width: 180px; aspect-ratio: 1/1; margin: 0 auto;
            padding: 10px; background: #fff; border: 1px solid #eee;
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: transform 0.2s;
        }
        .qr-grid-container:hover { transform: scale(1.05); border-color: #e91e63; }
        .qr-grid-container svg { width: 100% !important; height: auto !important; }
    </style>

    {{-- ===================== NOTIFICATION ===================== --}}
    @if (session()->has('message'))
    <div class="alert alert-success text-white px-4 py-2 mb-4 fade show" role="alert">
        <i class="material-icons text-sm me-2">check_circle</i>
        {{ session('message') }}
    </div>
    @endif

    {{-- ===================== HEADER & ACTIONS ===================== --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-4">
                        <h6 class="text-white mb-0">Manajemen Meja</h6>
                        {{-- Tombol Tambah yang memanggil fungsi create() --}}
                        <button class="btn bg-white text-primary mb-0" wire:click="create">
                            <i class="material-icons text-sm">add</i> Tambah Meja
                        </button>
                    </div>
                </div>

                <div class="card-body px-4 pb-2">
                    {{-- Filter --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Pilih Outlet</label>
                            <select class="form-select border px-2" wire:model="selectedOutlet">
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Filter Area</label>
                            <select class="form-select border px-2" wire:model="selectedArea">
                                <option value="">Semua Area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- GRID MEJA --}}
                    <div class="row">
                        @forelse($tables as $table)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 shadow-sm border">
                                <div class="card-body text-center p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-light text-dark mb-1">{{ $table->tableArea->name ?? 'Main Area' }}</span>
                                        <span class="badge {{ $table->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $table->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </div>
                                    
                                    <h4 class="mb-0 font-weight-bold">Meja {{ $table->table_number }}</h4>
                                    <p class="text-xs text-muted">Kapasitas: {{ $table->capacity }} Orang</p>

                                    {{-- QR Code Preview --}}
                                    <div class="qr-grid-container mb-3" wire:click="showQR('{{ $table->id }}')" title="Klik untuk Print">
                                        @php
                                            $url = route('table.login', ['code' => $table->qr_code ?: $table->id]);
                                        @endphp
                                        {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->margin(1)->generate($url) !!}
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button wire:click="edit('{{ $table->id }}')" class="btn btn-sm btn-info mb-0">Edit</button>
                                        <button wire:click="deleteTable('{{ $table->id }}')" 
                                                class="btn btn-sm btn-outline-danger mb-0"
                                                onclick="confirm('Yakin ingin menghapus meja ini?') || event.stopImmediatePropagation()">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-5">
                            <img src="https://img.icons8.com/ios/100/cccccc/table.png" class="mb-3">
                            <p class="text-secondary">Belum ada meja di outlet ini.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL FORM (ADD/EDIT) ===================== --}}
    {{-- Ini yang sebelumnya hilang --}}
    <div wire:ignore.self class="modal fade" id="tableFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditingTable ? 'Edit Meja' : 'Tambah Meja Baru' }}</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveTable">
                        
                        {{-- Outlet (Readonly if editing or forced) --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Outlet</label>
                            <select class="form-select border px-2 bg-light" wire:model="outlet_id" disabled>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Area & Nomor Meja --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Area</label>
                                <select class="form-select border px-2" wire:model="table_area_id">
                                    <option value="">-- Pilih Area --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                @error('table_area_id') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nomor Meja</label>
                                <input type="text" class="form-control border px-2" wire:model="table_number" placeholder="Contoh: 01, A5">
                                @error('table_number') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Kapasitas & Status --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Kapasitas (Orang)</label>
                                <input type="number" class="form-control border px-2" wire:model="capacity" min="1">
                                @error('capacity') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Status Aktif</label>
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" wire:model="is_table_active">
                                    <label class="form-check-label">{{ $is_table_active ? 'Aktif' : 'Non-Aktif' }}</label>
                                </div>
                            </div>
                        </div>

                        {{-- QR Code Custom (Optional) --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kode QR (Opsional)</label>
                            <input type="text" class="form-control border px-2" wire:model="qr_code" placeholder="Kosongkan untuk auto-generate">
                            <small class="text-muted text-xs">Biarkan kosong, sistem akan membuat kode unik otomatis.</small>
                            @error('qr_code') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

{{-- ===================== MODAL PREVIEW QR (PRINT) ===================== --}}
    @if($showQRModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,.6); z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                {{-- Header dengan Tombol Close (X) di Kanan Atas --}}
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title">QR Code Meja {{ $viewingTable->table_number }}</h5>
                    <button type="button" class="btn-close text-dark" wire:click="$set('showQRModal', false)" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center pt-0">
                    <div id="printableArea" class="p-4 bg-white mx-auto border rounded" style="width: 350px;">
                        <h5 class="fw-bold mb-3 text-uppercase">Scan Here to Order</h5>
                        
                        <div class="my-3">
                            {{-- Tampilkan QR Code PNG --}}
                            @php
                                echo '<img src="data:image/png;base64,' . base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(500)->margin(2)->generate($qrCodeUrl)) . '" style="width: 100%;">';
                            @endphp
                        </div>

                        <h3 class="fw-bold mb-0">MEJA {{ $viewingTable->table_number }}</h3>
                        <p class="text-muted text-sm">{{ $viewingTable->tableArea->name ?? '' }}</p>
                    </div>
                </div>

                {{-- Footer dengan Tombol Print & Tutup --}}
                <div class="modal-footer border-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary mb-0" wire:click="$set('showQRModal', false)">
                        Kembali
                    </button>
                    <button class="btn btn-dark mb-0" onclick="printDiv('printableArea')">
                        <i class="material-icons text-sm">print</i> Cetak QR
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ===================== SCRIPT UNTUK MODAL ===================== --}}
@push('js')
<script>
    // Inisialisasi Modal Bootstrap
    var myModal;
    
    // Listener saat tombol Edit/Tambah diklik
    window.addEventListener('open-modal-form', event => {
        var myModalEl = document.getElementById('tableFormModal');
        if (!myModal) {
            myModal = new bootstrap.Modal(myModalEl);
        }
        myModal.show();
    });

    // Listener saat data berhasil disimpan
    window.addEventListener('close-modal-form', event => {
        var myModalEl = document.getElementById('tableFormModal');
        var modal = bootstrap.Modal.getInstance(myModalEl);
        if (modal) {
            modal.hide();
        }
    });

    // Fungsi Print
    function printDiv(id) {
        var printContents = document.getElementById(id).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = 
            '<div style="display:flex; justify-content:center; align-items:center; height:100vh;">' + 
            printContents + 
            '</div>';
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload(); // Reload agar event listener livewire kembali aktif
    }
</script>
@endpush