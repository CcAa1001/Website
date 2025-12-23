<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 card p-4 mb-4 shadow-sm">
            <h5 class="mb-3">Add New Product to Inventory</h5>
            <form wire:submit.prevent="addProduct">
                <div class="row">
                    <div class="col-md-3 mb-3"><input type="text" wire:model="name" class="form-control border p-2" placeholder="Product Name"></div>
                    <div class="col-md-2 mb-3"><input type="number" wire:model="price" class="form-control border p-2" placeholder="Price"></div>
                    <div class="col-md-2 mb-3"><input type="number" wire:model="stock" class="form-control border p-2" placeholder="Stock"></div>
                    <div class="col-md-3 mb-3">
                        <select wire:model="category_id" class="form-control border p-2">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Add Product</button></div>
                </div>
            </form>
        </div>

        <div class="col-12 card p-4">
            <h6 class="mb-4">Live Stock Management</h6>
            <table class="table">
                <thead><tr><th>Name</th><th>Price</th><th>Stock Status</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>${{ number_format($product->price, 2) }}</td>
                        <td>
                            <span class="badge {{ $product->stock < 5 ? 'bg-gradient-danger' : 'bg-gradient-success' }}">
                                {{ $product->stock }} in stock
                            </span>
                        </td>
                        <td>
                            <button wire:click="incrementStock({{ $product->id }})" class="btn btn-sm btn-outline-info">+10 Stock</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>