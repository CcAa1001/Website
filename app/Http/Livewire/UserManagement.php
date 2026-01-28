<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Role;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserManagement extends Component
{
    use WithPagination;

    // Properties
    public $name, $email, $role_id, $password, $userId;
    public $isEdit = false;
    
    // Pagination Theme
    protected $paginationTheme = 'bootstrap';

    // Validation Rules
    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'email' => [
                'required', 
                'email', 
                Rule::unique('users', 'email')->ignore($this->userId)
            ],
            'role_id' => 'required|exists:roles,id',
            'password' => $this->isEdit ? 'nullable|min:6' : 'required|min:6',
        ];
    }

    public function render()
    {
        $users = User::where('tenant_id', auth()->user()->tenant_id)
                     ->with('role')
                     ->latest()
                     ->paginate(10);
                     
        $roles = Role::where('tenant_id', auth()->user()->tenant_id)->get();

        return view('livewire.user-management', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->dispatch('open-modal'); // Trigger JS jika diperlukan
    }

    public function store()
    {
        $this->validate();
        
        $currentUser = auth()->user();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->role_id,
            'password' => Hash::make($this->password),
            'tenant_id' => $currentUser->tenant_id,
            'outlet_id' => $currentUser->outlet_id, // Asumsi 1 outlet default
            'is_active' => true,
        ]);

        session()->flash('message', 'Karyawan berhasil ditambahkan.');
        
        $this->dispatch('close-modal'); // Menutup modal otomatis
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
        $this->password = ''; // Kosongkan password demi keamanan
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate();
        
        $user = User::findOrFail($this->userId);
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->role_id,
        ];
        
        // Update password hanya jika diisi
        if(!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);

        session()->flash('message', 'Data karyawan diperbarui.');
        
        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function delete($id)
    {
        if($id == auth()->id()) {
            session()->flash('error', 'Anda tidak bisa menghapus akun sendiri.');
            return;
        }

        User::find($id)->delete();
        session()->flash('message', 'Karyawan dihapus.');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->role_id = '';
        $this->password = '';
        $this->userId = null;
        $this->isEdit = false;
    }
}