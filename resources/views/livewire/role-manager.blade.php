<div>
    <div class="container-fluid py-4">
        
        {{-- Notifications --}}
        @if (session()->has('message'))
            <div class="alert alert-success text-white px-4 py-2 mb-4"><i class="material-icons text-sm me-2">check_circle</i> {{ session('message') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger text-white px-4 py-2 mb-4"><i class="material-icons text-sm me-2">error</i> {{ session('error') }}</div>
        @endif

        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-4">
                            <h6 class="text-white mb-0">Role & Permission Management</h6>
                            <button wire:click="openCreateModal" class="btn bg-white text-dark mb-0">
                                <i class="material-icons text-sm">add_moderator</i> Buat Role Baru
                            </button>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Role</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Akses Menu (Permission)</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jumlah User</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm font-weight-bold">{{ ucfirst($role->name) }}</h6>
                                                    </div>
                                                </div>
                                            </td>


                                            <td>
                                                @php
                                                    // Cek tipe data permissions agar aman
                                                    $perms = [];
                                                    if ($role->permissions) {
                                                        if (is_array($role->permissions)) {
                                                            $perms = $role->permissions;
                                                        } else {
                                                            $perms = json_decode($role->permissions, true) ?? [];
                                                        }
                                                    }
                                                @endphp
                                                
                                                <div class="d-flex flex-wrap gap-1" style="max-width: 400px;">
                                                    @if(empty($perms))
                                                        <span class="badge badge-sm bg-gradient-secondary">No Access</span>
                                                    @else
                                                        {{-- Jika punya akses semua (*) --}}
                                                        @if(in_array('*', $perms))
                                                            <span class="badge badge-sm bg-gradient-success">All Access (Super Admin)</span>
                                                        @else
                                                            @foreach(array_slice($perms, 0, 5) as $perm)
                                                                <span class="badge badge-sm bg-gradient-info">{{ $perm }}</span>
                                                            @endforeach
                                                            @if(count($perms) > 5)
                                                                <span class="badge badge-sm bg-gradient-light text-dark">+{{ count($perms) - 5 }} others</span>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">{{ $role->users()->count() }} Karyawan</span>
                                            </td>
                                            <td class="align-middle text-end">
                                                <button wire:click="edit({{ $role->id }})" class="btn btn-link text-dark px-3 mb-0">
                                                    <i class="material-icons text-sm me-2">edit</i> Edit
                                                </button>
                                                @if(!in_array(strtolower($role->name), ['admin', 'super_admin']))
                                                    <button wire:click="initiateDelete({{ $role->id }})" class="btn btn-link text-danger px-3 mb-0">
                                                        <i class="material-icons text-sm me-2">delete</i> Hapus
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>

                                        
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL CREATE/EDIT ROLE --}}
        @if($showModal)
        <div class="fixed-top w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="card shadow-lg m-3" style="width: 100%; max-width: 600px;">
                <div class="card-header p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $modalMode == 'create' ? 'Buat Role Baru' : 'Edit Role' }}</h5>
                    <button wire:click="closeModal" class="btn-close text-dark"><i class="material-icons">close</i></button>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nama Role</label>
                        <input type="text" class="form-control border px-2" wire:model="name" placeholder="Contoh: Kasir Senior">
                        @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                    </div>

                    <label class="form-label fw-bold mb-2">Akses Menu (Permission)</label>
                    <div class="row g-2 border p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                        @foreach($availablePermissions as $key => $label)
                            <div class="col-md-6">
                                <div class="form-check ps-0">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" value="{{ $key }}" wire:model="selectedPermissions" id="perm_{{ $key }}">
                                    <label class="custom-control-label" for="perm_{{ $key }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer p-3 bg-light text-end">
                    <button wire:click="closeModal" class="btn btn-light mb-0 me-2">Batal</button>
                    <button wire:click="initiateSave" class="btn bg-gradient-dark mb-0">Simpan Role</button>
                </div>
            </div>
        </div>
        @endif

        {{-- MODAL SECURITY CHECK (DOUBLE PASSWORD) --}}
        @if($showPinModal)
        <div class="fixed-top w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.8); z-index: 10000;">
            <div class="card shadow-lg text-center" style="width: 350px;">
                <div class="card-header bg-danger text-white p-3">
                    <i class="material-icons text-3xl mb-2">lock</i>
                    <h5 class="text-white mb-0">Keamanan Tingkat Tinggi</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-sm mb-3">Area ini dilindungi. Masukkan <strong>Password Login</strong> Anda untuk konfirmasi perubahan.</p>
                    <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control text-center" wire:model="security_pin_input">
                    </div>
                    @error('security_pin_input') <span class="text-danger text-xs d-block mb-2">{{ $message }}</span> @enderror
                    
                    <button wire:click="verifyPinAndExecute" class="btn bg-gradient-danger w-100 mb-2">Verifikasi & Lanjutkan</button>
                    <button wire:click="$set('showPinModal', false)" class="btn btn-link text-secondary w-100">Batal</button>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>