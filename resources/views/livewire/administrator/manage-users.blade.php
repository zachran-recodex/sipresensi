<div x-data="{}">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <flux:heading size="xl">Kelola Pengguna</flux:heading>
                <p class="text-zinc-500">Buat, edit, hapus pengguna dan kelola peran mereka</p>
            </div>
            <flux:button x-on:click="$wire.resetForm(); $flux.modal('create-user').show()" variant="primary" icon="plus">
                Tambah Pengguna
            </flux:button>
        </div>

        <!-- Success/Error Messages -->
        @if (session('message'))
            <flux:callout variant="success" dismissible>
                {{ session('message') }}
            </flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger" dismissible>
                {{ session('error') }}
            </flux:callout>
        @endif

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input
                wire:model.live="search"
                placeholder="Cari pengguna berdasarkan nama, username, atau email..."
                icon="magnifying-glass"
            />
            <flux:select wire:model.live="roleFilter" placeholder="Filter berdasarkan peran">
                <flux:select.option value="">Semua Peran</flux:select.option>
                @foreach($roles as $role)
                    <flux:select.option value="{{ $role->name }}">{{ ucfirst($role->name) }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <!-- Users Table -->
        <div class="overflow-hidden bg-white shadow sm:rounded-lg border-zinc-200">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                            Pengguna
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                            Username
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                            Peran
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                            Dibuat
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-zinc-200">
                    @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <flux:avatar size="sm">{{ $user->initials() }}</flux:avatar>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-zinc-900">
                                            {{ $user->name }}
                                        </div>
                                        <div class="text-sm text-zinc-500">
                                            {{ $user->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900">
                                {{ $user->username }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <flux:badge
                                            size="sm"
                                            variant="{{ $role->name === 'super admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : 'neutral') }}"
                                        >
                                            {{ ucfirst($role->name) }}
                                        </flux:badge>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500">
                                {{ $user->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center gap-2 justify-end">
                                    @if(auth()->user()->hasRole('super admin') || !$user->hasRole('super admin'))
                                        <flux:button x-on:click="$wire.setSelectedUser({{ $user->id }}); $flux.modal('manage-roles').show()" size="sm" variant="ghost" icon="user-group">
                                            Peran
                                        </flux:button>
                                        <flux:button x-on:click="$wire.setEditUser({{ $user->id }}); $flux.modal('edit-user').show()" size="sm" variant="ghost" icon="pencil">
                                            Edit
                                        </flux:button>
                                        <flux:button x-on:click="$wire.setDeleteUser({{ $user->id }}); $flux.modal('delete-user').show()" size="sm" variant="danger" icon="trash">
                                            Hapus
                                        </flux:button>
                                    @else
                                        <span class="text-xs text-zinc-400">Tidak ada akses</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-zinc-500">
                                    <flux:icon.users class="mx-auto size-12 mb-4 text-zinc-300" />
                                    <h3 class="text-sm font-medium">Tidak ada pengguna ditemukan</h3>
                                    <p class="text-sm">Coba sesuaikan kriteria pencarian atau filter Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div>
            {{ $users->links() }}
        </div>
    </div>

    <!-- Create User Modal -->
    <flux:modal name="create-user" class="md:w-[32rem]">
        <form wire:submit.prevent="createUser" class="space-y-6">
            <div>
                <flux:heading size="lg">Buat Pengguna Baru</flux:heading>
                <flux:subheading>Tambahkan pengguna baru ke sistem</flux:subheading>
            </div>

            <flux:input
                wire:model="name"
                label="Nama Lengkap"
                placeholder="Masukkan nama lengkap"
                required
            />

            <flux:input
                wire:model="username"
                label="Username"
                placeholder="Masukkan username"
                required
            />

            <flux:input
                wire:model="email"
                label="Alamat Email"
                type="email"
                placeholder="Masukkan alamat email"
                required
            />

            <flux:input
                wire:model="password"
                label="Kata Sandi"
                type="password"
                placeholder="Masukkan kata sandi"
                required
            />

            <flux:input
                wire:model="password_confirmation"
                label="Konfirmasi Kata Sandi"
                type="password"
                placeholder="Konfirmasi kata sandi"
                required
            />

            <div class="flex gap-3">
                <flux:spacer />

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Buat Pengguna</span>
                    <span wire:loading>Memproses...</span>
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>

    <!-- Edit User Modal -->
    <flux:modal name="edit-user" class="md:w-[32rem]">
        <form wire:submit.prevent="updateUser" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Pengguna</flux:heading>
                <flux:subheading>Perbarui informasi pengguna</flux:subheading>
            </div>

            <flux:input
                wire:model="name"
                label="Nama Lengkap"
                placeholder="Masukkan nama lengkap"
                required
            />

            <flux:input
                wire:model="username"
                label="Username"
                placeholder="Masukkan username"
                required
            />

            <flux:input
                wire:model="email"
                label="Alamat Email"
                type="email"
                placeholder="Masukkan alamat email"
                required
            />

            <flux:input
                wire:model="password"
                label="Kata Sandi Baru (kosongkan untuk tetap menggunakan yang lama)"
                type="password"
                placeholder="Masukkan kata sandi baru"
            />

            <flux:input
                wire:model="password_confirmation"
                label="Konfirmasi Kata Sandi Baru"
                type="password"
                placeholder="Konfirmasi kata sandi baru"
            />

            <div class="flex gap-3">
                <flux:spacer />

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Perbarui Pengguna</span>
                    <span wire:loading>Memproses...</span>
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>

    <!-- Delete User Modal -->
    <flux:modal name="delete-user" class="md:w-[28rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Hapus Pengguna</flux:heading>
                <flux:subheading>Apakah Anda yakin ingin menghapus pengguna ini?</flux:subheading>
            </div>

            @if($selectedUser)
                <div class="bg-zinc-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <flux:avatar size="sm">{{ $selectedUser->initials() }}</flux:avatar>
                        <div class="ml-3">
                            <div class="text-sm font-medium">{{ $selectedUser->name }}</div>
                            <div class="text-sm text-zinc-500">{{ $selectedUser->email }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <flux:callout variant="danger">
                Tindakan ini tidak dapat dibatalkan. Pengguna akan dihapus secara permanen dari sistem.
            </flux:callout>

            <div class="flex gap-3">
                <flux:spacer />

                <flux:button wire:click="deleteUser" variant="danger" wire:loading.attr="disabled">
                    <span wire:loading.remove>Hapus Pengguna</span>
                    <span wire:loading>Menghapus...</span>
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    <!-- Manage Roles Modal -->
    <flux:modal name="manage-roles" class="md:w-[28rem]">
        <form wire:submit.prevent="updateUserRoles" class="space-y-6">
            <div>
                <flux:heading size="lg">Kelola Peran Pengguna</flux:heading>
                <flux:subheading>Tetapkan atau hapus peran dari pengguna ini</flux:subheading>
            </div>

            @if($selectedUser)
                <div class="bg-zinc-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <flux:avatar size="sm">{{ $selectedUser->initials() }}</flux:avatar>
                        <div class="ml-3">
                            <div class="text-sm font-medium">{{ $selectedUser->name }}</div>
                            <div class="text-sm text-zinc-500">{{ $selectedUser->email }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <flux:checkbox.group wire:model="selectedRoles" label="Pilih Peran">
                @foreach($roles as $role)
                    <flux:checkbox
                        value="{{ $role->name }}"
                        label="{{ ucfirst($role->name) }}"
                    />
                @endforeach
            </flux:checkbox.group>

            <div class="flex gap-3">
                <flux:spacer />

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Perbarui Peran</span>
                    <span wire:loading>Memproses...</span>
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>
</div>
