<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 card p-4 mb-4">
            <h5 class="mb-3">Add New Product</h5>
            <form wire:submit.prevent="addProduct">
                <div class="row">
                    <div class="col-md-3"><input type="text" wire:model="name" class="form-control border p-2" placeholder="Product Name"></div>
                    <div class="col-md-2"><input type="number" wire:model="price" class="form-control border p-2" placeholder="Price"></div>
                    <div class="col-md-2"><input type="number" wire:model="stock" class="form-control border p-2" placeholder="Stock"></div>
                    <div class="col-md-3">
                        <select wire:model="category_id" class="form-control border p-2">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Add</button></div>
                </div>
            </form>
        </div>
        <div class="col-12 card p-4">
            <table class="table">
                <thead><tr><th>Name</th><th>Price</th><th>Stock</th></tr></thead>
                <tbody>
                    @foreach($products as $product)
                    <tr><td>{{ $product->name }}</td><td>${{ $product->price }}</td><td>{{ $product->stock }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>