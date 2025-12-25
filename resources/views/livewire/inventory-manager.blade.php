<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 card p-4 mb-4 shadow-sm">
            <h5 class="mb-3">Tambah Produk ke Katalog</h5>
            <form wire:submit.prevent="addProduct">
                <div class="row">
                    <div class="col-md-3 mb-3"><input type="text" wire:model="name" class="form-control border p-2" placeholder="Nama Produk"></div>
                    <div class="col-md-2 mb-3"><input type="number" wire:model="price" class="form-control border p-2" placeholder="Harga Jual"></div>
                    <div class="col-md-2 mb-3"><input type="number" wire:model="stock" class="form-control border p-2" placeholder="Stok"></div>
                    <div class="col-md-3 mb-3">
                        <select wire:model="category_id" class="form-control border p-2">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Simpan</button></div>
                </div>
            </form>
        </div>
        <div class="col-12 card p-4">
            <table class="table">
                <thead><tr><th>Nama</th><th>Harga</th><th>Stok</th></tr></thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>Rp {{ number_format($product->price) }}</td>
                        <td><span class="badge {{ $product->stock < 5 ? 'bg-gradient-danger' : 'bg-gradient-success' }}">{{ $product->stock }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>