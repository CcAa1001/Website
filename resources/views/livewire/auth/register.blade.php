<div class="page-header min-vh-100" style="background-color: #f0f2f5;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10 col-md-12">
                <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 20px;">
                    <div class="row g-0">
                        
                        <div class="col-md-7 bg-white p-5 order-2 order-md-1">
                            <h4 class="font-weight-bolder text-dark mb-1">Get Started</h4>
                            <p class="text-secondary text-sm mb-4">Create your business account in seconds.</p>

                            <form wire:submit="store">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3 @if(strlen($name ?? '') > 0) is-filled @endif">
                                            <label class="form-label">Owner Name</label>
                                            <input wire:model.live="name" type="text" class="form-control">
                                        </div>
                                        @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3 @if(strlen($business_name ?? '') > 0) is-filled @endif">
                                            <label class="form-label">Business Name</label>
                                            <input wire:model.live="business_name" type="text" class="form-control">
                                        </div>
                                        @error('business_name') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="input-group input-group-outline mb-3 @if(strlen($email ?? '') > 0) is-filled @endif">
                                    <label class="form-label">Email Address</label>
                                    <input wire:model.live="email" type="email" class="form-control">
                                </div>
                                @error('email') <span class="text-danger text-xs d-block mb-2">{{ $message }}</span> @enderror

                                <div class="input-group input-group-outline mb-3 @if(strlen($password ?? '') > 0) is-filled @endif">
                                    <label class="form-label">Password</label>
                                    <input wire:model.live="password" type="password" class="form-control">
                                </div>
                                @error('password') <span class="text-danger text-xs d-block mb-2">{{ $message }}</span> @enderror

                                <div class="form-check text-start ps-0 mt-3 mb-4">
                                    <input class="form-check-input" type="checkbox" value="" id="agreeCheck" checked>
                                    <label class="form-check-label text-sm text-secondary" for="agreeCheck">
                                        I agree to the <a href="javascript:;" class="text-dark font-weight-bold">Terms and Conditions</a>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" style="border-radius: 10px;">
                                    REGISTER NOW
                                </button>
                                
                                <p class="text-sm text-center mt-3">
                                    Already have an account? 
                                    <a href="{{ route('login') }}" class="text-primary font-weight-bold">Sign In</a>
                                </p>
                            </form>
                        </div>

                         <div class="col-md-5 d-none d-md-block bg-gradient-primary position-relative order-1 order-md-2" 
                              style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80'); background-size: cover; background-position: center;">
                            <span class="mask bg-gradient-primary opacity-6"></span>
                            <div class="position-absolute w-100 h-100 d-flex flex-column justify-content-center p-5 text-white z-index-2">
                                <h3 class="text-white font-weight-bold">Join Us Today</h3>
                                <p class="text-white">Manage your inventory, sales, and customers in one place.</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>