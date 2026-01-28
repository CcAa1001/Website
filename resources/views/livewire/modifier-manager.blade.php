<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1">Manajemen Modifier</h4>
                    <p class="text-sm text-muted mb-0">Kelola add-ons dan opsi tambahan produk</p>
                </div>
                <button wire:click="createGroup" class="btn bg-gradient-primary">
                    <i class="fas fa-plus me-2"></i> Tambah Grup Modifier
                </button>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="input-group input-group-outline">
                <label class="form-label"><i class="fas fa-search me-2"></i>Cari grup...</label>
                <input type="text" class="form-control" wire:model.live.debounce.300ms="search">
            </div>
        </div>
    </div>

    {{-- Modifier Groups --}}
    <div class="row">
        @forelse($groups as $group)
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    {{ $group->name }}
                                    @if($group->is_required)
                                        <span class="badge bg-warning text-dark ms-1">Wajib</span>
                                    @endif
                                    @if(!$group->is_active)
                                        <span class="badge bg-secondary ms-1">Nonaktif</span>
                                    @endif
                                </h6>
                                <p class="text-xs text-muted mb-0">
                                    {{ $group->selection_type === 'single' ? 'Pilih satu' : 'Pilih banyak' }}
                                    @if($group->min_selections > 0)
                                        • Min: {{ $group->min_selections }}
                                    @endif
                                    @if($group->max_selections)
                                        • Max: {{ $group->max_selections }}
                                    @endif
                                </p>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#" wire:click.prevent="editGroup('{{ $group->id }}')">
                                            <i class="fas fa-edit me-2 text-info"></i> Edit Grup
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" wire:click.prevent="createModifier('{{ $group->id }}')">
                                            <i class="fas fa-plus me-2 text-success"></i> Tambah Modifier
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" wire:click.prevent="toggleGroupActive('{{ $group->id }}')">
                                            <i class="fas fa-{{ $group->is_active ? 'eye-slash' : 'eye' }} me-2 text-warning"></i>
                                            {{ $group->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" wire:click.prevent="confirmDeleteGroup('{{ $group->id }}')">
                                            <i class="fas fa-trash me-2"></i> Hapus Grup
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        @if($group->modifiers->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Harga</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group->modifiers as $modifier)
                                            <tr>
                                                <td>
                                                    <span class="text-sm">{{ $modifier->name }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-sm font-weight-bold">
                                                        @if($modifier->price > 0)
                                                            +Rp {{ number_format($modifier->price, 0, ',', '.') }}
                                                        @else
                                                            <span class="text-muted">Gratis</span>
                                                        @endif
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-sm {{ $modifier->is_available ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $modifier->is_available ? 'Tersedia' : 'Habis' }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <button wire:click="editModifier('{{ $modifier->id }}')" 
                                                            class="btn btn-link text-info p-0 mb-0 me-2" title="Edit">
                                                        <i class="fas fa-edit fa-sm"></i>
                                                    </button>
                                                    <button wire:click="toggleModifierAvailable('{{ $modifier->id }}')" 
                                                            class="btn btn-link text-warning p-0 mb-0 me-2" 
                                                            title="{{ $modifier->is_available ? 'Set Habis' : 'Set Tersedia' }}">
                                                        <i class="fas fa-{{ $modifier->is_available ? 'eye-slash' : 'eye' }} fa-sm"></i>
                                                    </button>
                                                    <button wire:click="confirmDeleteModifier('{{ $modifier->id }}')" 
                                                            class="btn btn-link text-danger p-0 mb-0" title="Hapus">
                                                        <i class="fas fa-trash fa-sm"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-layer-group fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-2">Belum ada modifier</p>
                                <button wire:click="createModifier('{{ $group->id }}')" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i> Tambah Modifier
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer pt-0">
                        <button wire:click="createModifier('{{ $group->id }}')" class="btn btn-sm bg-gradient-info">
                            <i class="fas fa-plus me-1"></i> Tambah Modifier
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-layer-group fa-4x text-muted mb-3"></i>
                        <h5>Belum Ada Grup Modifier</h5>
                        <p class="text-muted">Buat grup modifier untuk menambahkan opsi ke produk Anda.</p>
                        <button wire:click="createGroup" class="btn bg-gradient-primary">
                            <i class="fas fa-plus me-2"></i> Buat Grup Pertama
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($groups->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $groups->links() }}
        </div>
    @endif

    {{-- Group Form Modal --}}
    @if($showGroupForm)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $isEditingGroup ? 'Edit Grup Modifier' : 'Tambah Grup Modifier' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeGroupForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Grup <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="groupName" 
                                   placeholder="Contoh: Level Pedas, Topping">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipe Pilihan</label>
                                <select class="form-control" wire:model="selectionType">
                                    <option value="single">Pilih Satu</option>
                                    <option value="multiple">Pilih Banyak</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Wajib Dipilih?</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" wire:model="isRequired">
                                    <label class="form-check-label">Ya, Wajib</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Minimum Pilihan</label>
                                <input type="number" class="form-control" wire:model="minSelections" min="0">
                                <small class="text-muted">0 = tidak ada minimum</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Maximum Pilihan</label>
                                <input type="number" class="form-control" wire:model="maxSelections" min="1" placeholder="Kosong = unlimited">
                                <small class="text-muted">Kosongkan jika tidak ada batasan</small>
                            </div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="groupIsActive" id="groupIsActive">
                            <label class="form-check-label" for="groupIsActive">Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeGroupForm">Batal</button>
                        <button type="button" class="btn bg-gradient-primary" wire:click="saveGroup" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveGroup">
                                <i class="fas fa-save me-1"></i> {{ $isEditingGroup ? 'Update' : 'Simpan' }}
                            </span>
                            <span wire:loading wire:target="saveGroup">
                                <i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modifier Form Modal --}}
    @if($showModifierForm)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editingModifierId ? 'Edit Modifier' : 'Tambah Modifier' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModifierForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Modifier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="modifierName" 
                                   placeholder="Contoh: Extra Cheese, Pedas Lv.3">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga Tambahan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" wire:model="modifierPrice" min="0" step="500">
                                </div>
                                <small class="text-muted">0 = gratis</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga Modal</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" wire:model="modifierCost" min="0" step="500">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Urutan Tampil</label>
                                <input type="number" class="form-control" wire:model="modifierSortOrder" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" wire:model="modifierAvailable">
                                    <label class="form-check-label">Tersedia</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeModifierForm">Batal</button>
                        <button type="button" class="btn bg-gradient-primary" wire:click="saveModifier" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveModifier">
                                <i class="fas fa-save me-1"></i> {{ $editingModifierId ? 'Update' : 'Simpan' }}
                            </span>
                            <span wire:loading wire:target="saveModifier">
                                <i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>Hapus {{ $deleteType === 'group' ? 'Grup' : 'Modifier' }}?</h5>
                        <p class="text-muted mb-0">{{ $deleteName }}</p>
                        @if($deleteType === 'group')
                            <p class="text-danger text-sm mt-2 mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Semua modifier dalam grup akan ikut terhapus!
                            </p>
                        @endif
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$set('showDeleteModal', false)">Batal</button>
                        <button type="button" class="btn btn-sm btn-danger" wire:click="delete">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('alert', (event) => {
            const data = event[0] || event;
            alert(data.message);
        });
    });
</script>
