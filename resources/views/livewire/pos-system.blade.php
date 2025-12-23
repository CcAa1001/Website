<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card mb-4">
                <div class="card-header pb-0"><h5>Available Products</h5></div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="row p-3">
                        @foreach($products as $p)
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card card-blog card-plain border p-2 text-center" style="cursor: pointer" wire:click="addToCart({{ $p->id }}, '{{ $p->name }}', {{ $p->price }})">
                                <h6 class="mt-2">{{ $p->name }}</h6>
                                <p class="text-gradient text-primary font-weight-bold">${{ number_format($p->price, 2) }}</p>
                                <span class="badge badge-sm bg-gradient-secondary">Stock: {{ $p->stock }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-header bg-gradient-primary">
                    <h6 class="text-white">Current Transaction</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($cart as $id => $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            {{ $item['name'] }} (x{{ $item['qty'] }})
                            <span>${{ number_format($item['price'] * $item['qty'], 2) }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>TOTAL:</strong>
                        <h4 class="text-primary font-weight-bold">${{ number_format($total, 2) }}</h4>
                    </div>
                    <button wire:click="checkout" class="btn bg-gradient-success w-100 mt-3" @if(empty($cart)) disabled @endif>Complete Order</button>
                </div>
            </div>
        </div>
    </div>
</div>