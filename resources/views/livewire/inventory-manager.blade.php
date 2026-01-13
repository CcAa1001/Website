<div class="container-fluid py-4">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="material-icons">check_circle</i></span>
            <span class="alert-text">{{ session('message') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="material-icons">error</i></span>
            <span class="alert-text">{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filters Bar --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="input-group input-group-outline is-filled">
                                <input type="text" class="form-control" placeholder="Cari produk..." wire:model.live="search">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" wire:model.live="filterCategory">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" wire:model.live="filterStatus">
                                <option value="all">Semua Status</option>
                                <option value="available">Tersedia</option>
                                <option value="unavailable">Tidak Tersedia</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            @if($search || $filterCategory || $filterStatus !== 'all')
                                <button wire:click="clearFilters" class="btn btn-sm btn-outline-secondary">
                                    <i class="material-icons text-sm">clear</i> Clear
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Form Section --}}
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header pb-0">
                    <h5 class="font-weight-bolder">{{ $this->formTitle }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        {{-- Product Name --}}
                        <div class="input-group input-group-outline mb-3 {{ $name ? 'is-filled' : '' }}">
                            <label class="form-label">Nama Produk *</label>
                            <input type="text" class="form-control" wire:model="name">
                        </div>
                        @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        {{-- SKU --}}
                        <div class="input-group input-group-outline mb-3 {{ $sku ? 'is-filled' : '' }}">
                            <label class="form-label">SKU (Opsional)</label>
                            <input type="text" class="form-control" wire:model="sku">
                        </div>
                        @error('sku') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        {{-- Category --}}
                        <div class="input-group input-group-outline mb-3 {{ $category_id ? 'is-filled' : '' }}">
                            <select class="form-control" wire:model="category_id">
                                <option value="">Pilih Kategori *</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('category_id') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        {{-- Description --}}
                        <div class="input-group input-group-outline mb-3 {{ $description ? 'is-filled' : '' }}">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" wire:model="description" rows="3"></textarea>
                        </div>

                        {{-- Price Row --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group input-group-outline mb-3 is-filled">
                                    <label class="form-label">Harga Jual *</label>
                                    <input type="number" class="form-control" wire:model="base_price" step="0.01">
                                </div>
                                @error('base_price') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-outline mb-3 {{ $cost_price ? 'is-filled' : '' }}">
                                    <label class="form-label">Harga Modal</label>
                                    <input type="number" class="form-control" wire:model="cost_price" step="0.01">
                                </div>
                            </div>
                        </div>

                        {{-- Image Upload --}}
                        <div class="mb-3">
                            <label class="form-label">Gambar Produk</label>
                            
                            {{-- Current Image --}}
                            @if ($currentImage && !$image)
                                <div class="mb-2 position-relative d-inline-block">
                                    <img src="{{ asset('storage/' . $currentImage) }}" class="img-thumbnail" style="max-height: 150px;">
                                    <button type="button" wire:click="removeImage" 
                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1">
                                        <i class="material-icons text-xs">close</i>
                                    </button>
                                </div>
                            @endif

                            {{-- New Image Preview --}}
                            @if ($image)
                                <div class="mb-2">
                                    <img src="{{ $image->temporaryUrl() }}" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            @endif

                            <input type="file" class="form-control border p-2" wire:model="image" accept="image/*">
                            <div wire:loading wire:target="image" class="text-primary text-xs mt-1">Mengupload...</div>
                        </div>
                        @error('image') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        {{-- Additional Settings --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group input-group-outline mb-3 is-filled">
                                    <label class="form-label">Waktu Persiapan (menit)</label>
                                    <input type="number" class="form-control" wire:model="preparation_time" min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-outline mb-3 {{ $calories ? 'is-filled' : '' }}">
                                    <label class="form-label">Kalori</label>
                                    <input type="number" class="form-control" wire:model="calories" min="0">
                                </div>
                            </div>
                        </div>

                        {{-- Sort Order --}}
                        <div class="input-group input-group-outline mb-3 is-filled">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" class="form-control" wire:model="sort_order" min="0">
                        </div>

                        {{-- Toggles --}}
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="is_available" wire:model="is_available">
                            <label class="form-check-label" for="is_available">Tersedia (Ready Stock)</label>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="is_featured" wire:model="is_featured">
                            <label class="form-check-label" for="is_featured">Produk Unggulan</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_taxable" wire:model="is_taxable">
                            <label class="form-check-label" for="is_taxable">Kena Pajak</label>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn bg-gradient-primary flex-grow-1">
                                <i class="material-icons text-sm">save</i> {{ $isEditing ? 'Update' : 'Simpan' }}
                            </button>
                            @if($isEditing)
                                <button type="button" wire:click="cancelEdit" class="btn btn-outline-secondary">
                                    <i class="material-icons text-sm">close</i> Batal
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Products Grid Section --}}
        <div class="col-lg-8">
            <div class="row">
                @forelse($products as $product)
                    <div class="col-md-6 col-xl-4 mb-4" wire:key="product-{{ $product->id }}">
                        <div class="card h-100">
                            {{-- Product Image --}}
                            <div class="position-relative">
                                <img src="{{ $product->image_url ? asset('storage/'.$product->image_url) : asset('assets/img/products/product-1-min.jpg') }}" 
                                     class="card-img-top" 
                                     style="height: 200px; object-fit: cover;"
                                     alt="{{ $product->name }}">
                                
                                {{-- Featured Badge --}}
                                @if($product->is_featured)
                                    <span class="badge bg-gradient-warning position-absolute top-0 end-0 m-2">
                                        <i class="material-icons text-xs">star</i> Featured
                                    </span>
                                @endif

                                {{-- Status Badge --}}
                                <span class="badge {{ $product->is_available ? 'bg-gradient-success' : 'bg-gradient-secondary' }} position-absolute top-0 start-0 m-2">
                                    {{ $product->is_available ? 'Tersedia' : 'Habis' }}
                                </span>
                            </div>

                            <div class="card-body pb-2">
                                {{-- Category --}}
                                @if($product->category)
                                    <span class="badge badge-sm bg-gradient-info mb-2">{{ $product->category->name }}</span>
                                @endif

                                {{-- Product Name --}}
                                <h6 class="mb-2">{{ $product->name }}</h6>

                                {{-- Description --}}
                                @if($product->description)
                                    <p class="text-xs text-secondary mb-2">{{ Str::limit($product->description, 60) }}</p>
                                @endif

                                {{-- Price --}}
                                <h5 class="text-primary mb-0">
                                    Rp {{ number_format($product->base_price, 0, ',', '.') }}
                                </h5>

                                {{-- SKU --}}
                                @if($product->sku)
                                    <small class="text-muted">SKU: {{ $product->sku }}</small>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="card-footer pt-0 border-0">
                                <div class="d-flex gap-2">
                                    <button wire:click="edit('{{ $product->id }}')" 
                                            class="btn btn-sm btn-outline-primary flex-grow-1"
                                            title="Edit">
                                        <i class="material-icons text-sm">edit</i> Edit
                                    </button>
                                    
                                    <button wire:click="toggleStatus('{{ $product->id }}')" 
                                            class="btn btn-sm {{ $product->is_available ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            title="{{ $product->is_available ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="material-icons text-sm">
                                            {{ $product->is_available ? 'visibility_off' : 'visibility' }}
                                        </i>
                                    </button>

                                    <button wire:click="toggleFeatured('{{ $product->id }}')" 
                                            class="btn btn-sm {{ $product->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}"
                                            title="Featured">
                                        <i class="material-icons text-sm">star</i>
                                    </button>

                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button" 
                                                data-bs-toggle="dropdown">
                                            <i class="material-icons text-sm">more_vert</i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click.prevent="duplicate('{{ $product->id }}')">
                                                    <i class="material-icons text-sm me-2">content_copy</i> Duplikat
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" 
                                                   href="#" 
                                                   wire:click.prevent="delete('{{ $product->id }}')"
                                                   onclick="return confirm('Yakin ingin menghapus produk ini?')">
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
                                <i class="material-icons text-secondary" style="font-size: 48px;">inventory_2</i>
                                <h6 class="text-secondary mt-3">Belum ada produk</h6>
                                <p class="text-sm text-secondary">Tambahkan produk pertama Anda menggunakan form di sebelah kiri</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="row mt-3">
                <div class="col-12">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    // Auto-dismiss alerts
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 3000);
    });
</script>
@endpush