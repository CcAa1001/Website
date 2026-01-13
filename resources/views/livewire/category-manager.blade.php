<div class="container-fluid py-4">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="ni ni-check-bold"></i></span>
            <span class="alert-text">{{ session('message') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="ni ni-fat-remove"></i></span>
            <span class="alert-text">{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        {{-- Form Section --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="font-weight-bolder">{{ $this->formTitle }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        {{-- Name Input --}}
                        <div class="input-group input-group-outline mb-3 {{ $name ? 'is-filled' : '' }}">
                            <label class="form-label">Nama Kategori *</label>
                            <input type="text" class="form-control" wire:model="name">
                        </div>
                        @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        {{-- Parent Category Dropdown --}}
                        <div class="input-group input-group-outline mb-3 {{ $parent_id ? 'is-filled' : '' }}">
                            <select class="form-control" wire:model="parent_id">
                                <option value="">-- Kategori Utama --</option>
                                @foreach($parentOptions as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Description --}}
                        <div class="input-group input-group-outline mb-3 {{ $description ? 'is-filled' : '' }}">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" wire:model="description" rows="3"></textarea>
                        </div>
                        @error('description') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        {{-- Image Upload --}}
                        <div class="mb-3">
                            <label class="form-label">Gambar Kategori</label>
                            
                            {{-- Current Image Preview --}}
                            @if ($currentImage && !$image)
                                <div class="mb-2 position-relative d-inline-block">
                                    <img src="{{ asset('storage/' . $currentImage) }}" class="img-thumbnail" style="max-height: 120px;">
                                    <button type="button" wire:click="removeImage" 
                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1">
                                        <i class="material-icons text-xs">close</i>
                                    </button>
                                </div>
                            @endif

                            {{-- New Image Preview --}}
                            @if ($image)
                                <div class="mb-2">
                                    <img src="{{ $image->temporaryUrl() }}" class="img-thumbnail" style="max-height: 120px;">
                                </div>
                            @endif

                            <input type="file" class="form-control border p-2" wire:model="image" accept="image/*">
                            <div wire:loading wire:target="image" class="text-primary text-xs mt-1">Mengupload...</div>
                        </div>
                        @error('image') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        {{-- Sort Order --}}
                        <div class="input-group input-group-outline mb-3 is-filled">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" class="form-control" wire:model="sort_order" min="0">
                        </div>

                        {{-- Active Toggle --}}
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" wire:model="is_active">
                            <label class="form-check-label" for="is_active">Aktif (Tampil di Menu)</label>
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

        {{-- List Section --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bolder">Daftar Kategori</h5>
                    
                    {{-- Filters --}}
                    <div class="d-flex gap-2 align-items-center">
                        <div class="input-group input-group-outline is-filled" style="width: 200px;">
                            <input type="text" class="form-control" placeholder="Cari kategori..." wire:model.live="search">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showInactive" wire:model.live="showInactive">
                            <label class="form-check-label" for="showInactive">Tampilkan Nonaktif</label>
                        </div>
                    </div>
                </div>
                
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kategori</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Parent</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Urutan</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        {{-- Category Info --}}
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                @if($category->image_url)
                                                    <img src="{{ asset('storage/' . $category->image_url) }}" 
                                                         class="avatar avatar-sm me-3 border-radius-lg" 
                                                         alt="{{ $category->name }}">
                                                @else
                                                    <div class="avatar avatar-sm bg-gradient-secondary me-3 d-flex align-items-center justify-content-center border-radius-lg">
                                                        <i class="material-icons text-white text-sm">category</i>
                                                    </div>
                                                @endif
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $category->name }}</h6>
                                                    @if($category->description)
                                                        <p class="text-xs text-secondary mb-0">{{ Str::limit($category->description, 50) }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Parent Category --}}
                                        <td>
                                            @if($category->parent)
                                                <span class="badge badge-sm bg-gradient-info">{{ $category->parent->name }}</span>
                                            @else
                                                <span class="text-secondary text-xs">-</span>
                                            @endif
                                        </td>

                                        {{-- Sort Order --}}
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-sm bg-gradient-secondary">{{ $category->sort_order }}</span>
                                        </td>

                                        {{-- Status Toggle --}}
                                        <td class="align-middle text-center">
                                            <button wire:click="toggleStatus({{ $category->id }})" 
                                                    class="btn btn-sm mb-0 {{ $category->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="align-middle">
                                            <div class="d-flex gap-2 justify-content-end pe-3">
                                                <button wire:click="edit('{{ $category->id }}')" 
                                                        class="btn btn-link text-secondary mb-0" 
                                                        data-bs-toggle="tooltip" 
                                                        title="Edit">
                                                    <i class="material-icons text-sm">edit</i>
                                                </button>
                                                <button wire:click="delete('{{ $category->id }}')" 
                                                        onclick="return confirm('Yakin ingin menghapus kategori ini?')" 
                                                        class="btn btn-link text-danger mb-0" 
                                                        data-bs-toggle="tooltip" 
                                                        title="Hapus">
                                                    <i class="material-icons text-sm">delete</i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-secondary">
                                                <i class="material-icons text-lg mb-2">category</i>
                                                <p class="mb-0">Belum ada kategori. Tambahkan kategori pertama Anda!</p>
                                            </div>
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
</div>

@push('js')
<script>
    // Auto-dismiss alerts after 3 seconds
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