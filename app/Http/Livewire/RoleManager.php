<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str; // [PENTING] Jangan lupa import ini

class RoleManager extends Component
{
    public $roles;
    public $name;
    public $selectedPermissions = [];
    public $roleId;
    
    // Security PIN State
    public $security_pin_input = '';
    public $showPinModal = false;
    public $pendingAction = null; 
    public $pendingId = null;

    public $showModal = false;
    public $modalMode = 'create';

    // Daftar Permission
    public $availablePermissions = [
        'dashboard' => 'Akses Dashboard',
        'pos' => 'Mesin Kasir (POS)',
        'orders' => 'Manajemen Pesanan (KDS)',
        'products' => 'Manajemen Produk',
        'inventory' => 'Stok & Inventaris',
        'categories' => 'Kategori Produk',
        'tables' => 'Meja & QR',
        'transactions' => 'Laporan & Transaksi',
        'users' => 'Kelola Karyawan',
        'roles' => 'Kelola Role & Akses',
        'settings' => 'Pengaturan Toko',
    ];

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        // Security Check Tambahan (Opsional)
        // $user = auth()->user();
        // $roleSlug = strtolower($user->role->slug ?? '');
        // if (!in_array($roleSlug, ['admin', 'super_admin'])) {
        //     return redirect()->route('dashboard');
        // }
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
        
        // Handle JSON/Array Permissions safely
        if (is_array($role->permissions)) {
            $this->selectedPermissions = $role->permissions;
        } else {
            $this->selectedPermissions = json_decode($role->permissions, true) ?? [];
        }
        
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    // --- SECURITY LAYER ---

    public function initiateSave()
    {
        $this->validate([
            'name' => [
                'required',
                'min:3',
                Rule::unique('roles', 'name')->ignore($this->roleId)
            ]
        ]);

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

        if (!Hash::check($this->security_pin_input, $user->password)) {
            $this->addError('security_pin_input', 'Password salah. Akses ditolak.');
            return;
        }

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
        $perms = $this->selectedPermissions ?? [];

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name), // [FIX] Generate Slug Otomatis
            'permissions' => $perms,
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
        
        if (in_array(strtolower($role->name), ['admin', 'super admin', 'super_admin'])) {
            session()->flash('error', 'Role Admin Utama tidak bisa dihapus!');
            $this->closeModal();
            return;
        }
        
        if ($role->users()->count() > 0) {
            session()->flash('error', 'Role ini masih digunakan oleh karyawan. Pindahkan mereka dulu.');
            $this->closeModal();
            return;
        }

        $role->delete();
        session()->flash('message', 'Role berhasil dihapus.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showPinModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['name', 'selectedPermissions', 'roleId', 'security_pin_input', 'pendingAction', 'pendingId']);
    }
}