<div>
<div class="product-manager-modern">
    {{-- Header Section --}}
    <div class="pm-header">
        <div class="pm-header-left">
            <h2 class="pm-title">
                <i class="fas fa-box"></i>
                Manajemen Produk
            </h2>
            <div class="pm-stats">
                <span class="stat-badge stat-total">
                    <i class="fas fa-boxes"></i>
                    Total: {{ $stats['total'] }}
                </span>
                <span class="stat-badge stat-available">
                    <i class="fas fa-check-circle"></i>
                    Tersedia: {{ $stats['available'] }}
                </span>
                <span class="stat-badge stat-unavailable">
                    <i class="fas fa-times-circle"></i>
                    Tidak Tersedia: {{ $stats['unavailable'] }}
                </span>
                <span class="stat-badge stat-featured">
                    <i class="fas fa-star"></i>
                    Featured: {{ $stats['featured'] }}
                </span>
            </div>
        </div>
        <div class="pm-header-right">
            <button wire:click="openCreateModal" class="btn btn-primary btn-create">
                <i class="fas fa-plus"></i>
                Tambah Produk
            </button>
        </div>
    </div>

    {{-- Filters & Controls --}}
    <div class="pm-controls">
        <div class="pm-controls-left">
            {{-- Search --}}
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari produk, SKU..."
                    class="search-input"
                >
                @if($search)
                    <button wire:click="$set('search', '')" class="search-clear">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
            </div>

            {{-- Category Filter --}}
            <select wire:model.live="filterCategory" class="filter-select">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            {{-- Status Filter --}}
            <select wire:model.live="filterStatus" class="filter-select">
                <option value="all">Semua Status</option>
                <option value="available">Tersedia</option>
                <option value="unavailable">Tidak Tersedia</option>
            </select>

            {{-- Featured Filter --}}
            <select wire:model.live="filterFeatured" class="filter-select">
                <option value="all">Semua Produk</option>
                <option value="featured">Featured</option>
                <option value="regular">Regular</option>
            </select>

            {{-- Sort --}}
            <select wire:model.live="sortBy" class="filter-select">
                <option value="default">Urutan Default</option>
                <option value="name_asc">Nama A-Z</option>
                <option value="name_desc">Nama Z-A</option>
                <option value="price_low">Harga Terendah</option>
                <option value="price_high">Harga Tertinggi</option>
                <option value="newest">Terbaru</option>
            </select>

            @if($search || $filterCategory || $filterStatus !== 'all' || $filterFeatured !== 'all' || $sortBy !== 'default')
                <button wire:click="clearFilters" class="btn btn-clear-filters">
                    <i class="fas fa-filter-circle-xmark"></i>
                    Clear Filters
                </button>
            @endif
        </div>

        <div class="pm-controls-right">
            {{-- View Toggle --}}
            <div class="view-toggle">
                <button 
                    wire:click="setViewMode('grid')" 
                    class="view-btn {{ $viewMode === 'grid' ? 'active' : '' }}"
                    title="Grid View"
                >
                    <i class="fas fa-grid"></i>
                </button>
                <button 
                    wire:click="setViewMode('table')" 
                    class="view-btn {{ $viewMode === 'table' ? 'active' : '' }}"
                    title="Table View"
                >
                    <i class="fas fa-table"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Bulk Actions Bar --}}
    @if(count($selectedProducts) > 0)
        <div class="bulk-actions-bar">
            <div class="bulk-info">
                <i class="fas fa-check-square"></i>
                <span>{{ count($selectedProducts) }} produk dipilih</span>
            </div>
            <div class="bulk-buttons">
                <button wire:click="bulkActivate" class="btn btn-sm btn-success">
                    <i class="fas fa-check"></i>
                    Aktifkan
                </button>
                <button wire:click="bulkDeactivate" class="btn btn-sm btn-warning">
                    <i class="fas fa-ban"></i>
                    Nonaktifkan
                </button>
                <button 
                    wire:click="bulkDelete" 
                    onclick="return confirm('Yakin ingin menghapus {{ count($selectedProducts) }} produk?')"
                    class="btn btn-sm btn-danger"
                >
                    <i class="fas fa-trash"></i>
                    Hapus
                </button>
            </div>
        </div>
    @endif

    {{-- Grid View --}}
    @if($viewMode === 'grid')
        <div class="products-grid">
            @forelse($products as $product)
                <div class="product-card" wire:key="product-{{ $product->id }}">
                    {{-- Checkbox --}}
                    <div class="card-checkbox">
                        <input 
                            type="checkbox" 
                            wire:model.live="selectedProducts" 
                            value="{{ $product->id }}"
                            id="check-{{ $product->id }}"
                        >
                    </div>

                    {{-- Image --}}
                    <div class="card-image">
                        <img 
                            src="{{ $product->medium_image }}" 
                            alt="{{ $product->name }}"
                            loading="lazy"
                        >
                        @if($product->is_featured)
                            <span class="badge-featured">
                                <i class="fas fa-star"></i>
                                Featured
                            </span>
                        @endif
                        @if(!$product->is_available)
                            <div class="overlay-unavailable">
                                <i class="fas fa-ban"></i>
                                Tidak Tersedia
                            </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="card-content">
                        <div class="card-header">
                            <h3 class="card-title">{{ $product->name }}</h3>
                            <div class="card-badges">
                                @if($product->sku)
                                    <span class="badge-sku">{{ $product->sku }}</span>
                                @endif
                                <span class="badge-category">{{ $product->category->name }}</span>
                            </div>
                        </div>

                        @if($product->description)
                            <p class="card-description">{{ Str::limit($product->description, 80) }}</p>
                        @endif

                        <div class="card-footer">
                            <div class="card-price">
                                <span class="price-label">Harga:</span>
                                <span class="price-value">{{ $product->formatted_price }}</span>
                            </div>
                            
                            <div class="card-actions">
                                <button 
                                    wire:click="toggleAvailability({{ $product->id }})"
                                    class="btn-action {{ $product->is_available ? 'btn-success' : 'btn-secondary' }}"
                                    title="{{ $product->is_available ? 'Nonaktifkan' : 'Aktifkan' }}"
                                >
                                    <i class="fas fa-{{ $product->is_available ? 'check' : 'times' }}-circle"></i>
                                </button>
                                
                                <button 
                                    wire:click="toggleFeatured({{ $product->id }})"
                                    class="btn-action {{ $product->is_featured ? 'btn-warning' : 'btn-secondary' }}"
                                    title="{{ $product->is_featured ? 'Unfeature' : 'Set Featured' }}"
                                >
                                    <i class="fas fa-star"></i>
                                </button>
                                
                                <button 
                                    wire:click="openEditModal('{{ $product->id }}')"
                                    class="btn-action btn-primary"
                                    title="Edit"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <button 
                                    wire:click="duplicate('{{ $product->id }}')"
                                    class="btn-action btn-info"
                                    title="Duplicate"
                                >
                                    <i class="fas fa-copy"></i>
                                </button>
                                
                                <button 
                                    wire:click="delete('{{ $product->id }}')"
                                    onclick="return confirm('Yakin ingin menghapus {{ $product->name }}?')"
                                    class="btn-action btn-danger"
                                    title="Hapus"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>Tidak ada produk</h3>
                    <p>Belum ada produk yang tersedia</p>
                    <button wire:click="openCreateModal" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Produk Pertama
                    </button>
                </div>
            @endforelse
        </div>
    @endif

    {{-- Table View --}}
    @if($viewMode === 'table')
        <div class="products-table-wrapper">
            <table class="products-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input 
                                type="checkbox" 
                                wire:model.live="selectAll"
                            >
                        </th>
                        <th style="width: 80px;">Gambar</th>
                        <th>Nama Produk</th>
                        <th>SKU</th>
                        <th>Kategori</th>
                        <th style="text-align: right;">Harga</th>
                        <th style="width: 100px; text-align: center;">Status</th>
                        <th style="width: 200px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr wire:key="product-table-{{ $product->id }}" class="{{ !$product->is_available ? 'row-unavailable' : '' }}">
                            <td>
                                <input 
                                    type="checkbox" 
                                    wire:model.live="selectedProducts" 
                                    value="{{ $product->id }}"
                                >
                            </td>
                            <td>
                                <div class="table-image">
                                    <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}">
                                    @if($product->is_featured)
                                        <i class="fas fa-star icon-featured" title="Featured"></i>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="product-name">
                                    {{ $product->name }}
                                    @if(!$product->is_available)
                                        <span class="badge-unavailable">Tidak Tersedia</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="text-muted">{{ $product->sku ?: '-' }}</span>
                            </td>
                            <td>
                                <span class="badge-category-table">{{ $product->category->name }}</span>
                            </td>
                            <td style="text-align: right;">
                                <strong>{{ $product->formatted_price }}</strong>
                            </td>
                            <td style="text-align: center;">
                                <button 
                                    wire:click="toggleAvailability({{ $product->id }})"
                                    class="toggle-status {{ $product->is_available ? 'status-active' : 'status-inactive' }}"
                                >
                                    <i class="fas fa-{{ $product->is_available ? 'check' : 'times' }}-circle"></i>
                                    {{ $product->is_available ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button 
                                        wire:click="toggleFeatured({{ $product->id }})"
                                        class="btn-table-action {{ $product->is_featured ? 'text-warning' : '' }}"
                                        title="Featured"
                                    >
                                        <i class="fas fa-star"></i>
                                    </button>
                                    
                                    <button 
                                        wire:click="openEditModal('{{ $product->id }}')"
                                        class="btn-table-action text-primary"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button 
                                        wire:click="duplicate('{{ $product->id }}')"
                                        class="btn-table-action text-info"
                                        title="Duplicate"
                                    >
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    
                                    <button 
                                        wire:click="delete('{{ $product->id }}')"
                                        onclick="return confirm('Yakin ingin menghapus?')"
                                        class="btn-table-action text-danger"
                                        title="Hapus"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state-table">
                                    <i class="fas fa-box-open"></i>
                                    <p>Tidak ada produk yang ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- Pagination --}}
    <div class="pm-pagination">
        {{ $products->links() }}
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="modal-overlay" wire:click="closeModal">
            <div class="modal-container" wire:click.stop x-data="{ activeTab: 'general' }">
                {{-- Modal Header --}}
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-{{ $modalMode === 'create' ? 'plus' : 'edit' }}"></i>
                        {{ $modalMode === 'create' ? 'Tambah Produk Baru' : 'Edit Produk' }}
                    </h3>
                    <button wire:click="closeModal" class="modal-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Modal Tabs --}}
                <div class="modal-tabs">
                    <button 
                        @click="activeTab = 'general'" 
                        :class="{ 'active': activeTab === 'general' }"
                        class="tab-btn"
                    >
                        <i class="fas fa-info-circle"></i>
                        Informasi Umum
                    </button>
                    <button 
                        @click="activeTab = 'images'" 
                        :class="{ 'active': activeTab === 'images' }"
                        class="tab-btn"
                    >
                        <i class="fas fa-image"></i>
                        Gambar Produk
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        {{-- General Tab --}}
                        <div x-show="activeTab === 'general'" class="tab-content">
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label class="required">Nama Produk</label>
                                    <input 
                                        type="text" 
                                        wire:model="name" 
                                        class="form-control @error('name') is-invalid @enderror"
                                        placeholder="Contoh: Nasi Goreng Special"
                                    >
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label>SKU</label>
                                    <input 
                                        type="text" 
                                        wire:model="sku" 
                                        class="form-control @error('sku') is-invalid @enderror"
                                        placeholder="Contoh: NAS-001"
                                    >
                                    @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea 
                                    wire:model="description" 
                                    class="form-control @error('description') is-invalid @enderror"
                                    rows="3"
                                    placeholder="Deskripsi produk..."
                                ></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="required">Kategori</label>
                                    <select 
                                        wire:model="category_id" 
                                        class="form-control @error('category_id') is-invalid @enderror"
                                    >
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="required">Harga Jual</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input 
                                            type="number" 
                                            wire:model="base_price" 
                                            class="form-control @error('base_price') is-invalid @enderror"
                                            placeholder="0"
                                            step="0.01"
                                        >
                                    </div>
                                    @error('base_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Harga Modal</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input 
                                            type="number" 
                                            wire:model="cost_price" 
                                            class="form-control @error('cost_price') is-invalid @enderror"
                                            placeholder="0"
                                            step="0.01"
                                        >
                                    </div>
                                    @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Waktu Persiapan (menit)</label>
                                    <input 
                                        type="number" 
                                        wire:model="preparation_time" 
                                        class="form-control @error('preparation_time') is-invalid @enderror"
                                        placeholder="15"
                                    >
                                    @error('preparation_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Kalori (kcal)</label>
                                    <input 
                                        type="number" 
                                        wire:model="calories" 
                                        class="form-control @error('calories') is-invalid @enderror"
                                        placeholder="0"
                                    >
                                    @error('calories') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Urutan Tampil</label>
                                    <input 
                                        type="number" 
                                        wire:model="sort_order" 
                                        class="form-control @error('sort_order') is-invalid @enderror"
                                        placeholder="0"
                                    >
                                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Tags (pisahkan dengan koma)</label>
                                    <input 
                                        type="text" 
                                        wire:model="tags" 
                                        class="form-control @error('tags') is-invalid @enderror"
                                        placeholder="spicy, halal, recommended"
                                    >
                                    @error('tags') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-text text-muted">Contoh: spicy, halal, recommended</small>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Alergen (pisahkan dengan koma)</label>
                                    <input 
                                        type="text" 
                                        wire:model="allergens" 
                                        class="form-control @error('allergens') is-invalid @enderror"
                                        placeholder="dairy, nuts, gluten"
                                    >
                                    @error('allergens') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-text text-muted">Contoh: dairy, nuts, gluten</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-checks-group">
                                    <div class="custom-control custom-switch">
                                        <input 
                                            type="checkbox" 
                                            wire:model="is_available" 
                                            class="custom-control-input" 
                                            id="is_available"
                                        >
                                        <label class="custom-control-label" for="is_available">
                                            <i class="fas fa-check-circle"></i>
                                            Tersedia
                                        </label>
                                    </div>

                                    <div class="custom-control custom-switch">
                                        <input 
                                            type="checkbox" 
                                            wire:model="is_featured" 
                                            class="custom-control-input" 
                                            id="is_featured"
                                        >
                                        <label class="custom-control-label" for="is_featured">
                                            <i class="fas fa-star"></i>
                                            Produk Featured
                                        </label>
                                    </div>

                                    <div class="custom-control custom-switch">
                                        <input 
                                            type="checkbox" 
                                            wire:model="is_taxable" 
                                            class="custom-control-input" 
                                            id="is_taxable"
                                        >
                                        <label class="custom-control-label" for="is_taxable">
                                            <i class="fas fa-receipt"></i>
                                            Kena Pajak
                                        </label>
                                    </div>

                                    <div class="custom-control custom-switch">
                                        <input 
                                            type="checkbox" 
                                            wire:model="tax_inclusive" 
                                            class="custom-control-input" 
                                            id="tax_inclusive"
                                        >
                                        <label class="custom-control-label" for="tax_inclusive">
                                            <i class="fas fa-percent"></i>
                                            Harga Sudah Termasuk Pajak
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Images Tab --}}
                        <div x-show="activeTab === 'images'" class="tab-content">
                            <div class="image-upload-section">
                                <h5 class="section-title">
                                    <i class="fas fa-image"></i>
                                    Gambar Produk
                                </h5>
                                <p class="section-description">Upload gambar produk Anda. Format yang didukung: JPG, PNG, WebP. Maksimal 2MB.</p>

                                {{-- Current Image Preview --}}
                                @if($currentImage || $imagePreview)
                                    <div class="current-image-preview">
                                        <div class="preview-container">
                                            <img src="{{ $imagePreview ?: $currentImage }}" alt="Preview">
                                            <button 
                                                type="button"
                                                wire:click="removeImage" 
                                                class="btn-remove-image"
                                                title="Hapus gambar"
                                            >
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <p class="preview-info">
                                            <i class="fas fa-check-circle text-success"></i>
                                            {{ $imagePreview ? 'Gambar baru dipilih' : 'Gambar saat ini' }}
                                        </p>
                                    </div>
                                @endif

                                {{-- Upload Zone --}}
                                <div 
                                    class="dropzone {{ $imagePreview || $currentImage ? 'has-image' : '' }}"
                                    x-data="{ 
                                        dragging: false,
                                        handleDrop(e) {
                                            this.dragging = false;
                                            const files = e.dataTransfer.files;
                                            if (files.length > 0) {
                                                @this.upload('imageFile', files[0]);
                                            }
                                        }
                                    }"
                                    @dragover.prevent="dragging = true"
                                    @dragleave.prevent="dragging = false"
                                    @drop.prevent="handleDrop"
                                    :class="{ 'dragging': dragging }"
                                >
                                    <div class="dropzone-content">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <h5>Drag & Drop Gambar Disini</h5>
                                        <p>atau</p>
                                        <label for="imageFileInput" class="btn btn-primary btn-sm">
                                            <i class="fas fa-folder-open"></i>
                                            Pilih File
                                        </label>
                                        <input 
                                            type="file" 
                                            id="imageFileInput"
                                            wire:model="imageFile" 
                                            accept="image/jpeg,image/png,image/jpg,image/webp"
                                            style="display: none;"
                                        >
                                        <small class="dropzone-hint">JPG, PNG, WebP - Maks 2MB</small>
                                    </div>

                                    {{-- Upload Progress --}}
                                    <div wire:loading wire:target="imageFile" class="upload-progress">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Uploading...</span>
                                        </div>
                                        <p>Mengupload...</p>
                                    </div>
                                </div>

                                @error('imageFile') 
                                    <div class="alert alert-danger mt-2">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Batal
                    </button>
                    <button type="button" wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save"></i>
                            {{ $modalMode === 'create' ? 'Simpan' : 'Update' }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-spin"></i>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Loading Indicator --}}
    <div wire:loading.delay class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>

{{-- Styles --}}
<style>
:root {
    --primary: #ff6b6b;
    --primary-dark: #ee5a5a;
    --primary-light: #ff8787;
    --success: #28a745;
    --warning: #ffc107;
    --danger: #dc3545;
    --info: #17a2b8;
    --secondary: #6c757d;
    --light: #f8f9fa;
    --dark: #343a40;
    --border-radius: 8px;
    --box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    --transition: all 0.3s ease;
}

.product-manager-modern {
    padding: 20px;
    background: #f5f6fa;
    min-height: 100vh;
}

/* Header */
.pm-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.pm-title {
    font-size: 24px;
    font-weight: 600;
    color: var(--dark);
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.pm-title i {
    color: var(--primary);
}

.pm-stats {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: white;
    border-radius: var(--border-radius);
    font-size: 13px;
    font-weight: 500;
    box-shadow: var(--box-shadow);
}

.stat-total { color: var(--primary); }
.stat-available { color: var(--success); }
.stat-unavailable { color: var(--danger); }
.stat-featured { color: var(--warning); }

.btn-create {
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 500;
    border-radius: var(--border-radius);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--primary);
    border: none;
    color: white;
    transition: var(--transition);
}

.btn-create:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
}

/* Controls */
.pm-controls {
    background: white;
    padding: 15px;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.pm-controls-left {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    flex: 1;
}

.search-box {
    position: relative;
    min-width: 250px;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.search-input {
    width: 100%;
    padding: 8px 35px 8px 35px;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-size: 14px;
    transition: var(--transition);
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
}

.search-clear {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 4px;
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-size: 14px;
    background: white;
    cursor: pointer;
    transition: var(--transition);
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary);
}

.btn-clear-filters {
    padding: 8px 15px;
    background: var(--light);
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-size: 14px;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-clear-filters:hover {
    background: #e9ecef;
}

/* View Toggle */
.view-toggle {
    display: flex;
    gap: 5px;
    background: var(--light);
    padding: 4px;
    border-radius: var(--border-radius);
}

.view-btn {
    padding: 8px 15px;
    background: transparent;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: var(--transition);
    color: #666;
}

.view-btn:hover {
    color: var(--primary);
}

.view-btn.active {
    background: white;
    color: var(--primary);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Bulk Actions Bar */
.bulk-actions-bar {
    background: var(--primary-light);
    color: white;
    padding: 12px 20px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.bulk-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.bulk-buttons {
    display: flex;
    gap: 8px;
}

/* Grid View */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.product-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    transition: var(--transition);
    position: relative;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.card-checkbox {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
}

.card-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.card-image {
    position: relative;
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: var(--light);
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.badge-featured {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--warning);
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

.overlay-unavailable {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.7);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 600;
}

.overlay-unavailable i {
    font-size: 32px;
    margin-bottom: 8px;
}

.card-content {
    padding: 15px;
}

.card-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--dark);
    margin: 0 0 8px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}

.badge-sku {
    background: var(--light);
    color: var(--dark);
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.badge-category {
    background: var(--primary-light);
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.card-description {
    font-size: 13px;
    color: #666;
    margin-bottom: 12px;
    line-height: 1.4;
}

.card-footer {
    border-top: 1px solid #f0f0f0;
    padding-top: 12px;
}

.card-price {
    margin-bottom: 12px;
}

.price-label {
    font-size: 12px;
    color: #999;
    display: block;
    margin-bottom: 4px;
}

.price-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary);
}

.card-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.btn-action {
    flex: 1;
    min-width: 36px;
    height: 36px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
    background: var(--light);
    color: #666;
}

.btn-action:hover {
    transform: translateY(-2px);
}

.btn-action.btn-success { background: var(--success); color: white; }
.btn-action.btn-warning { background: var(--warning); color: white; }
.btn-action.btn-primary { background: var(--primary); color: white; }
.btn-action.btn-info { background: var(--info); color: white; }
.btn-action.btn-danger { background: var(--danger); color: white; }
.btn-action.btn-secondary { background: var(--secondary); color: white; }

/* Table View */
.products-table-wrapper {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow-x: auto;
}

.products-table {
    width: 100%;
    border-collapse: collapse;
}

.products-table thead {
    background: var(--light);
}

.products-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: var(--dark);
    border-bottom: 2px solid #ddd;
}

.products-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
}

.products-table tr:hover {
    background: #f8f9fa;
}

.row-unavailable {
    opacity: 0.6;
}

.table-image {
    position: relative;
    width: 60px;
    height: 60px;
    border-radius: 6px;
    overflow: hidden;
}

.table-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.icon-featured {
    position: absolute;
    top: 4px;
    right: 4px;
    color: var(--warning);
    font-size: 12px;
}

.product-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.badge-unavailable {
    background: var(--danger);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
}

.badge-category-table {
    background: var(--primary-light);
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
}

.toggle-status {
    padding: 6px 12px;
    border: none;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: var(--transition);
}

.status-active {
    background: rgba(40, 167, 69, 0.1);
    color: var(--success);
}

.status-inactive {
    background: rgba(220, 53, 69, 0.1);
    color: var(--danger);
}

.toggle-status:hover {
    transform: scale(1.05);
}

.table-actions {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-table-action {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
    color: #666;
}

.btn-table-action:hover {
    background: var(--light);
    transform: scale(1.1);
}

.btn-table-action.text-primary { color: var(--primary) !important; }
.btn-table-action.text-info { color: var(--info) !important; }
.btn-table-action.text-danger { color: var(--danger) !important; }
.btn-table-action.text-warning { color: var(--warning) !important; }

/* Empty State */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.empty-state i {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 20px;
    color: var(--dark);
    margin-bottom: 10px;
}

.empty-state p {
    color: #999;
    margin-bottom: 20px;
}

.empty-state-table {
    padding: 40px;
}

.empty-state-table i {
    font-size: 48px;
    color: #ddd;
    display: block;
    margin-bottom: 15px;
}

/* Pagination */
.pm-pagination {
    margin-top: 20px;
    display: flex;
    justify-content: center;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-container {
    background: white;
    border-radius: var(--border-radius);
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 20px;
    font-weight: 600;
    color: var(--dark);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-title i {
    color: var(--primary);
}

.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: var(--light);
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.modal-close:hover {
    background: var(--danger);
    color: white;
}

.modal-tabs {
    display: flex;
    border-bottom: 1px solid #f0f0f0;
    padding: 0 20px;
    background: var(--light);
}

.tab-btn {
    padding: 15px 20px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-weight: 500;
    color: #666;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 3px solid transparent;
    transition: var(--transition);
}

.tab-btn:hover {
    color: var(--primary);
}

.tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.tab-content {
    animation: fadeIn 0.3s ease;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 6px;
    color: var(--dark);
    font-size: 14px;
}

.form-group label.required::after {
    content: ' *';
    color: var(--danger);
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-size: 14px;
    transition: var(--transition);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
}

.form-control.is-invalid {
    border-color: var(--danger);
}

.invalid-feedback {
    color: var(--danger);
    font-size: 12px;
    margin-top: 4px;
    display: block;
}

.form-text {
    font-size: 12px;
    color: #999;
    margin-top: 4px;
}

.input-group {
    display: flex;
}

.input-group-text {
    padding: 10px 12px;
    background: var(--light);
    border: 1px solid #ddd;
    border-right: none;
    border-radius: var(--border-radius) 0 0 var(--border-radius);
    font-size: 14px;
    color: #666;
}

.input-group .form-control {
    border-left: none;
    border-radius: 0 var(--border-radius) var(--border-radius) 0;
}

.form-checks-group {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.custom-control {
    display: flex;
    align-items: center;
}

.custom-control-input {
    width: 20px;
    height: 20px;
    margin-right: 8px;
    cursor: pointer;
}

.custom-control-label {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    color: var(--dark);
}

/* Image Upload */
.image-upload-section {
    max-width: 600px;
    margin: 0 auto;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title i {
    color: var(--primary);
}

.section-description {
    color: #666;
    font-size: 14px;
    margin-bottom: 20px;
}

.current-image-preview {
    margin-bottom: 20px;
    text-align: center;
}

.preview-container {
    position: relative;
    display: inline-block;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
}

.preview-container img {
    max-width: 100%;
    height: auto;
    max-height: 300px;
    display: block;
}

.btn-remove-image {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 32px;
    height: 32px;
    background: var(--danger);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.btn-remove-image:hover {
    transform: scale(1.1);
}

.preview-info {
    margin-top: 10px;
    font-size: 14px;
    color: #666;
}

.dropzone {
    border: 2px dashed #ddd;
    border-radius: var(--border-radius);
    padding: 40px;
    text-align: center;
    background: var(--light);
    transition: var(--transition);
    position: relative;
}

.dropzone.dragging {
    border-color: var(--primary);
    background: rgba(255, 107, 107, 0.05);
}

.dropzone.has-image {
    opacity: 0.7;
}

.dropzone-content i {
    font-size: 48px;
    color: var(--primary);
    margin-bottom: 15px;
}

.dropzone-content h5 {
    font-size: 16px;
    color: var(--dark);
    margin-bottom: 8px;
}

.dropzone-content p {
    color: #999;
    margin-bottom: 15px;
}

.dropzone-hint {
    display: block;
    color: #999;
    font-size: 12px;
    margin-top: 10px;
}

.upload-progress {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.95);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

/* Modal Footer */
.modal-footer {
    padding: 20px;
    border-top: 1px solid #f0f0f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border-radius: var(--border-radius);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
    border: none;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

.btn-secondary {
    background: var(--secondary);
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    inset: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

/* Responsive */
@media (max-width: 1024px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
}

@media (max-width: 768px) {
    .pm-header {
        flex-direction: column;
    }
    
    .pm-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .pm-controls-left {
        flex-direction: column;
    }
    
    .search-box {
        min-width: 100%;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        flex-direction: column;
    }
    
    .modal-container {
        max-height: 95vh;
    }
    
    .form-checks-group {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

{{-- Scripts --}}
@push('scripts')
<script>
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + S to save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            @this.call('save');
        }
        
        // ESC to close modal
        if (e.key === 'Escape') {
            @this.set('showModal', false);
        }
    });

    // Toast notification listener
    window.addEventListener('show-toast', event => {
        const { message, type } = event.detail;
        
        // You can integrate with your existing toast system
        // Or use a simple alert for now
        if (typeof showToast === 'function') {
            showToast(message, type);
        } else {
            alert(message);
        }
    });
</script>
@endpush
</div>