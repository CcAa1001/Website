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
                            <h6 class="text-white mb-0">Manajemen Produk</h6>
                            <button wire:click="openCreateModal" class="btn bg-white text-primary mb-0 shadow-sm">
                                <i class="material-icons text-sm">add</i> Produk Baru
                            </button>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-2">
                        {{-- Filter Bar --}}
                        <div class="d-flex flex-wrap gap-3 align-items-center mb-4 bg-gray-100 p-3 rounded">
                            <div class="flex-grow-1">
                                <div class="input-group input-group-outline bg-white rounded">
                                    <label class="form-label">Cari Produk...</label>
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
                                        <div class="d-block w-100 bg-light rounded-top" style="height: 180px; overflow: hidden;">
                                            @if($product->image_url)
                                                <img src="{{ asset('storage/' . $product->image_url) }}" class="w-100 h-100" style="object-fit: cover;">
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-secondary">
                                                    <i class="material-icons" style="font-size: 48px;">image</i>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="position-absolute top-0 end-0 m-2 badge {{ $product->is_available ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                            {{ $product->is_available ? 'Stok Ada' : 'Habis' }}
                                        </span>
                                    </div>

                                    <div class="card-body p-3 d-flex flex-column">
                                        <div class="mb-2">
                                            <h6 class="font-weight-bold text-dark mb-0 text-truncate">{{ $product->name }}</h6>
                                            <small class="text-xs text-primary font-weight-bold text-uppercase">{{ $product->category->name ?? '-' }}</small>
                                            <p class="text-xs text-muted mb-0">SKU: {{ $product->sku ?? '-' }}</p>
                                        </div>
                                        
                                        <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                                            <span class="h6 mb-0 text-dark">Rp {{ number_format($product->base_price, 0, ',', '.') }}</span>
                                            
                                            <div class="btn-group">
                                                <button wire:click="edit('{{ $product->id }}')" class="btn btn-link text-dark p-2 mb-0" title="Edit">
                                                    <i class="material-icons text-sm">edit</i>
                                                </button>
                                                <button onclick="confirm('Hapus produk ini?') || event.stopImmediatePropagation()" wire:click="delete('{{ $product->id }}')" class="btn btn-link text-danger p-2 mb-0" title="Hapus">
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

        {{-- Modal Form (Sama seperti sebelumnya) --}}
        @if($showModal)
        <div class="fixed-top w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="card shadow-lg m-3" style="width: 100%; max-width: 700px; max-height: 90vh; display: flex; flex-direction: column;">
                <div class="card-header p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $modalMode === 'create' ? 'Tambah Produk' : 'Edit Produk' }}</h5>
                    <button type="button" class="btn-close p-2 text-dark" wire:click="closeModal">
                        <i class="material-icons">close</i>
                    </button>
                </div>
                <div class="card-body p-4" style="overflow-y: auto;">
                    <form wire:submit.prevent="save">
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <label class="form-label fw-bold d-block">Foto Produk</label>
                                @if ($imageFile)
                                    <img src="{{ $imageFile->temporaryUrl() }}" class="rounded shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                                @elseif ($currentImageUrl)
                                    <img src="{{ asset('storage/' . $currentImageUrl) }}" class="rounded shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded border d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                        <i class="material-icons text-secondary" style="font-size: 40px;">image</i>
                                    </div>
                                @endif
                                <input type="file" wire:model="imageFile" class="form-control mt-2 form-control-sm">
                                <div wire:loading wire:target="imageFile" class="text-xs text-primary mt-1">Uploading...</div>
                            </div>
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-12 mb-3">
                                        <div class="input-group input-group-outline my-1">
                                            <label class="form-label">Nama Produk</label>
                                            <input type="text" class="form-control" wire:model="name">
                                        </div>
                                        @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="input-group input-group-outline my-1">
                                            <label class="form-label">SKU</label>
                                            <input type="text" class="form-control" wire:model="sku">
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="input-group input-group-static my-1">
                                            <label>Kategori</label>
                                            <select class="form-control" wire:model="category_id">
                                                <option value="">Pilih Kategori</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('category_id') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="input-group input-group-outline my-1">
                                            <label class="form-label">Harga Jual</label>
                                            <input type="number" class="form-control" wire:model="base_price">
                                        </div>
                                        @error('base_price') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="input-group input-group-outline my-1">
                                            <label class="form-label">Harga Modal</label>
                                            <input type="number" class="form-control" wire:model="cost_price">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check ps-0">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" wire:model="is_available">
                                            <label class="form-check-label text-body text-sm font-weight-bold">Tersedia untuk dijual</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-light me-2" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn bg-gradient-primary">Simpan</button>
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

</div> {{-- END ROOT ELsEMENT --}}