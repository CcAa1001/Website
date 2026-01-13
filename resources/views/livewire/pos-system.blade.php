<div class="container-fluid py-4">
    <div class="row" style="min-height: 80vh;">
        <div class="col-lg-8">
            <div class="card bg-transparent shadow-none border-0">
                <div class="card-header pb-0 bg-transparent px-0">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="font-weight-bolder">Point of Sale</h4>
                            <p class="text-sm">Select products to add to order</p>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-outline bg-white rounded">
                                <label class="form-label">Search products...</label>
                                <input type="text" class="form-control" wire:model.live="search">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body px-0 pt-4">
                    <div class="row">
                        @forelse($products as $p)
                        <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
                            <div class="card border-radius-lg shadow-sm h-100 product-card cursor-pointer border" wire:click="addToCart('{{ $p->id }}')">
                                @if($p->image_url)
                                    <img src="{{ asset('storage/' . $p->image_url) }}" 
                                         class="card-img-top border-radius-lg" 
                                         style="height: 120px; object-fit: cover;"
                                         onerror="this.src='https://via.placeholder.com/150?text=Image+Error'">
                                @else
                                    <div class="bg-gray-200 border-radius-lg d-flex align-items-center justify-content-center" style="height: 120px;">
                                        <i class="material-icons text-secondary opacity-5">image</i>
                                    </div>
                                @endif

                                <div class="card-body p-2 text-center">
                                    <h6 class="mb-0 text-sm font-weight-bold">{{ $p->name }}</h6>
                                    <h6 class="text-primary mb-0 text-sm">Rp {{ number_format($p->base_price, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-5">
                            <p class="text-secondary">No products found.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-lg sticky-top" style="top: 20px;">
                <div class="theme-card-header bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 px-3 mx-3 mt-n4">
                    <h6 class="text-white text-capitalize ps-3 font-weight-bold">Current Order</h6>
                </div>
                <div class="card-body px-3 pt-4">
                    @if(empty($cart))
                        <p class="text-center text-secondary py-5">Cart is empty</p>
                    @else
                        <ul class="list-group list-group-flush mb-4 overflow-auto" style="max-height: 300px;">
                            @foreach($cart as $id => $item)
                            <li class="list-group-item px-0 border-0 d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0 text-xs font-weight-bold">{{ $item['name'] }}</h6>
                                    <small class="text-secondary">Rp {{ number_format($item['price'], 0, ',', '.') }} x {{ $item['qty'] }}</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-link text-danger mb-0 p-1" wire:click="removeFromCart('{{ $id }}')"><i class="material-icons text-sm">remove_circle</i></button>
                                    <span class="text-xs font-weight-bold mx-1">{{ $item['qty'] }}</span>
                                    <button class="btn btn-link text-success mb-0 p-1" wire:click="addToCart('{{ $id }}')"><i class="material-icons text-sm">add_circle</i></button>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        <div class="bg-light p-3 rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Total</h6>
                                <h5 class="text-primary mb-0 font-weight-bold">Rp {{ number_format($grand_total, 0, ',', '.') }}</h5>
                            </div>
                        </div>
                        <button wire:click="checkout" class="btn theme-btn bg-gradient-primary w-100 py-3 shadow-lg">COMPLETE ORDER</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>