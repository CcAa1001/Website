<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0 text-start">
                    <h4 class="font-weight-bolder">{{ $isEditing ? 'Edit Produk' : 'Tambah Produk Baru' }}</h4>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="input-group input-group-outline mb-3 {{ $name ? 'is-filled' : '' }}">
                            <label class="form-label">Nama Makanan</label>
                            <input type="text" class="form-control" wire:model="name">
                        </div>
                        <div class="input-group input-group-outline mb-3 {{ $price ? 'is-filled' : '' }}">
                            <label class="form-label">Harga ($)</label>
                            <input type="number" class="form-control" wire:model="price">
                        </div>
                        <div class="input-group input-group-outline mb-3">
                            <select class="form-control" wire:model="category_id">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Produk</label>
                            <input type="file" class="form-control border p-2" wire:model="image">
                        </div>
                        <div class="form-check form-switch d-flex align-items-center mb-3">
                            <input class="form-check-input" type="checkbox" id="is_available" wire:model="is_available">
                            <label class="form-check-label mb-0 ms-3" for="is_available">Tersedia (Stock Ready)</label>
                        </div>
                        <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Simpan Produk</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('assets/img/products/product-1-min.jpg') }}" class="avatar avatar-sm me-3 border-radius-lg">
                                            <h6 class="mb-0 text-sm">{{ $product->name }}</h6>
                                        </div>
                                    </td>
                                    <td>${{ number_format($product->price, 2) }}</td>
                                    <td>
                                        <span class="badge badge-sm {{ $product->is_available ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                            {{ $product->is_available ? 'Available' : 'Sold Out' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button wire:click="edit({{ $product->id }})" class="btn btn-link text-info p-0 mb-0">Edit</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>