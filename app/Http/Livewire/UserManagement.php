<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserManagement extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    // Properties
    public $userId;
    public $name, $email, $phone, $role_id;
    public $password, $password_confirmation;
    public $avatar, $currentAvatar;
    public $is_active = true;

    // UI States
    public $showModal = false;
    public $modalMode = 'create';
    public $search = '';
    public $filterRole = '';

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        // // Pastikan hanya admin/manager yang bisa akses
        // if (!auth()->check() || !in_array(auth()->user()->role->name ?? '', ['admin', 'manager', 'super_admin'])) {
        //     return redirect()->route('dashboard');
        // }
    }

    public function render()
    {
        $user = auth()->user();

        $query = User::where('tenant_id', $user->tenant_id)
            ->with('role');

        // Filter Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Filter Role
        if ($this->filterRole) {
            $query->where('role_id', $this->filterRole);
        }

        $users = $query->latest()->paginate(10);
        $roles = Role::where('tenant_id', $user->tenant_id)->orWhereNull('tenant_id')->get(); // Ambil role default & custom

        return view('livewire.user-management', [
            'users' => $users,
            'roles' => $roles
        ])->layout('layouts.app', ['activePage' => 'user-management', 'titlePage' => 'Kelola Karyawan']);
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
        $user = User::findOrFail($id);
        
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role_id = $user->role_id;
        $this->is_active = $user->is_active;
        $this->currentAvatar = $user->avatar_url;

        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|min:3',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->userId)->whereNull('deleted_at')],
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|numeric',
        ];

        // Validasi Password hanya jika create atau jika field diisi saat edit
        if ($this->modalMode === 'create' || !empty($this->password)) {
            $rules['password'] = 'required|min:6|confirmed';
        }

        $this->validate($rules);

        $currentUser = auth()->user();

        $data = [
            'tenant_id' => $currentUser->tenant_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role_id' => $this->role_id,
            'is_active' => $this->is_active,
        ];

        // Handle Password
        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        // Handle Avatar
        if ($this->avatar) {
            if ($this->userId && $this->currentAvatar) {
                Storage::disk('public')->delete($this->currentAvatar);
            }
            $data['avatar_url'] = $this->avatar->store('avatars', 'public');
        }

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $user->update($data);
            session()->flash('message', 'Data karyawan berhasil diperbarui.');
        } else {
            // Default outlet sama dengan pembuat (bisa diubah nanti jika perlu multi-outlet)
            $data['outlet_id'] = $currentUser->outlet_id; 
            User::create($data);
            session()->flash('message', 'Karyawan baru berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        if ($id == auth()->id()) {
            session()->flash('error', 'Anda tidak bisa menghapus akun sendiri!');
            return;
        }

        $user = User::findOrFail($id);
        $user->delete();
        session()->flash('message', 'Karyawan berhasil dihapus (Non-aktif).');
    }

    public function toggleStatus($id)
    {
        if ($id == auth()->id()) return;
        
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['userId', 'name', 'email', 'phone', 'role_id', 'password', 'password_confirmation', 'avatar', 'currentAvatar']);
        $this->is_active = true;
    }
}