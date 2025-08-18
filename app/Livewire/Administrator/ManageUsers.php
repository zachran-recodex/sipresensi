<?php

namespace App\Livewire\Administrator;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class ManageUsers extends Component
{
    use WithPagination;

    public $selectedUserId;

    public $selectedUser;

    // Form fields
    public $name = '';

    public $username = '';

    public $email = '';

    public $password = '';

    public $password_confirmation = '';

    public $selectedRoles = [];

    // Search and filter
    public $search = '';

    public $roleFilter = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ];

    protected $listeners = [
        'userUpdated' => '$refresh',
        'modal.close' => 'onModalClose',
    ];

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('username', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->latest()
            ->paginate(10);

        $roles = Role::all();

        return view('livewire.administrator.manage-users', compact('users', 'roles'));
    }

    public function setEditUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->resetForm();
        $this->selectedUserId = $user->id;
        $this->selectedUser = $user;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;

        // Update validation rules for edit (exclude current user)
        $this->rules['username'] = 'required|string|max:255|unique:users,username,'.$user->id;
        $this->rules['email'] = 'required|email|max:255|unique:users,email,'.$user->id;
        $this->rules['password'] = 'nullable|string|min:8|confirmed';
    }

    public function setDeleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->selectedUserId = $user->id;
        $this->selectedUser = $user;
    }

    public function setSelectedUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->selectedUserId = $user->id;
        $this->selectedUser = $user;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    public function createUser(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        // Assign karyawan role by default
        $user->assignRole('karyawan');

        $this->resetForm();
        $this->dispatch('userCreated');
        $this->dispatch('close-modal', 'create-user');
        session()->flash('message', 'User berhasil dibuat.');
    }

    public function updateUser(): void
    {
        // Set proper validation rules for edit
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$this->selectedUserId,
            'email' => 'required|email|max:255|unique:users,email,'.$this->selectedUserId,
            'password' => 'nullable|string|min:8|confirmed',
        ];

        $this->validate($rules);

        $user = User::findOrFail($this->selectedUserId);

        $updateData = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
        ];

        if (! empty($this->password)) {
            $updateData['password'] = Hash::make($this->password);
        }

        $user->update($updateData);

        $this->resetForm();
        $this->dispatch('userUpdated');
        $this->dispatch('close-modal', 'edit-user');
        session()->flash('message', 'User berhasil diperbarui.');
    }

    public function deleteUser(): void
    {
        $user = User::findOrFail($this->selectedUserId);

        // Prevent deleting current user
        if ($user->id === auth()->user()->id) {
            session()->flash('error', 'Anda tidak dapat menghapus akun sendiri.');
            $this->resetForm();

            return;
        }

        $user->delete();

        $this->resetForm();
        $this->dispatch('userDeleted');
        $this->dispatch('close-modal', 'delete-user');
        session()->flash('message', 'User berhasil dihapus.');
    }

    public function updateUserRoles(): void
    {
        $user = User::findOrFail($this->selectedUserId);
        $user->syncRoles($this->selectedRoles);

        $this->resetForm();
        $this->dispatch('userUpdated');
        $this->dispatch('close-modal', 'manage-roles');
        session()->flash('message', 'Role user berhasil diperbarui.');
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->username = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedUserId = null;
        $this->selectedUser = null;
        $this->selectedRoles = [];

        // Reset validation rules
        $this->rules['username'] = 'required|string|max:255|unique:users,username';
        $this->rules['email'] = 'required|email|max:255|unique:users,email';
        $this->rules['password'] = 'required|string|min:8|confirmed';

        $this->resetErrorBag();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function onModalClose(): void
    {
        // Reset form when modal is closed
        $this->resetForm();
    }
}
