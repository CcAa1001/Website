<div class="container py-4">
    @if($orderSuccess)
    <div class="card text-center mt-5 shadow">
        <div class="card-body py-5">
            <div class="mb-3 text-success">
                <h1 style="font-size: 4rem;">✅</h1>
            </div>
            <h3 class="font-weight-bold">Pesanan Terkirim!</h3>
            <p class="mb-1">Nomor Order: <strong class="text-primary">{{ $orderNumberStr }}</strong></p>
            <p class="text-muted">Mohon tunggu, pesanan sedang disiapkan.</p>
            <hr class="w-50 mx-auto my-4">
            <button wire:click="$set('orderSuccess', false)" class="btn btn-outline-dark mt-2">Pesan Lagi</button>
        </div>
    </div>
    @else

    <div class="d-flex justify-content-between align-items-center mb-4 sticky-top bg-white py-3 shadow-sm px-3 rounded">
        <div>
            <h5 class="font-weight-bold mb-0">Menu Restoran</h5>
            <span class="badge bg-gradient-info">Meja {{ $tableNumber }}</span>
        </div>
        <button class="btn btn-dark position-relative" data-bs-toggle="modal" data-bs-target="#cartModal">
            <i class="material-icons text-lg">shopping_cart</i>
            @if(count($cart) > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                {{ count($cart) }}
            </span>
            @endif
        </button>
    </div>

    <div class="d-flex overflow-auto mb-4 pb-2 px-1" style="white-space: nowrap;">
        <button class="btn btn-sm me-2 mb-0 {{ $selectedCategory == 'all' ? 'bg-gradient-dark' : 'btn-outline-dark' }}" 
                wire:click="selectCategory('all')">Semua</button>
        @foreach($categories as $cat)
        <button class="btn btn-sm me-2 mb-0 {{ $selectedCategory == $cat->id ? 'bg-gradient-dark' : 'btn-outline-dark' }}" 
                wire:click="selectCategory('{{ $cat->id }}')">
            {{ $cat->name }}
        </button>
        @endforeach
    </div>

    <div class="row">
        @forelse($products as $product)
        <div class="col-6 col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header p-0 position-relative z-index-1">
                    <a href="javascript:;" class="d-block">
                        <img src="{{ $product->image_url ? asset('storage/'.$product->image_url) : 'https://via.placeholder.com/300x200?text=No+Image' }}" 
                             class="img-fluid border-radius-lg w-100" style="height: 150px; object-fit: cover;">
                    </a>
                </div>
                <div class="card-body pt-3 pb-3 px-3">
                    <h6 class="text-dark font-weight-bold mb-1 text-sm">{{ $product->name }}</h6>
                    <p class="text-xs text-secondary mb-2">{{ Str::limit($product->description, 30) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-sm font-weight-bold text-primary">Rp {{ number_format($product->base_price, 0, ',', '.') }}</span>
                        <button wire:click="addToCart('{{ $product->id }}')" class="btn btn-icon btn-sm btn-primary mb-0 shadow-sm">
                            <span class="btn-inner--icon"><i class="material-icons">add</i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">Tidak ada menu di kategori ini.</p>
        </div>
        @endforelse
    </div>

    <div class="modal fade" id="cartModal" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Keranjang Pesanan</h6>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if(empty($cart))
                        <div class="text-center py-4">
                            <i class="material-icons text-secondary text-4xl mb-2">remove_shopping_cart</i>
                            <p class="text-sm">Keranjang Anda kosong.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush mb-3">
                            @foreach($cart as $id => $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div class="d-flex align-items-center">
                                    <div class="ms-2">
                                        <h6 class="text-sm mb-0">{{ $item['name'] }}</h6>
                                        <small class="text-muted">Rp {{ number_format($item['price'], 0, ',', '.') }}</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center bg-gray-100 rounded p-1">
                                    <button class="btn btn-xs btn-link mb-0 p-1" wire:click="updateQty('{{ $id }}', -1)"><i class="material-icons text-xs">remove</i></button>
                                    <span class="text-xs font-weight-bold mx-2">{{ $item['qty'] }}</span>
                                    <button class="btn btn-xs btn-link mb-0 p-1" wire:click="updateQty('{{ $id }}', 1)"><i class="material-icons text-xs">add</i></button>
                                </div>
                            </li>
                            @endforeach
                        </ul>

                        <hr>
                        <div class="form-group">
                            <label class="form-label text-xs font-weight-bold">Nama Pemesan (Wajib)</label>
                            <input type="text" class="form-control border px-2" wire:model="customerName" placeholder="Contoh: Budi">
                            @error('customerName') <span class="text-danger text-xs d-block mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 pt-0">
                    @if(!empty($cart))
                    <button type="button" wire:click="placeOrder" class="btn bg-gradient-dark w-100 shadow-lg">
                        Pesan Sekarang <i class="material-icons text-sm ms-1">send</i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>