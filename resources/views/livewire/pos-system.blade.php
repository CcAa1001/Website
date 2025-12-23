<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                @foreach($products as $p)
                <div class="col-md-4 mb-4">
                    <div class="card p-3 text-center shadow-sm">
                        <h6>{{ $p->name }}</h6>
                        <p class="text-primary font-weight-bold">${{ $p->price }}</p>
                        <button wire:click="addToCart({{ $p->id }}, '{{ $p->name }}', {{ $p->price }})" class="btn btn-sm btn-outline-primary">Add to Order</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4">
                <h5>Current Order</h5>
                <hr>
                @foreach($cart as $item)
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ $item['name'] }} (x{{ $item['qty'] }})</span>
                    <span>${{ $item['price'] * $item['qty'] }}</span>
                </div>
                @endforeach
                <hr>
                <h4 class="text-end">Total: ${{ $total }}</h4>
                <button wire:click="checkout" class="btn btn-success w-100 mt-3">Complete Transaction</button>
            </div>
        </div>
    </div>
</div>