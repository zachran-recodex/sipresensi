<div x-data="{}">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col space-y-4 md:flex-row md:justify-between md:items-center md:space-y-0">
            <div>
                <flux:heading size="xl">Kelola Pengguna</flux:heading>
                <flux:subheading>Buat, edit, hapus pengguna dan kelola peran mereka</flux:subheading>
            </div>
            <flux:button x-on:click="$wire.resetForm(); $flux.modal('create-user').show()" variant="primary" icon="plus" class="w-full md:w-auto">
                Tambah Pengguna
            </flux:button>
        </div>

        <!-- View Mode Toggle -->
        @if(auth()->user()->hasRole('super admin'))
            <div class="flex space-x-1 p-1 bg-zinc-100 rounded-lg w-fit">
                <flux:button
                    wire:click="setViewMode('users')"
                    variant="{{ $viewMode === 'users' ? 'primary' : 'ghost' }}"
                    size="sm"
                >
                    Pengguna Biasa
                </flux:button>
                <flux:button
                    wire:click="setViewMode('super-admin')"
                    variant="{{ $viewMode === 'super-admin' ? 'primary' : 'ghost' }}"
                    size="sm"
                >
                    Super Admin
                </flux:button>
            </div>
        @endif

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
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <flux:heading size="sm" class="mb-3">Filter & Pencarian</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input
                    wire:model.live="search"
                    placeholder="Cari pengguna berdasarkan nama, username, atau email..."
                    icon="magnifying-glass"
                />
                @if($viewMode === 'users')
                    <flux:select wire:model.live="roleFilter" placeholder="Filter berdasarkan peran">
                        <flux:select.option value="">Semua Peran</flux:select.option>
                        @foreach($roles as $role)
                            <flux:select.option value="{{ $role->name }}">{{ ucfirst($role->name) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
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
                                            color="{{ $role->name === 'super admin' ? 'red' : ($role->name === 'admin' ? 'amber' : 'blue') }}"
                                        >
                                            {{ ucfirst($role->name) }}
                                        </flux:badge>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex flex-col md:flex-row items-end md:items-center gap-2 justify-end">
                                    @if($viewMode === 'super-admin')
                                        <!-- Super Admin users - only super admin can manage them -->
                                        @if(auth()->user()->hasRole('super admin'))
                                            <flux:button x-on:click="$wire.setEditUser({{ $user->id }}); $flux.modal('edit-user').show()" size="sm" variant="ghost" icon="pencil" class="w-full md:w-auto">
                                                <span class="md:hidden">Edit {{ $user->name }}</span>
                                                <span class="hidden md:inline">Edit</span>
                                            </flux:button>
                                            <flux:button x-on:click="$wire.setDeleteUser({{ $user->id }}); $flux.modal('delete-user').show()" size="sm" variant="danger" icon="trash" class="w-full md:w-auto">
                                                <span class="md:hidden">Hapus {{ $user->name }}</span>
                                                <span class="hidden md:inline">Hapus</span>
                                            </flux:button>
                                        @else
                                            <span class="text-xs text-zinc-400">Tidak ada akses</span>
                                        @endif
                                    @else
                                        <!-- Regular users -->
                                        @if(auth()->user()->hasRole('super admin') || !$user->hasRole('super admin'))
                                            <flux:button x-on:click="$wire.setEditUser({{ $user->id }}); $flux.modal('edit-user').show()" size="sm" variant="ghost" icon="pencil" class="w-full md:w-auto">
                                                <span class="md:hidden">Edit {{ $user->name }}</span>
                                                <span class="hidden md:inline">Edit</span>
                                            </flux:button>
                                            <flux:button x-on:click="$wire.setDeleteUser({{ $user->id }}); $flux.modal('delete-user').show()" size="sm" variant="danger" icon="trash" class="w-full md:w-auto">
                                                <span class="md:hidden">Hapus {{ $user->name }}</span>
                                                <span class="hidden md:inline">Hapus</span>
                                            </flux:button>
                                        @else
                                            <span class="text-xs text-zinc-400">Tidak ada akses</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="text-zinc-500">
                                    <flux:icon.users class="mx-auto size-12 mb-4 text-zinc-300" />
                                    <h3 class="text-sm font-medium">
                                        @if($viewMode === 'super-admin')
                                            Tidak ada super admin ditemukan
                                        @else
                                            Tidak ada pengguna ditemukan
                                        @endif
                                    </h3>
                                    <p class="text-sm">Coba sesuaikan kriteria pencarian atau filter Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div>
            {{ $users->links() }}
        </div>
    </div>

    <!-- Create User Modal -->
    <flux:modal name="create-user" class="w-full max-w-2xl mx-4">
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
                    <span wire:loading.remove>Buat</span>
                    <span wire:loading>Memproses...</span>
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>

    <!-- Edit User Modal -->
    <flux:modal name="edit-user" class="w-full max-w-2xl mx-4">
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
                    <span wire:loading.remove>Perbarui</span>
                    <span wire:loading>Memproses...</span>
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>

    <!-- Delete User Modal -->
    <flux:modal name="delete-user" class="w-full max-w-lg mx-4">
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
                    <span wire:loading.remove>Hapus</span>
                    <span wire:loading>Menghapus...</span>
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
