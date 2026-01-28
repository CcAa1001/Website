<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <p class="mb-0">Pengaturan Toko (Store Settings)</p>
                    </div>
                </div>
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success text-white mb-3">
                            {{ session('message') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="save">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="input-group input-group-outline my-3 is-filled">
                                    <label class="form-label">Nama Restoran / Toko</label>
                                    <input type="text" class="form-control" wire:model="name">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="input-group input-group-outline my-3 is-filled">
                                    <label class="form-label">Email Resmi</label>
                                    <input type="email" class="form-control" wire:model="email" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-group input-group-outline my-3 is-filled">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control" wire:model="phone">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="input-group input-group-outline my-3 is-filled">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <textarea class="form-control" wire:model="address" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn bg-gradient-dark mb-0">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>