<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class StoreSettings extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $phone;
    public $address;
    public $logo;
    public $newLogo;

    public function mount()
    {
        // Ambil data dari Tenant user yang sedang login
        $user = Auth::user();
        $tenant = $user->tenant;

        if ($tenant) {
            $this->name = $tenant->name;
            $this->email = $tenant->email;
            $this->phone = $tenant->phone; // Pastikan kolom ini ada di DB tenants
            $this->address = $tenant->address; // Pastikan kolom ini ada di DB tenants
        } else {
            // Default jika belum ada tenant
            $this->name = 'Nama Restoran';
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $tenant = $user->tenant;

        if ($tenant) {
            $tenant->update([
                'name' => $this->name,
                // 'phone' => $this->phone, // Uncomment jika kolom ada
                // 'address' => $this->address, // Uncomment jika kolom ada
            ]);

            session()->flash('message', 'Pengaturan toko berhasil disimpan.');
        } else {
            session()->flash('error', 'Data Tenant tidak ditemukan.');
        }
    }

    public function render()
    {
        return view('livewire.store-settings');
    }
}