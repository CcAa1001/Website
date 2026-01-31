<div>
    <div class="container-fluid py-4">
        
        {{-- Notification --}}
        @if (session()->has('message'))
            <div class="alert alert-success text-white px-4 py-2 mb-4 role='alert'"><i class="material-icons text-sm me-2">check_circle</i> {{ session('message') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger text-white px-4 py-2 mb-4 role='alert'"><i class="material-icons text-sm me-2">error</i> {{ session('error') }}</div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-10 col-12">
                <div class="card my-4 shadow-lg">
                    
                    {{-- HEADER --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white mb-0">Manajemen Kategori</h6>
                                <p class="text-xs text-white opacity-8 mb-0">Atur menu makanan dan minuman Anda</p>
                            </div>
                            <button wire:click="openCreateModal" class="btn bg-white text-primary mb-0 shadow-sm fw-bold">
                                <i class="material-icons text-sm me-1">add_circle</i> Kategori Baru
                            </button>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-4">
                        
                        {{-- SEARCH BAR --}}
                        <div class="input-group input-group-outline bg-white rounded mb-4 w-100 w-md-50">
                            <label class="form-label">Cari Kategori...</label>
                            <input type="text" class="form-control" wire:model.live.debounce.300ms="search">
                        </div>

                        {{-- CATEGORY LIST (MODERN STYLE) --}}
                        <div class="category-list">
                            @forelse($categories as $parent)
                                {{-- PARENT ITEM CARD --}}
                                <div class="card mb-3 border category-card shadow-none">
                                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                        
                                        {{-- LEFT: Icon + Info --}}
                                        <div class="d-flex align-items-center cursor-pointer flex-grow-1" wire:click="toggleExpand('{{ $parent->id }}')">
                                            
                                            {{-- Chevron Arrow (Rotates if expanded) --}}
                                            <div class="me-3 transition-transform {{ in_array($parent->id, $expanded) ? 'rotate-90' : '' }}">
                                                <i class="material-icons text-secondary text-lg">chevron_right</i>
                                            </div>

                                            {{-- Parent Image/Icon --}}
                                            @if($parent->image_url)
                                                <img src="{{ asset('storage/' . $parent->image_url) }}" class="avatar avatar-sm me-3 border rounded-3 bg-light object-fit-cover">
                                            @else
                                                <div class="avatar avatar-sm me-3 bg-gradient-light border rounded-3 d-flex align-items-center justify-content-center text-dark">
                                                    <i class="material-icons">restaurant</i>
                                                </div>
                                            @endif

                                            {{-- Parent Text --}}
                                            <div>
                                                <h6 class="mb-0 text-dark font-weight-bold">{{ $parent->name }}</h6>
                                                <div class="d-flex align-items-center text-xs text-muted">
                                                    <span class="me-2">{{ $parent->children->count() }} Sub-kategori</span>
                                                    <span class="text-secondary">•</span>
                                                    <span class="ms-2">{{ $parent->products->count() }} Produk Langsung</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- RIGHT: Actions --}}
                                        <div class="d-flex align-items-center gap-2">
                                            {{-- Shortcut Add Child (+) --}}
                                            <button wire:click="addChild('{{ $parent->id }}')" class="btn btn-icon-only btn-rounded btn-outline-success mb-0" title="Tambah Sub-Kategori">
                                                <i class="material-icons text-sm">add</i>
                                            </button>
                                            
                                            {{-- Edit --}}
                                            <button wire:click="edit('{{ $parent->id }}')" class="btn btn-icon-only btn-rounded btn-outline-secondary mb-0">
                                                <i class="material-icons text-sm">edit</i>
                                            </button>

                                            {{-- Delete --}}
                                            <button wire:click="delete('{{ $parent->id }}')" onclick="return confirm('Hapus kategori induk ini?') || event.stopImmediatePropagation()" class="btn btn-icon-only btn-rounded btn-outline-danger mb-0">
                                                <i class="material-icons text-sm">delete</i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- CHILD ITEMS CONTAINER (Collapse) --}}
                                    @if(in_array($parent->id, $expanded))
                                        <div class="bg-gray-100 border-top p-3 ps-5">
                                            @if($parent->children->count() > 0)
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach($parent->children as $child)
                                                        <div class="d-flex align-items-center justify-content-between p-2 bg-white border rounded-3 shadow-sm child-hover">
                                                            <div class="d-flex align-items-center">
                                                                <i class="material-icons text-secondary text-sm me-3 opacity-5">subdirectory_arrow_right</i>
                                                                @if($child->image_url)
                                                                    <img src="{{ asset('storage/' . $child->image_url) }}" class="avatar avatar-xs me-2 border rounded-circle bg-light object-fit-cover">
                                                                @endif
                                                                <div>
                                                                    <span class="text-sm font-weight-bold text-dark d-block">{{ $child->name }}</span>
                                                                    <span class="text-xs text-muted">{{ $child->products->count() }} Produk</span>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex gap-1">
                                                                <button wire:click="edit('{{ $child->id }}')" class="btn btn-link text-secondary p-1 mb-0"><i class="material-icons text-xs">edit</i></button>
                                                                <button wire:click="delete('{{ $child->id }}')" onclick="return confirm('Hapus sub-kategori ini?') || event.stopImmediatePropagation()" class="btn btn-link text-danger p-1 mb-0"><i class="material-icons text-xs">delete</i></button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-3 text-muted text-xs">
                                                    <i class="material-icons text-sm d-block mb-1">playlist_remove</i>
                                                    Belum ada sub-kategori (misal: Nasi Goreng, Nasi Putih). <br>
                                                    <a href="javascript:;" wire:click="addChild('{{ $parent->id }}')" class="fw-bold text-primary cursor-pointer">Tambah sekarang?</a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <div class="icon icon-lg bg-light rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center text-secondary">
                                        <i class="material-icons" style="font-size: 32px;">category</i>
                                    </div>
                                    <h5>Belum ada kategori</h5>
                                    <p class="text-sm text-secondary">Mulai dengan menambahkan kategori utama (Induk).</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="mt-4">
                            {{ $categories->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL FORM --}}
        @if($showModal)
        <div class="fixed-top w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="card shadow-lg m-3 fade-in-down" style="width: 100%; max-width: 500px;">
                <div class="card-header p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $isEditing ? 'Edit Kategori' : 'Kategori Baru' }}</h5>
                    <button wire:click="closeModal" class="btn-close text-dark p-2"><i class="material-icons">close</i></button>
                </div>
                <div class="card-body p-4" style="max-height: 80vh; overflow-y: auto;">
                    <form wire:submit.prevent="save">
                        
                        {{-- Alert jika sedang menambah anak --}}
                        @if(!$isEditing && $parent_id)
                            <div class="alert alert-info text-white text-xs mb-3 px-3 py-2">
                                <i class="material-icons text-xs me-1">info</i> 
                                Menambahkan Sub-kategori untuk: 
                                <strong>{{ $parentOptions->find($parent_id)->name ?? 'Parent' }}</strong>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-bold text-xs text-uppercase text-secondary">Induk (Parent)</label>
                            <div class="input-group input-group-static">
                                <select class="form-control" wire:model="parent_id" {{ (!$isEditing && $parent_id) ? 'disabled' : '' }}>
                                    <option value="">-- Kategori Utama (Level Atas) --</option>
                                    @foreach($parentOptions as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Kategori <span class="text-danger">*</span></label>
                            <div class="input-group input-group-outline">
                                <input type="text" class="form-control" wire:model="name" placeholder="Misal: Makanan, Nasi Goreng, Es Teh">
                            </div>
                            @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <div class="input-group input-group-outline">
                                <textarea class="form-control" wire:model="description" rows="2" placeholder="Deskripsi singkat..."></textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Gambar</label>
                            <div class="d-flex align-items-center gap-3 border p-2 rounded">
                                @if ($image)
                                    <img src="{{ $image->temporaryUrl() }}" class="avatar avatar-lg rounded object-fit-cover">
                                @elseif ($currentImage)
                                    <img src="{{ asset('storage/' . $currentImage) }}" class="avatar avatar-lg rounded object-fit-cover">
                                @else
                                    <div class="avatar avatar-lg bg-gray-100 rounded d-flex align-items-center justify-content-center text-secondary">
                                        <i class="material-icons">image</i>
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <input type="file" class="form-control form-control-sm" wire:model="image" accept="image/*">
                                    <div wire:loading wire:target="image" class="text-xs text-primary mt-1">Mengupload...</div>
                                    @if($currentImage && !$image)
                                        <a href="javascript:;" wire:click="removeImage" class="text-danger text-xs fw-bold mt-1 d-inline-block">Hapus Gambar</a>
                                    @endif
                                </div>
                            </div>
                            @error('image') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="row align-items-center mt-4">
                            <div class="col-6">
                                <div class="form-check form-switch ps-0">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" id="activeSwitch" wire:model="is_active">
                                    <label class="form-check-label text-body fw-bold" for="activeSwitch">Status Aktif</label>
                                </div>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" wire:click="closeModal" class="btn btn-light mb-0 me-2">Batal</button>
                                <button type="submit" class="btn bg-gradient-primary mb-0">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- CSS Khusus --}}
    <style>
        .category-card { transition: all 0.2s ease; border-left: 4px solid transparent; }
        .category-card:hover { border-left-color: #e91e63; background-color: #fff; transform: translateY(-1px); }
        .rotate-90 { transform: rotate(90deg); }
        .transition-transform { transition: transform 0.2s ease; }
        .btn-icon-only { width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; }
        .child-hover:hover { background-color: #f8f9fa !important; border-color: #adb5bd !important; }
        .cursor-pointer { cursor: pointer; }
    </style>
</div>