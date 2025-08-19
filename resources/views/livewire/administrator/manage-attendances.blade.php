<div x-data="{}">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col space-y-4 md:flex-row md:justify-between md:items-center md:space-y-0">
            <div>
                <flux:heading size="xl">Kelola Pengaturan Kehadiran</flux:heading>
                <flux:subheading>Atur lokasi, jam kerja, dan hari kerja untuk karyawan</flux:subheading>
            </div>
            <flux:button x-on:click="$wire.resetForm(); $flux.modal('create-attendance').show()" variant="primary" icon="plus" class="w-full md:w-auto">
                Tambah
            </flux:button>
        </div>

        <!-- Success/Error Messages -->
        @if (session('message'))
            <flux:callout variant="success" dismissible heading="{{ session('message') }}" />
        @endif

        @if (session('error'))
            <flux:callout variant="danger" dismissible heading="{{ session('message') }}" />
        @endif

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <flux:heading size="sm" class="mb-3">Filter & Pencarian</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:input
                    wire:model.live="search"
                    placeholder="Cari berdasarkan nama, username, atau email karyawan..."
                    icon="magnifying-glass"
                />
                <flux:select wire:model.live="locationFilter" placeholder="Filter berdasarkan lokasi">
                    <flux:select.option value="">Semua Lokasi</flux:select.option>
                    @foreach($locations as $location)
                        <flux:select.option value="{{ $location->id }}">{{ $location->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="statusFilter" placeholder="Filter berdasarkan status">
                    <flux:select.option value="">Semua Status</flux:select.option>
                    <flux:select.option value="active">Aktif</flux:select.option>
                    <flux:select.option value="inactive">Tidak Aktif</flux:select.option>
                </flux:select>
            </div>
        </div>

        <!-- Attendances Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                            Karyawan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                            Lokasi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                            Jam Kerja
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                            Hari Kerja
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-zinc-200">
                    @forelse($attendances as $attendance)
                        <tr wire:key="attendance-{{ $attendance->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <flux:avatar size="sm">{{ $attendance->user->initials() }}</flux:avatar>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-zinc-900">
                                            {{ $attendance->user->name }}
                                        </div>
                                        <div class="text-sm text-zinc-500">
                                            {{ $attendance->user->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-900">{{ $attendance->location->name }}</div>
                                <div class="text-sm text-zinc-500">{{ $attendance->location->address }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-900">
                                    {{ $attendance->getFormattedClockInTime() }} - {{ $attendance->getFormattedClockOutTime() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-900">{{ $attendance->getWorkDaysText() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:badge
                                    size="sm"
                                    color="{{ $attendance->is_active ? 'green' : 'yellow' }}"
                                >
                                    {{ $attendance->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex flex-col md:flex-row items-end md:items-center gap-2 justify-end">
                                    <flux:button x-on:click="$wire.setEditAttendance({{ $attendance->id }}); $flux.modal('edit-attendance').show()" size="sm" variant="ghost" icon="pencil" class="w-full md:w-auto">
                                        <span class="md:hidden">Edit {{ $attendance->user->name }}</span>
                                        <span class="hidden md:inline">Edit</span>
                                    </flux:button>
                                    <flux:button x-on:click="$wire.setDeleteAttendance({{ $attendance->id }}); $flux.modal('delete-attendance').show()" size="sm" variant="danger" icon="trash" class="w-full md:w-auto">
                                        <span class="md:hidden">Hapus {{ $attendance->user->name }}</span>
                                        <span class="hidden md:inline">Hapus</span>
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-zinc-500">
                                    <flux:icon.clock class="mx-auto size-12 mb-4 text-zinc-300" />
                                    <h3 class="text-sm font-medium">Tidak ada pengaturan kehadiran ditemukan</h3>
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
            {{ $attendances->links() }}
        </div>
    </div>

    <!-- Create Attendance Modal -->
    <flux:modal name="create-attendance" class="w-full max-w-lg mx-auto">
        <form wire:submit.prevent="createAttendance" class="space-y-6">
            <div>
                <flux:heading size="lg">Buat Pengaturan Kehadiran Baru</flux:heading>
                <flux:subheading>Atur lokasi, jam kerja, dan hari kerja untuk karyawan</flux:subheading>
            </div>

            <flux:select
                wire:model="userId"
                label="Pilih Karyawan"
                placeholder="Pilih karyawan"
                required
            >
                @foreach($users as $user)
                    <flux:select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->username }})</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select
                wire:model="locationId"
                label="Pilih Lokasi"
                placeholder="Pilih lokasi kerja"
                required
            >
                @foreach($locations as $location)
                    <flux:select.option value="{{ $location->id }}">{{ $location->name }} - {{ $location->address }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="grid grid-cols-2 gap-4">
                <flux:input
                    wire:model="clockInTime"
                    label="Jam Masuk Kerja"
                    type="time"
                    required
                />
                <flux:input
                    wire:model="clockOutTime"
                    label="Jam Keluar Kerja"
                    type="time"
                    required
                />
            </div>

            <div>
                <flux:heading size="sm" class="mb-3">Hari Kerja</flux:heading>
                <div class="grid grid-cols-7 gap-2">
                    @php
                        $days = [
                            1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam',
                            5 => 'Jum', 6 => 'Sab', 7 => 'Min'
                        ];
                    @endphp
                    @foreach($days as $dayNum => $dayName)
                        <flux:button
                            type="button"
                            variant="{{ in_array($dayNum, $workDays) ? 'primary' : 'ghost' }}"
                            size="sm"
                            wire:click="toggleWorkDay({{ $dayNum }})"
                            class="w-full"
                        >
                            {{ $dayName }}
                        </flux:button>
                    @endforeach
                </div>
            </div>

            <flux:switch wire:model="isActive" label="Aktif" />

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

    <!-- Edit Attendance Modal -->
    <flux:modal name="edit-attendance" class="w-full max-w-lg mx-auto">
        <form wire:submit.prevent="updateAttendance" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Pengaturan Kehadiran</flux:heading>
                <flux:subheading>Perbarui lokasi, jam kerja, dan hari kerja karyawan</flux:subheading>
            </div>

            <flux:select
                wire:model="userId"
                label="Pilih Karyawan"
                placeholder="Pilih karyawan"
                required
            >
                @foreach($users as $user)
                    <flux:select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->username }})</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select
                wire:model="locationId"
                label="Pilih Lokasi"
                placeholder="Pilih lokasi kerja"
                required
            >
                @foreach($locations as $location)
                    <flux:select.option value="{{ $location->id }}">{{ $location->name }} - {{ $location->address }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="grid grid-cols-2 gap-4">
                <flux:input
                    wire:model="clockInTime"
                    label="Jam Masuk Kerja"
                    type="time"
                    required
                />
                <flux:input
                    wire:model="clockOutTime"
                    label="Jam Keluar Kerja"
                    type="time"
                    required
                />
            </div>

            <div>
                <flux:heading size="sm" class="mb-3">Hari Kerja</flux:heading>
                <div class="grid grid-cols-7 gap-2">
                    @php
                        $days = [
                            1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam',
                            5 => 'Jum', 6 => 'Sab', 7 => 'Min'
                        ];
                    @endphp
                    @foreach($days as $dayNum => $dayName)
                        <flux:button
                            type="button"
                            variant="{{ in_array($dayNum, $workDays) ? 'primary' : 'ghost' }}"
                            size="sm"
                            wire:click="toggleWorkDay({{ $dayNum }})"
                            class="w-full"
                        >
                            {{ $dayName }}
                        </flux:button>
                    @endforeach
                </div>
            </div>

            <flux:switch wire:model="isActive" label="Aktif" />

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

    <!-- Delete Attendance Modal -->
    <flux:modal name="delete-attendance" class="w-full max-w-lg mx-auto">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Hapus Pengaturan Kehadiran</flux:heading>
                <flux:subheading>Apakah Anda yakin ingin menghapus pengaturan kehadiran ini?</flux:subheading>
            </div>

            @if($selectedAttendance)
                <div class="bg-zinc-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <flux:avatar size="sm">{{ $selectedAttendance->user->initials() }}</flux:avatar>
                        <div class="ml-3">
                            <div class="text-sm font-medium">{{ $selectedAttendance->user->name }}</div>
                            <div class="text-sm text-zinc-500">{{ $selectedAttendance->location->name }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <flux:callout variant="danger">
                Tindakan ini tidak dapat dibatalkan. Pengaturan kehadiran akan dihapus secara permanen dari sistem.
            </flux:callout>

            <div class="flex gap-3">
                <flux:spacer />
                <flux:button wire:click="deleteAttendance" variant="danger" wire:loading.attr="disabled">
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
