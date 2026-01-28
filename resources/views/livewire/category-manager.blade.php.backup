<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3">
                <h6>Tambah Kategori</h6>
                <form wire:submit.prevent="save">
                    <div class="input-group input-group-outline my-3 {{ $name ? 'is-filled' : '' }}">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" wire:model="name">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-3">
                <h6>Daftar Kategori</h6>
                <ul class="list-group">
                    @foreach($categories as $cat)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $cat->name }}
                        <button wire:click="delete({{ $cat->id }})" class="btn btn-link text-danger mb-0">Hapus</button>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>