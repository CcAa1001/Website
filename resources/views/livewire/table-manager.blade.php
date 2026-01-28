<div class="container-fluid py-4">
    
    {{-- Notifikasi --}}
    @if (session()->has('message'))
        <div class="alert alert-success text-white px-4 py-2 mb-4" role="alert">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger text-white px-4 py-2 mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter & Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-4">
                        <h6 class="text-white text-capitalize ps-3 mb-0">Manajemen Meja & QR Code</h6>
                        <button class="btn bg-white text-primary mb-0" data-bs-toggle="modal" data-bs-target="#tableFormModal" wire:click="$set('isEditingTable', false)">
                            <i class="material-icons text-sm">add</i> Tambah Meja
                        </button>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    
                    {{-- Filter Area --}}
                    <div class="px-4 pb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group input-group-outline my-3 is-filled">
                                    <label class="form-label">Filter Outlet</label>
                                    <select wire:model.live="selectedOutlet" class="form-control">
                                        @foreach($outlets as $outlet)
                                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-outline my-3 is-filled">
                                    <label class="form-label">Filter Area</label>
                                    <select wire:model.live="selectedArea" class="form-control">
                                        <option value="">Semua Area</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}">{{ $area->name }} ({{ $area->tables_count }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table List --}}
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nomor Meja</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Area</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kapasitas</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">QR Code</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tables as $table)
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Meja {{ $table->table_number }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $table->tableArea->name ?? '-' }}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-secondary">{{ $table->capacity }} Orang</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button wire:click="showQR('{{ $table->id }}')" class="btn btn-sm btn-outline-info mb-0">
                                            <i class="material-icons text-sm">qr_code</i> Lihat QR
                                        </button>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input" type="checkbox" wire:click="toggleTableStatus('{{ $table->id }}')" {{ $table->is_active ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <a href="javascript:;" wire:click="editTable('{{ $table->id }}')" class="text-secondary font-weight-bold text-xs me-3" data-bs-toggle="modal" data-bs-target="#tableFormModal">
                                            Edit
                                        </a>
                                        <a href="javascript:;" wire:click="deleteTable('{{ $table->id }}')" class="text-danger font-weight-bold text-xs" onclick="return confirm('Yakin hapus meja ini?')">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">
                                        Belum ada meja. Silakan tambah meja baru.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL FORM MEJA --}}
    <div class="modal fade" id="tableFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $this->tableFormTitle }}</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveTable">
                        <div class="input-group input-group-outline my-3 {{ $table_number ? 'is-filled' : '' }}">
                            <label class="form-label">Nomor Meja</label>
                            <input type="text" class="form-control" wire:model="table_number">
                        </div>
                        @error('table_number') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        <div class="input-group input-group-outline my-3 is-filled">
                            <label class="form-label">Area</label>
                            <select class="form-control" wire:model="table_area_id">
                                <option value="">-- Pilih Area --</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group input-group-outline my-3 {{ $capacity ? 'is-filled' : '' }}">
                            <label class="form-label">Kapasitas (Orang)</label>
                            <input type="number" class="form-control" wire:model="capacity">
                        </div>

                        <div class="input-group input-group-outline my-3 {{ $qr_code ? 'is-filled' : '' }}">
                            <label class="form-label">Custom QR Code (Opsional)</label>
                            <input type="text" class="form-control" wire:model="qr_code">
                        </div>
                        <small class="text-muted d-block mb-3">
                            <i class="material-icons text-xs">info</i> Tips: Isi dengan <b>https://google.com</b> untuk link website, atau biarkan kosong untuk menu restoran.
                        </small>

                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn bg-gradient-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL VIEW QR --}}
    @if($showQRModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); z-index: 1050;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Scan QR Code</h5>
                    <button type="button" class="btn-close text-dark" wire:click="$set('showQRModal', false)">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div id="printableArea" class="p-3 border rounded bg-white d-inline-block">
                        <h5 class="font-weight-bold mb-1">SCAN HERE</h5>
                        <p class="text-xs text-secondary mb-3">Meja {{ $viewingTable->table_number ?? '' }}</p>
                        
                        <div class="my-2 d-flex justify-content-center">
                            {!! $generatedQrSvg !!}
                        </div>
                        
                        <p class="text-xs text-muted mt-2">{{ $viewingTable->tableArea->name ?? '' }}</p>
                    </div>
                    
                    <div class="mt-3">
                        <small>Link: <a href="{{ $qrCodeUrl }}" target="_blank">{{ $qrCodeUrl }}</a></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" wire:click="regenerateQR('{{ $viewingTable->id ?? '' }}')">
                        <i class="material-icons text-sm">refresh</i> Reset
                    </button>
                    <button type="button" class="btn bg-gradient-dark" onclick="printDiv('printableArea')">
                        <i class="material-icons text-sm">print</i> Print
                    </button>
                    <button type="button" class="btn btn-secondary" wire:click="$set('showQRModal', false)">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('js')
<script>
    // Logic tutup modal otomatis
    window.addEventListener('close-modal', event => {
        var myModalEl = document.getElementById('tableFormModal');
        var modal = bootstrap.Modal.getInstance(myModalEl);
        if (modal) { modal.hide(); } else { new bootstrap.Modal(myModalEl).hide(); }
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    });

    function printDiv(divName) {
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = '<div style="display:flex;justify-content:center;align-items:center;height:100vh;text-align:center;">' + printContents + '</div>';
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload(); 
    }
</script>
@endpush