<div class="container-fluid py-4">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="material-icons text-sm me-2">check_circle</i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="material-icons text-sm me-2">error</i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Advanced Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" 
                           placeholder="🔍 Cari produk, SKU..." 
                           wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-2">
                    <select class="form-control form-control-sm" wire:model.live="filterCategory">
                        <option value="">📂 Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control form-control-sm" wire:model.live="filterStatus">
                        <option value="all">Semua Status</option>
                        <option value="available">✅ Tersedia</option>
                        <option value="unavailable">❌ Habis</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control form-control-sm" wire:model.live="filterFeatured">
                        <option value="all">Semua Produk</option>
                        <option value="featured">⭐ Featured</option>
                        <option value="regular">Regular</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control form-control-sm" wire:model.live="sortBy">
                        <option value="default">Urutan Default</option>
                        <option value="name_asc">A-Z</option>
                        <option value="name_desc">Z-A</option>
                        <option value="price_low">Harga Terendah</option>
                        <option value="price_high">Harga Tertinggi</option>
                        <option value="newest">Terbaru</option>
                    </select>
                </div>
                <div class="col-md-1">
                    @if($search || $filterCategory || $filterStatus != 'all' || $filterFeatured != 'all' || $sortBy != 'default')
                        <button wire:click="clearFilters" class="btn btn-sm btn-outline-secondary w-100" title="Clear Filters">
                            <i class="material-icons text-xs">clear</i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- FORM SECTION --}}
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 80px;">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ $this->formTitle }}</h6>
                    @if($isEditing)
                        <span class="badge bg-gradient-info">Editing</span>
                    @endif
                </div>
                <div class="card-body" style="max-height: 80vh; overflow-y: auto;">
                    <form wire:submit.prevent="save">
                        {{-- Product Name --}}
                        <div class="input-group input-group-outline mb-3 {{ $name ? 'is-filled' : '' }}">
                            <label class="form-label">Nama Produk *</label>
                            <input type="text" class="form-control" wire:model="name" required>
                        </div>
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror

                        {{-- SKU & Category Row --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group input-group-outline mb-3 {{ $sku ? 'is-filled' : '' }}">
                                    <label class="form-label">SKU</label>
                                    <input type="text" class="form-control" wire:model="sku">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <select class="form-control" wire:model="category_id" required>
                                        <option value="">Pilih Kategori *</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="input-group input-group-outline mb-3 {{ $description ? 'is-filled' : '' }}">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" wire:model="description" rows="2"></textarea>
                        </div>

                        {{-- Price Row --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group input-group-outline mb-3 is-filled">
                                    <label class="form-label">Harga Jual *</label>
                                    <input type="number" class="form-control" wire:model="base_price" step="0.01" required>
                                </div>
                                @error('base_price') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-outline mb-3 {{ $cost_price ? 'is-filled' : '' }}">
                                    <label class="form-label">Harga Modal</label>
                                    <input type="number" class="form-control" wire:model="cost_price" step="0.01">
                                </div>
                            </div>
                        </div>

                        {{-- Image Section --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Gambar Produk</label>
                            
                            {{-- Current Image --}}
                            @if ($currentImageUrl)
                                <div class="mb-2 position-relative d-inline-block">
                                    <img src="{{ filter_var($currentImageUrl, FILTER_VALIDATE_URL) ? $currentImageUrl : asset('storage/' . $currentImageUrl) }}" 
                                         class="img-thumbnail" style="max-height: 120px;">
                                    <button type="button" wire:click="removeImage" 
                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1">
                                        <i class="material-icons text-xs">close</i>
                                    </button>
                                </div>
                            @endif

                            {{-- New Image Preview --}}
                            @if ($imageFile)
                                <div class="mb-2">
                                    <img src="{{ $imageFile->temporaryUrl() }}" class="img-thumbnail" style="max-height: 120px;">
                                </div>
                            @endif

                            {{-- Upload or URL Tabs --}}
                            <ul class="nav nav-tabs nav-tabs-sm mb-2" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#upload-tab">
                                        <i class="material-icons text-xs">upload</i> Upload
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#url-tab">
                                        <i class="material-icons text-xs">link</i> URL
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                {{-- Upload Tab --}}
                                <div class="tab-pane fade show active" id="upload-tab">
                                    <input type="file" class="form-control form-control-sm" 
                                           wire:model="imageFile" accept="image/*">
                                    <div wire:loading wire:target="imageFile" class="text-primary text-xs mt-1">
                                        Uploading...
                                    </div>
                                    @error('imageFile') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- URL Tab --}}
                                <div class="tab-pane fade" id="url-tab">
                                    <input type="url" class="form-control form-control-sm" 
                                           wire:model="image_url" placeholder="https://example.com/image.jpg">
                                    @error('image_url') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group input-group-outline mb-3 is-filled">
                                    <label class="form-label">Waktu (min)</label>
                                    <input type="number" class="form-control" wire:model="preparation_time">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-outline mb-3 {{ $calories ? 'is-filled' : '' }}">
                                    <label class="form-label">Kalori</label>
                                    <input type="number" class="form-control" wire:model="calories">
                                </div>
                            </div>
                        </div>

                        {{-- Tags & Allergens --}}
                        <div class="input-group input-group-outline mb-3 {{ $tags ? 'is-filled' : '' }}">
                            <label class="form-label">Tags (pisahkan dengan koma)</label>
                            <input type="text" class="form-control" wire:model="tags" placeholder="vegan, spicy, halal">
                        </div>

                        <div class="input-group input-group-outline mb-3 {{ $allergens ? 'is-filled' : '' }}">
                            <label class="form-label">Allergens (pisahkan dengan koma)</label>
                            <input type="text" class="form-control" wire:model="allergens" placeholder="peanuts, dairy, eggs">
                        </div>

                        {{-- Sort Order --}}
                        <div class="input-group input-group-outline mb-3 is-filled">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" class="form-control" wire:model="sort_order">
                        </div>

                        {{-- Toggles --}}
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" wire:model="is_available" id="is_available">
                            <label class="form-check-label" for="is_available">
                                <i class="material-icons text-xs">{{ $is_available ? 'check_circle' : 'cancel' }}</i>
                                Tersedia
                            </label>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" wire:model="is_featured" id="is_featured">
                            <label class="form-check-label" for="is_featured">
                                <i class="material-icons text-xs">star</i> Produk Unggulan
                            </label>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" wire:model="is_taxable" id="is_taxable">
                            <label class="form-check-label" for="is_taxable">Kena Pajak</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" wire:model="tax_inclusive" id="tax_inclusive">
                            <label class="form-check-label" for="tax_inclusive">Harga Sudah Termasuk Pajak</label>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn bg-gradient-primary flex-grow-1">
                                <i class="material-icons text-sm">save</i> 
                                {{ $isEditing ? 'Update' : 'Simpan' }}
                            </button>
                            @if($isEditing)
                                <button type="button" wire:click="cancelEdit" class="btn btn-outline-secondary">
                                    <i class="material-icons text-sm">close</i>
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- PRODUCTS GRID --}}
        <div class="col-lg-8">
            {{-- Results Info --}}
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Menampilkan {{ $products->count() }} dari {{ $products->total() }} produk
                </small>
            </div>

            <div class="row">
                @forelse($products as $product)
                    <div class="col-md-6 col-xl-4 mb-4" wire:key="product-{{ $product->id }}">
                        <div class="card h-100 {{ $product->is_featured ? 'border-warning' : '' }}">
                            {{-- Image --}}
                            <div class="position-relative">
                                @if($product->image_url)
                                    <img src="{{ filter_var($product->image_url, FILTER_VALIDATE_URL) ? $product->image_url : asset('storage/' . $product->image_url) }}" 
                                         class="card-img-top" 
                                         style="height: 180px; object-fit: cover;"
                                         alt="{{ $product->name }}">
                                @else
                                    <div class="bg-gradient-secondary d-flex align-items-center justify-content-center" style="height: 180px;">
                                        <i class="material-icons text-white" style="font-size: 48px;">image</i>
                                    </div>
                                @endif

                                {{-- Badges --}}
                                <div class="position-absolute top-0 start-0 m-2">
                                    @if($product->is_featured)
                                        <span class="badge bg-gradient-warning">
                                            <i class="material-icons text-xs">star</i> Featured
                                        </span>
                                    @endif
                                </div>
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge {{ $product->is_available ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                        {{ $product->is_available ? 'Ready' : 'Sold Out' }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body pb-2">
                                {{-- Category --}}
                                @if($product->category)
                                    <span class="badge badge-sm bg-gradient-info mb-1">{{ $product->category->name }}</span>
                                @endif

                                {{-- Name --}}
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                
                                {{-- SKU --}}
                                @if($product->sku)
                                    <small class="text-muted d-block mb-2">SKU: {{ $product->sku }}</small>
                                @endif

                                {{-- Description --}}
                                @if($product->description)
                                    <p class="text-xs text-secondary mb-2">
                                        {{ Str::limit($product->description, 80) }}
                                    </p>
                                @endif

                                {{-- Price --}}
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="text-primary mb-0">
                                        {{ $product->formatted_price }}
                                    </h5>
                                    @if($product->cost_price)
                                        <small class="text-muted">
                                            Modal: Rp {{ number_format($product->cost_price, 0, ',', '.') }}
                                        </small>
                                    @endif
                                </div>

                                {{-- Tags --}}
                                @if($product->tags && count($product->tags) > 0)
                                    <div class="mb-2">
                                        @foreach(array_slice($product->tags, 0, 3) as $tag)
                                            <span class="badge badge-sm bg-gradient-secondary">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="card-footer pt-0 border-0">
                                <div class="btn-group w-100" role="group">
                                    <button wire:click="edit('{{ $product->id }}')" 
                                            class="btn btn-sm btn-outline-primary">
                                        <i class="material-icons text-xs">edit</i>
                                    </button>
                                    
                                    <button wire:click="toggleStatus('{{ $product->id }}')" 
                                            class="btn btn-sm {{ $product->is_available ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                        <i class="material-icons text-xs">
                                            {{ $product->is_available ? 'visibility_off' : 'visibility' }}
                                        </i>
                                    </button>

                                    <button wire:click="toggleFeatured('{{ $product->id }}')" 
                                            class="btn btn-sm {{ $product->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}">
                                        <i class="material-icons text-xs">star</i>
                                    </button>

                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            <i class="material-icons text-xs">more_vert</i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click.prevent="duplicate('{{ $product->id }}')">
                                                    <i class="material-icons text-sm me-2">content_copy</i> Duplikasi
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" 
                                                   wire:click.prevent="delete('{{ $product->id }}')"
                                                   onclick="return confirm('Yakin hapus {{ $product->name }}?')">
                                                    <i class="material-icons text-sm me-2">delete</i> Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="material-icons text-secondary" style="font-size: 64px;">inventory_2</i>
                                <h5 class="text-secondary mt-3">Belum Ada Produk</h5>
                                <p class="text-sm">Tambahkan produk pertama menggunakan form di sebelah kiri</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        });
    }, 3000);

    // Scroll to form when editing
    Livewire.on('productLoaded', () => {
        document.querySelector('.sticky-top').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
</script>
@endpush