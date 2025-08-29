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

    // View mode: 'users' or 'super-admin'
    public $viewMode = 'users';

    protected $rules = [
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ];

    protected $listeners = [
        'userCreated' => '$refresh',
        'userUpdated' => '$refresh',
        'userDeleted' => '$refresh',
        'faceEnrollmentDeleted' => '$refresh',
        'searchUpdated' => '$refresh',
        'roleFilterUpdated' => '$refresh',
        'modal.close' => 'onModalClose',
    ];

    public function render()
    {
        if ($this->viewMode === 'super-admin') {
            // Show only super admin users
            $users = User::with(['roles', 'faceEnrollment'])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'super admin');
                })
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('username', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                })
                ->latest()
                ->paginate(10);
        } else {
            // Show regular users (excluding super admin)
            $users = User::with(['roles', 'faceEnrollment'])
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'super admin');
                })
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
        }

        // Filter roles for admin users - they can't assign super admin role
        $roles = auth()->user()->hasRole('super admin')
            ? Role::all()
            : Role::where('name', '!=', 'super admin')->get();

        return view('livewire.administrator.manage-users', compact('users', 'roles'));
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        $this->resetPage();
        $this->reset(['search', 'roleFilter']);
    }

    public function setEditUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        // Prevent admin from editing super admin users
        if (auth()->user()->hasRole('admin') && ! auth()->user()->hasRole('super admin')) {
            if ($user->hasRole('super admin')) {
                session()->flash('error', 'Anda tidak memiliki izin untuk mengedit super admin.');

                return;
            }
        }

        $this->resetForm();
        $this->selectedUserId = $user->id;
        $this->selectedUser = $user;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();

        // Update validation rules for edit (exclude current user)
        $this->rules['username'] = 'required|string|max:255|unique:users,username,'.$user->id;
        $this->rules['email'] = 'required|email|max:255|unique:users,email,'.$user->id;
        $this->rules['password'] = 'nullable|string|min:8|confirmed';
    }

    public function setDeleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        // Prevent admin from deleting super admin users
        if (auth()->user()->hasRole('admin') && ! auth()->user()->hasRole('super admin')) {
            if ($user->hasRole('super admin')) {
                session()->flash('error', 'Anda tidak memiliki izin untuk menghapus super admin.');

                return;
            }
        }

        $this->selectedUserId = $user->id;
        $this->selectedUser = $user;
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

        // Assign selected roles or default to karyawan if none selected
        if (! empty($this->selectedRoles)) {
            $user->syncRoles($this->selectedRoles);
        } else {
            $user->assignRole('karyawan');
        }

        $this->resetForm();
        session()->flash('message', 'User berhasil dibuat.');
        $this->dispatch('userCreated');
        $this->dispatch('close-modal', 'create-user');
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

        // Update user roles
        if (! empty($this->selectedRoles)) {
            $user->syncRoles($this->selectedRoles);
        }

        $this->resetForm();
        session()->flash('message', 'User berhasil diperbarui.');
        $this->dispatch('userUpdated');
        $this->dispatch('close-modal', 'edit-user');
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
        session()->flash('message', 'User berhasil dihapus.');
        $this->dispatch('userDeleted');
        $this->dispatch('close-modal', 'delete-user');
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
        $this->viewMode = 'users';

        // Reset validation rules
        $this->rules['username'] = 'required|string|max:255|unique:users,username';
        $this->rules['email'] = 'required|email|max:255|unique:users,email';
        $this->rules['password'] = 'required|string|min:8|confirmed';

        $this->resetErrorBag();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->dispatch('searchUpdated');
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
        $this->dispatch('roleFilterUpdated');
    }

    public function onModalClose(): void
    {
        // Reset form when modal is closed
        $this->resetForm();
    }

    public function deleteFaceEnrollment(int $userId): void
    {
        try {
            $user = User::findOrFail($userId);

            // Get face enrollment
            $faceEnrollment = $user->faceEnrollment()->where('is_active', true)->first();

            if (! $faceEnrollment) {
                session()->flash('error', 'Pengguna tidak memiliki data wajah yang aktif.');

                return;
            }

            // Delete from Biznet API first
            try {
                $faceService = app(\App\Services\FaceRecognitionService::class);
                $faceService->deleteFace($faceEnrollment->biznet_user_id);
            } catch (\Exception $e) {
                // Log but don't fail if API deletion fails
                \Log::warning('Failed to delete face from Biznet API', [
                    'biznet_user_id' => $faceEnrollment->biznet_user_id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Deactivate enrollment in database
            $faceEnrollment->update(['is_active' => false]);

            session()->flash('message', "Data wajah untuk {$user->name} berhasil dihapus. User dapat mendaftar ulang.");

            // Refresh the component
            $this->dispatch('faceEnrollmentDeleted');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data wajah: '.$e->getMessage());
        }
    }
}
