<div> {{-- ROOT ELEMENT UTAMA (JANGAN DIHAPUS) --}}
    
    <div class="container-fluid py-4">
        {{-- Notifikasi --}}
        @if (session()->has('message'))
            <div class="alert alert-success text-white px-4 py-2 mb-4 role='alert'">
                <i class="material-icons text-sm me-2">check_circle</i> {{ session('message') }}
            </div>
        @endif

        {{-- Header & Controls --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-4">
                            <div>
                                <h6 class="text-white mb-0">Manajemen Produk</h6>
                                <p class="text-xs text-white opacity-8 mb-0">Kelola menu, harga, dan stok.</p>
                            </div>
                            <button wire:click="openCreateModal" class="btn bg-white text-primary mb-0 shadow-sm font-weight-bold">
                                <i class="material-icons text-sm me-1">add</i> Produk Baru
                            </button>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-2">
                        {{-- Filter Bar --}}
                        <div class="d-flex flex-wrap gap-3 align-items-center mb-4 bg-gray-100 p-3 rounded">
                            <div class="flex-grow-1">
                                <div class="input-group input-group-outline bg-white rounded {{ $search ? 'is-filled' : '' }}">
                                    <label class="form-label">Cari Nama / SKU...</label>
                                    <input type="text" class="form-control ps-2" wire:model.live.debounce.300ms="search">
                                </div>
                            </div>
                            <div class="" style="min-width: 200px;">
                                <div class="input-group input-group-static">
                                    <select class="form-control" wire:model.live="filterCategory">
                                        <option value="">Semua Kategori</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- GRID PRODUK --}}
                        <div class="row">
                            @forelse($products as $product)
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                <div class="card h-100 shadow-sm border product-hover-card">
                                    <div class="position-relative">
                                        {{-- Image --}}
                                        <div class="d-block w-100 bg-light rounded-top" style="height: 180px; overflow: hidden;">
                                            @if($product->image_url)
                                                <img src="{{ asset('storage/' . $product->image_url) }}" class="w-100 h-100" style="object-fit: cover;">
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-secondary bg-gray-200">
                                                    <i class="material-icons" style="font-size: 48px;">restaurant_menu</i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Badges --}}
                                        <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1 align-items-end">
                                            @if(!$product->is_available)
                                                <span class="badge bg-gradient-secondary shadow-sm">Non-Aktif</span>
                                            @elseif($product->stock <= $product->min_stock)
                                                <span class="badge bg-gradient-danger shadow-sm">Stok Menipis: {{ $product->stock }}</span>
                                            @endif
                                            
                                            @if($product->is_featured)
                                                <span class="badge bg-gradient-warning shadow-sm"><i class="material-icons text-xxs me-1">star</i> Favorit</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="card-body p-3 d-flex flex-column">
                                        <div class="mb-2">
                                            <h6 class="font-weight-bold text-dark mb-0 text-truncate" title="{{ $product->name }}">{{ $product->name }}</h6>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-xs text-primary font-weight-bold text-uppercase">{{ $product->category->name ?? 'Uncategorized' }}</small>
                                                <small class="text-xxs text-muted">{{ $product->sku ?? '-' }}</small>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="h6 mb-0 text-dark">Rp {{ number_format($product->base_price, 0, ',', '.') }}</span>
                                                @if(auth()->user()->role->slug === 'super_admin' && $product->cost_price > 0)
                                                    <div class="text-xxs text-muted">HPP: {{ number_format($product->cost_price, 0, ',', '.') }}</div>
                                                @endif
                                            </div>
                                            
                                            <div class="btn-group">
                                                <button wire:click="edit('{{ $product->id }}')" class="btn btn-icon-only btn-rounded btn-outline-secondary mb-0 btn-sm">
                                                    <i class="material-icons text-sm">edit</i>
                                                </button>
                                                <button onclick="confirm('Hapus produk ini?') || event.stopImmediatePropagation()" wire:click="delete('{{ $product->id }}')" class="btn btn-icon-only btn-rounded btn-outline-danger mb-0 btn-sm">
                                                    <i class="material-icons text-sm">delete</i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-5">
                                <i class="material-icons text-secondary opacity-3" style="font-size: 64px;">inventory_2</i>
                                <h5 class="mt-3 text-secondary">Tidak ada produk ditemukan.</h5>
                                <button wire:click="openCreateModal" class="btn btn-outline-primary mt-2">Tambah Produk Baru</button>
                            </div>
                            @endforelse
                        </div>

                        <div class="mt-4">
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL FORM --}}
        @if($showModal)
        <div class="fixed-top w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="card shadow-lg m-3" style="width: 100%; max-width: 800px; max-height: 90vh; display: flex; flex-direction: column;">
                
                {{-- Header --}}
                <div class="card-header p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $modalMode === 'create' ? 'Tambah Produk Baru' : 'Edit Produk' }}</h5>
                    <button type="button" class="btn-close p-2 text-dark" wire:click="closeModal">
                        <i class="material-icons">close</i>
                    </button>
                </div>

                {{-- Body (Scrollable) --}}
                <div class="card-body p-4" style="overflow-y: auto;">
                    <form wire:submit.prevent="save">
                        <div class="row">
                            
                            {{-- KOLOM KIRI (DATA UTAMA) --}}
                            <div class="col-md-7 border-end">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Informasi Produk</h6>
                                
                                <div class="row g-3">
                                    {{-- Nama Produk --}}
                                    <div class="col-12">
                                        {{-- [FIX] Logic is-filled agar label naik --}}
                                        <div class="input-group input-group-outline {{ $name ? 'is-filled' : '' }}">
                                            <label class="form-label">Nama Produk *</label>
                                            <input type="text" class="form-control" wire:model="name">
                                        </div>
                                        @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Kategori --}}
                                    <div class="col-md-6">
                                        <div class="input-group input-group-static">
                                            <label>Kategori *</label>
                                            <select class="form-control" wire:model="category_id">
                                                <option value="">Pilih Kategori...</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('category_id') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- SKU --}}
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline {{ $sku ? 'is-filled' : '' }}">
                                            <label class="form-label">Kode / SKU</label>
                                            <input type="text" class="form-control" wire:model="sku">
                                        </div>
                                        @error('sku') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Harga --}}
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline {{ $base_price || $base_price === 0 || $base_price === '0' ? 'is-filled' : '' }}">
                                            <label class="form-label">Harga Jual (Rp) *</label>
                                            <input type="number" class="form-control" wire:model="base_price">
                                        </div>
                                        @error('base_price') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline {{ $cost_price || $cost_price === 0 || $cost_price === '0' ? 'is-filled' : '' }}">
                                            <label class="form-label">Harga Modal (HPP)</label>
                                            <input type="number" class="form-control" wire:model="cost_price">
                                        </div>
                                        <small class="text-xs text-muted">Hanya dilihat admin.</small>
                                    </div>

                                    {{-- Deskripsi --}}
                                    <div class="col-12 mt-2">
                                        <div class="input-group input-group-outline {{ $description ? 'is-filled' : '' }}">
                                            <label class="form-label">Deskripsi Singkat</label>
                                            <textarea class="form-control" wire:model="description" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- KOLOM KANAN (MEDIA & STOK) --}}
                            <div class="col-md-5 ps-md-4">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Media & Stok</h6>

                                {{-- Upload Gambar --}}
                                <div class="mb-4 text-center">
                                    <div class="border rounded p-2 bg-gray-100 position-relative mb-2">
                                        @if ($imageFile)
                                            <img src="{{ $imageFile->temporaryUrl() }}" class="img-fluid rounded object-fit-cover" style="height: 150px; width: 100%;">
                                        @elseif ($currentImageUrl)
                                            <img src="{{ asset('storage/' . $currentImageUrl) }}" class="img-fluid rounded object-fit-cover" style="height: 150px; width: 100%;">
                                        @else
                                            <div class="bg-white rounded d-flex align-items-center justify-content-center text-secondary" style="height: 150px;">
                                                <div class="text-center">
                                                    <i class="material-icons opacity-3" style="font-size: 40px;">add_photo_alternate</i>
                                                    <p class="text-xs mb-0">Upload Foto</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($currentImageUrl && !$imageFile)
                                            <button type="button" wire:click="removeImage" class="btn btn-sm btn-icon btn-danger position-absolute top-0 end-0 m-2 shadow-sm" title="Hapus Foto">
                                                <i class="material-icons text-xs">close</i>
                                            </button>
                                        @endif
                                    </div>

                                    <input type="file" id="prodImage" class="d-none" wire:model="imageFile" accept="image/*">
                                    <label for="prodImage" class="btn btn-sm btn-outline-primary w-100">Pilih Foto</label>
                                    <div wire:loading wire:target="imageFile" class="text-xs text-primary">Mengupload...</div>
                                    @error('imageFile') <span class="text-danger text-xs d-block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Stok Management --}}
                                <div class="p-3 border rounded bg-white mb-3">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="input-group input-group-outline {{ $stock || $stock === 0 || $stock === '0' ? 'is-filled' : '' }}">
                                                <label class="form-label">Stok Saat Ini</label>
                                                <input type="number" class="form-control" wire:model="stock">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group input-group-outline {{ $min_stock || $min_stock === 0 || $min_stock === '0' ? 'is-filled' : '' }}">
                                                <label class="form-label">Min. Stok</label>
                                                <input type="number" class="form-control" wire:model="min_stock">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Toggles --}}
                                <div class="form-check form-switch ps-0 mb-2">
                                    <input class="form-check-input ms-auto" type="checkbox" id="availSwitch" wire:model="is_available">
                                    <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="availSwitch">Tersedia untuk Dijual</label>
                                </div>
                                <div class="form-check form-switch ps-0">
                                    <input class="form-check-input ms-auto" type="checkbox" id="featSwitch" wire:model="is_featured">
                                    <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="featSwitch">Produk Unggulan (Favorit)</label>
                                </div>
                            </div>

                        </div>

                        <div class="text-end border-top pt-3 mt-4">
                            <button type="button" wire:click="closeModal" class="btn btn-light me-2">Batal</button>
                            <button type="submit" class="btn bg-gradient-primary">Simpan Produk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- Style Khusus Component Ini --}}
    <style>
        .product-hover-card { transition: transform 0.2s; }
        .product-hover-card:hover { transform: translateY(-5px); }
    </style>

</div>