<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleManager extends Component
{
    public $roles;
    public $name;
    public $selectedPermissions = [];
    public $roleId;
    
    // Security PIN State
    public $security_pin_input = '';
    public $showPinModal = false;
    public $pendingAction = null; // 'save' or 'delete'
    public $pendingId = null;

    public $showModal = false;
    public $modalMode = 'create';

    // Daftar Menu/Permission yang tersedia di sistem
    public $availablePermissions = [
        'dashboard' => 'Akses Dashboard',
        'pos' => 'Mesin Kasir (POS)',
        'orders' => 'Manajemen Pesanan',
        'products' => 'Manajemen Produk',
        'inventory' => 'Stok & Inventaris',
        'tables' => 'Meja & QR',
        'transactions' => 'Laporan Keuangan',
        'customers' => 'Data Pelanggan',
        'users' => 'Kelola Karyawan',
        'roles' => 'Kelola Role & Akses',
        'settings' => 'Pengaturan Toko',
    ];

public function mount()
    {
        $user = auth()->user();

        // 1. Cek apakah user login
        if (!$user) {
            return redirect()->route('login');
        }

        // 2. Ambil Nama dan Slug Role (ubah ke huruf kecil semua biar aman)
        $roleName = strtolower($user->role->name ?? '');
        $roleSlug = strtolower($user->role->slug ?? '');

        // 3. Daftar Role yang DIPERBOLEHKAN masuk menu ini
        $allowedRoles = [
            'admin', 
            'super admin', 
            'super_admin', 
            'master',
            'owner'
        ];

        // 4. Cek apakah Role user ada di daftar yang boleh
        // Cek berdasarkan Nama ATAU Slug (salah satu cocok, boleh masuk)
        if (!in_array($roleName, $allowedRoles) && !in_array($roleSlug, $allowedRoles)) {
            
            // Debugging (Opsional): Uncomment baris bawah ini kalau masih gagal, 
            // nanti akan muncul tulisan role anda sebenarnya apa di layar.
            // dd("Role Anda saat ini terdeteksi sebagai: " . $roleName . " / Slug: " . $roleSlug);

            session()->flash('error', 'Akses ditolak. Anda tidak memiliki izin.');
            return redirect()->route('dashboard');
        }
    }
    public function render()
    {
        $this->roles = Role::all();
        return view('livewire.role-manager')
            ->layout('layouts.app', ['activePage' => 'roles', 'titlePage' => 'Role Management']);
    }

    // --- ACTIONS ---

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        // Decode JSON permissions
        $this->selectedPermissions = json_decode($role->permissions, true) ?? [];
        
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    // --- SECURITY LAYER: DOUBLE PASSWORD (PIN) ---

    public function initiateSave()
    {
        $this->validate([
            'name' => 'required|min:3|unique:roles,name,' . $this->roleId
        ]);

        // Cek apakah user punya PIN. Jika tidak, minta buat dulu atau gunakan password login (opsional)
        // Disini kita asumsikan untuk aksi sensitif di Role Manager, butuh konfirmasi PIN
        $this->pendingAction = 'save';
        $this->showPinModal = true;
        $this->security_pin_input = '';
    }

    public function initiateDelete($id)
    {
        $this->pendingId = $id;
        $this->pendingAction = 'delete';
        $this->showPinModal = true;
        $this->security_pin_input = '';
    }

    public function verifyPinAndExecute()
    {
        $user = auth()->user();

        // Skenario: Menggunakan Password Login sebagai "Double Auth" jika PIN belum diset
        // Atau Anda bisa cek kolom 'security_pin' jika sudah dibuat fiturnya.
        // Di sini saya gunakan Password Login sebagai verifikasi keamanan tambahan.
        
        if (!Hash::check($this->security_pin_input, $user->password)) {
            $this->addError('security_pin_input', 'Password salah. Akses ditolak.');
            return;
        }

        // Jika lolos verifikasi, jalankan aksi
        if ($this->pendingAction === 'save') {
            $this->executeSave();
        } elseif ($this->pendingAction === 'delete') {
            $this->executeDelete();
        }

        $this->showPinModal = false;
        $this->security_pin_input = '';
    }

    // --- REAL EXECUTION ---

    private function executeSave()
    {
        $data = [
            'name' => $this->name,
            'permissions' => json_encode($this->selectedPermissions), // Simpan sebagai JSON
            'tenant_id' => auth()->user()->tenant_id
        ];

        if ($this->roleId) {
            Role::find($this->roleId)->update($data);
            session()->flash('message', 'Role berhasil diperbarui.');
        } else {
            Role::create($data);
            session()->flash('message', 'Role baru berhasil dibuat.');
        }

        $this->closeModal();
    }

    private function executeDelete()
    {
        $role = Role::findOrFail($this->pendingId);
        
        // Prevent deleting admin role or role with users
        if (in_array(strtolower($role->name), ['admin', 'super_admin'])) {
            session()->flash('error', 'Role Admin Utama tidak bisa dihapus!');
            return;
        }
        
        if ($role->users()->count() > 0) {
            session()->flash('error', 'Role ini masih digunakan oleh karyawan. Pindahkan mereka dulu.');
            return;
        }

        $role->delete();
        session()->flash('message', 'Role berhasil dihapus.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['name', 'selectedPermissions', 'roleId']);
    }
}