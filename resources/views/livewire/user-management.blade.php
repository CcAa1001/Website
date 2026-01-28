<div class="container-fluid py-4">
    
    {{-- Notifikasi --}}
    @if (session()->has('message'))
        <div class="alert alert-success text-white px-4 py-2 mb-4" role="alert">
            <i class="material-icons text-sm me-2">check_circle</i> {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger text-white px-4 py-2 mb-4" role="alert">
            <i class="material-icons text-sm me-2">error</i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-4">
                        <h6 class="text-white text-capitalize ps-3 mb-0">Kelola Karyawan</h6>
                        <button wire:click="create" class="btn bg-white text-primary mb-0" data-bs-toggle="modal" data-bs-target="#userModal">
                            <i class="material-icons text-sm">person_add</i> Tambah Karyawan
                        </button>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Nama</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $user->role->name ?? 'No Role' }}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-success">Active</span>
                                    </td>
                                    <td class="align-middle">
                                        <button wire:click="edit('{{ $user->id }}')" class="btn btn-link text-dark px-3 mb-0" data-bs-toggle="modal" data-bs-target="#userModal">
                                            <i class="material-icons text-sm me-2">edit</i>Edit
                                        </button>
                                        <button wire:click="delete('{{ $user->id }}')" class="btn btn-link text-danger px-3 mb-0" onclick="confirm('Yakin ingin menghapus?') || event.stopImmediatePropagation()">
                                            <i class="material-icons text-sm me-2">delete</i>Hapus
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL --}}
    <div class="modal fade" id="userModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? 'Edit Karyawan' : 'Tambah Karyawan Baru' }}</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                        
                        <div class="input-group input-group-outline my-3 {{ $name ? 'is-filled' : '' }}">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" wire:model="name">
                        </div>
                        @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        <div class="input-group input-group-outline my-3 {{ $email ? 'is-filled' : '' }}">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" wire:model="email">
                        </div>
                        @error('email') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        <div class="input-group input-group-static my-3">
                            <label>Role (Jabatan)</label>
                            <select class="form-control" wire:model="role_id">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('role_id') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        <div class="input-group input-group-outline my-3 {{ $password ? 'is-filled' : '' }}">
                            <label class="form-label">Password {{ $isEdit ? '(Kosongkan jika tidak diganti)' : '' }}</label>
                            <input type="password" class="form-control" wire:model="password">
                        </div>
                        @error('password') <span class="text-danger text-xs">{{ $message }}</span> @enderror

                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn bg-gradient-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    // Event listener untuk menutup modal secara otomatis dari Livewire
    window.addEventListener('close-modal', event => {
        var myModalEl = document.getElementById('userModal');
        var modal = bootstrap.Modal.getInstance(myModalEl);
        if (modal) { modal.hide(); } else { new bootstrap.Modal(myModalEl).hide(); }
        
        // Membersihkan backdrop jika tertinggal
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    });
</script>
@endpush