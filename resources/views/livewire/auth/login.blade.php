
<div class="container d-flex justify-content-center">
    {{-- Root element harus membungkus semua konten --}}
    <div class="glass-card">
        <div class="text-center mb-4">
            <h3 class="font-weight-bolder text-white text-shadow">Welcome Back!</h3>
            <p class="mb-0 text-white opacity-8">Silakan login untuk melanjutkan</p>
                        <h6 class='text-white text-center'>
                <span class="font-weight-normal">Email:</span>budi.santoso@resto.com {{--admin@material.com --}}
                <br>
                <span class="font-weight-normal">Password:</span>password {{--secret--}}
            </h6>
        </div>

        <form wire:submit.prevent='login'>
            
            @if (Session::has('status'))
            <div class="alert alert-success text-white mb-3 bg-gradient-success" role="alert">
                {{ Session::get('status') }}
            </div>
            @endif

            <div class="mb-3">
                <label class="form-label text-white">Email</label>
                <input wire:model="email" type="email" class="form-control glass-input ps-3" placeholder="name@example.com">
                @error('email') <p class='text-danger text-xs mt-1 font-weight-bold'>{{ $message }}</p> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label text-white">Password</label>
                <input wire:model="password" type="password" class="form-control glass-input ps-3" placeholder="••••••••">
                @error('password') <p class='text-danger text-xs mt-1 font-weight-bold'>{{ $message }}</p> @enderror
            </div>

            <div class="form-check form-switch d-flex align-items-center mb-3">
                <input class="form-check-input" type="checkbox" id="rememberMe" wire:model="remember_me">
                <label class="form-check-label mb-0 ms-3 text-white" for="rememberMe">Ingat Saya</label>
            </div>

            <div class="text-center">
                <button type="submit" class="btn bg-white text-primary w-100 my-3 mb-2 font-weight-bold">
                    <span wire:loading.remove>MASUK</span>
                    <span wire:loading>LOADING...</span>
                </button>
            </div>

            <p class="mt-4 text-sm text-center text-white">
                Lupa password?
                <a href="{{ route('password.forgot') }}" class="text-info text-gradient font-weight-bold">Reset disini</a>
            </p>
        </form>
    </div>
</div>