@if($open)
<div class="modal fade show d-block" style="background: rgba(0,0,0,.6)">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">

            <button class="btn-close ms-auto" wire:click="close"></button>

            <h4>{{ $product->name }}</h4>

            <x-product-image :product="$product" size="large" />

            <p class="mt-2">{{ $product->description }}</p>

            <h5>
                Rp {{ number_format($product->base_price, 0, ',', '.') }}
            </h5>

            <button class="btn btn-primary w-100 mt-3">
                Tambah ke Pesanan
            </button>

        </div>
    </div>
</div>
@endif
