<div> {{-- ROOT ELEMENT --}}
    <div class="container-fluid py-4">
        
        {{-- Flash Message --}}
        @if (session()->has('message'))
            <div class="alert alert-success text-white px-4 py-2 mb-4">
                <i class="material-icons text-sm me-2">check_circle</i> {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger text-white px-4 py-2 mb-4">
                <i class="material-icons text-sm me-2">error</i> {{ session('error') }}
            </div>
        @endif

        {{-- Header & Controls --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-4">
                            <h6 class="text-white mb-0">Kelola Karyawan & Role</h6>
                            <button wire:click="openCreateModal" class="btn bg-white text-primary mb-0 shadow-sm">
                                <i class="material-icons text-sm">person_add</i> Tambah Karyawan
                            </button>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-2">
                        <div class="d-flex flex-wrap gap-3 align-items-center mb-4 bg-gray-100 p-3 rounded">
                            <div class="flex-grow-1">
                                <div class="input-group input-group-outline bg-white rounded">
                                    <label class="form-label">Cari Nama / Email...</label>
                                    <input type="text" class="form-control ps-2" wire:model.live.debounce.300ms="search">
                                </div>
                            </div>
                            <div class="" style="min-width: 200px;">
                                <div class="input-group input-group-static">
                                    <select class="form-control" wire:model.live="filterRole">
                                        <option value="">Semua Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- USER GRID --}}
                        <div class="row">
                            @forelse($users as $user)
                            <div class="col-xl-4 col-md-6 mb-4">
                                <div class="card h-100 shadow-sm border card-hover">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="position-relative me-3">
                                                @if($user->avatar_url)
                                                    <img src="{{ asset('storage/'.$user->avatar_url) }}" alt="Avatar" class="avatar avatar-lg rounded-circle shadow-sm object-fit-cover">
                                                @else
                                                    <div class="avatar avatar-lg rounded-circle bg-gradient-info shadow-sm d-flex align-items-center justify-content-center text-white text-lg font-weight-bold">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <span class="position-absolute bottom-0 end-0 p-1 bg-{{ $user->is_active ? 'success' : 'secondary' }} border border-white rounded-circle">
                                                    <span class="visually-hidden">Status</span>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="mb-0 text-truncate">{{ $user->name }}</h6>
                                                <p class="text-xs text-muted mb-0 text-truncate">{{ $user->email }}</p>
                                                <div class="mt-1">
                                                    @php
                                                        $badgeColor = match(strtolower($user->role->name ?? '')) {
                                                            'admin', 'super_admin' => 'dark',
                                                            'manager' => 'primary',
                                                            'cashier' => 'success',
                                                            'kitchen' => 'warning',
                                                            'waiter' => 'info',
                                                            default => 'secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge badge-sm bg-gradient-{{ $badgeColor }}">
                                                        {{ ucfirst($user->role->name ?? 'No Role') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="dropdown ms-2">
                                                <button class="btn btn-link text-secondary mb-0" data-bs-toggle="dropdown">
                                                    <i class="material-icons">more_vert</i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:;" wire:click="edit('{{ $user->id }}')">
                                                            <i class="material-icons text-sm me-2">edit</i> Edit Akun
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:;" wire:click="toggleStatus('{{ $user->id }}')">
                                                            <i class="material-icons text-sm me-2">{{ $user->is_active ? 'block' : 'check_circle' }}</i> 
                                                            {{ $user->is_active ? 'Non-aktifkan' : 'Aktifkan' }}
                                                        </a>
                                                    </li>
                                                    @if(auth()->id() !== $user->id)
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="javascript:;" onclick="confirm('Hapus user ini?') || event.stopImmediatePropagation()" wire:click="delete('{{ $user->id }}')">
                                                            <i class="material-icons text-sm me-2">delete</i> Hapus
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                        <hr class="horizontal dark my-3">
                                        <div class="d-flex justify-content-between text-xs text-muted">
                                            <span><i class="material-icons text-xs me-1">phone</i> {{ $user->phone ?? '-' }}</span>
                                            <span>Joined: {{ $user->created_at->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-5">
                                <i class="material-icons text-secondary opacity-3" style="font-size: 64px;">people_outline</i>
                                <h5 class="mt-3 text-secondary">Belum ada data karyawan.</h5>
                            </div>
                            @endforelse
                        </div>

                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL FORM --}}
        @if($showModal)
        <div class="fixed-top w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="card shadow-lg m-3" style="width: 100%; max-width: 600px; max-height: 90vh; display: flex; flex-direction: column;">
                <div class="card-header p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $modalMode === 'create' ? 'Tambah Karyawan' : 'Edit Karyawan' }}</h5>
                    <button type="button" class="btn-close p-2 text-dark" wire:click="closeModal">
                        <i class="material-icons">close</i>
                    </button>
                </div>
                
                <div class="card-body p-4" style="overflow-y: auto;">
                    <form wire:submit.prevent="save">
                        
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if ($avatar)
                                    <img src="{{ $avatar->temporaryUrl() }}" class="avatar avatar-xl rounded-circle object-fit-cover border border-2 border-primary">
                                @elseif ($currentAvatar)
                                    <img src="{{ asset('storage/'.$currentAvatar) }}" class="avatar avatar-xl rounded-circle object-fit-cover border">
                                @else
                                    <div class="avatar avatar-xl rounded-circle bg-gray-200 d-flex align-items-center justify-content-center">
                                        <i class="material-icons text-secondary" style="font-size: 32px;">person</i>
                                    </div>
                                @endif
                                <label for="avatarUpload" class="position-absolute bottom-0 end-0 btn btn-sm btn-icon btn-primary rounded-circle mb-0 shadow-sm" style="width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="material-icons text-xs">edit</i>
                                </label>
                                <input type="file" id="avatarUpload" wire:model="avatar" class="d-none" accept="image/*">
                            </div>
                            <div wire:loading wire:target="avatar" class="text-xs text-primary mt-1">Uploading...</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group input-group-outline">
                                    <input type="text" class="form-control" wire:model="name">
                                </div>
                                @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <div class="input-group input-group-outline">
                                    <input type="email" class="form-control" wire:model="email">
                                </div>
                                @error('email') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">No. Telepon</label>
                                <div class="input-group input-group-outline">
                                    <input type="text" class="form-control" wire:model="phone">
                                </div>
                                @error('phone') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Jabatan / Role <span class="text-danger">*</span></label>
                                <div class="input-group input-group-static">
                                    <select class="form-control" wire:model="role_id">
                                        <option value="">Pilih Role...</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('role_id') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                                <small class="text-muted text-xs">Role menentukan akses menu pengguna.</small>
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="form-check form-switch ps-0">
                                    <input class="form-check-input ms-auto" type="checkbox" id="activeSwitch" wire:model="is_active">
                                    <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="activeSwitch">Akun Aktif</label>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <div class="alert alert-light border">
                                    <h6 class="text-sm mb-2"><i class="material-icons text-sm me-1">lock</i> Keamanan</h6>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="input-group input-group-outline my-1">
                                                <label class="form-label">Password {{ $modalMode == 'edit' ? '(Opsional)' : '*' }}</label>
                                                <input type="password" class="form-control" wire:model="password">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group input-group-outline my-1">
                                                <label class="form-label">Konfirmasi Password</label>
                                                <input type="password" class="form-control" wire:model="password_confirmation">
                                            </div>
                                        </div>
                                    </div>
                                    @error('password') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-light me-2" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn bg-gradient-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    <style>
        .card-hover { transition: transform 0.2s; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        .bg-gray-100 { background-color: #f8f9fa !important; }
        .bg-gray-200 { background-color: #e9ecef !important; }
        .object-fit-cover { object-fit: cover; }
    </style>
</div>