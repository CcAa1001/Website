<div class="page-header min-vh-100" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
    <style>
        /* Modern Mesh Background (Optional alternative to the gradient above) */
        .page-header {
            background-color: #e5e5f7;
            background-image:  radial-gradient(#4433ff 0.5px, transparent 0.5px), radial-gradient(#4433ff 0.5px, #e5e5f7 0.5px);
            background-size: 20px 20px;
            background-position: 0 0,10px 10px;
            /* Or use a soft solid color: background: #f8f9fa; */
        }

        /* Enhanced Glass Wrapper */
        .glass-container {
            background: rgba(255, 255, 255, 0.4); /* More transparent for better glass effect */
            backdrop-filter: blur(20px);          /* Stronger blur */
            -webkit-backdrop-filter: blur(20px);
            border-radius: 40px;                  /* Softer corners */
            border: 1px solid rgba(255, 255, 255, 0.7); 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        /* Make the internal card also slightly transparent */
        .glass-card {
            background: rgba(255, 255, 255, 0.8) !important;
            border: none !important;
        }

        .fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .btn-round {
            border-radius: 12px !important;
            text-transform: none;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
    </style>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8"> <div class="glass-container p-3 fade-in-up">
                    <div class="card glass-card shadow-none overflow-hidden" style="border-radius: 30px;">
                        <div class="card-body p-5">
                            <div class="text-center mb-5">
                                <h3 class="font-weight-bolder text-dark">Welcome</h3>
                                <p class="text-secondary text-sm">Please enter your credentials</p>
                            </div>

                            <form wire:submit="store" class="text-start">
                                @if (Session::has('status'))
                                <div class="alert alert-success text-white text-xs mb-3 p-2 rounded" role="alert">
                                    {{ Session::get('status') }}
                                </div>
                                @endif

                                @error('email')
                                <div class="alert alert-danger text-white text-xs mb-3 p-2 rounded" role="alert">
                                    {{ $message }}
                                </div>
                                @enderror

                                <div class="input-group input-group-outline mb-4 @if(strlen($email ?? '') > 0) is-filled @endif">
                                    <label class="form-label">Email</label>
                                    <input wire:model.live="email" type="email" class="form-control">
                                </div>

                                <div class="input-group input-group-outline mb-4 @if(strlen($password ?? '') > 0) is-filled @endif">
                                    <label class="form-label">Password</label>
                                    <input wire:model.live="password" type="password" class="form-control">
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check form-switch p-0 m-0 d-flex align-items-center">
                                        <input class="form-check-input ms-0" type="checkbox" wire:model="remember" id="rememberMe">
                                        <label class="form-check-label mb-0 ms-2 text-sm text-secondary" for="rememberMe">Remember</label>
                                    </div>
                                    <a href="{{ route('password.forgot') }}" class="text-primary text-xs font-weight-bold">Forgot?</a>
                                </div>

                                <button type="submit" class="btn bg-gradient-primary w-100 btn-lg mb-0 btn-round shadow-primary">
                                    Sign In
                                </button>
                            </form>
                        </div>
                    </div>
                </div> 
                
                <div class="text-center mt-4">
                    <p class="text-sm text-secondary">
                        Don't have an account? <a href="#" class="text-primary font-weight-bold">Contact Admin</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>