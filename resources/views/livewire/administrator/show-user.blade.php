<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex flex-col space-y-4 md:flex-row md:justify-between md:items-center md:space-y-0">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <flux:button 
                    href="{{ route('administrator.manage-users') }}" 
                    variant="subtle" 
                    icon="arrow-left" 
                    size="sm"
                >
                    Kembali
                </flux:button>
                <flux:heading size="xl">Detail Pengguna</flux:heading>
            </div>
            <flux:subheading>Informasi lengkap akun dan data absensi</flux:subheading>
        </div>
    </div>

    <!-- User Basic Info -->
    <div class="bg-white border border-zinc-200 rounded-lg p-6">
        <div class="flex items-start gap-4">
            <flux:avatar size="xl">{{ $user->initials() }}</flux:avatar>
            <div class="flex-1">
                <div class="flex flex-col md:flex-row md:items-center gap-2 mb-4">
                    <h3 class="text-2xl font-semibold text-zinc-900">{{ $user->name }}</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($user->roles as $role)
                            <flux:badge
                                size="md"
                                color="{{ $role->name === 'super admin' ? 'red' : ($role->name === 'admin' ? 'amber' : 'blue') }}"
                            >
                                {{ ucfirst($role->name) }}
                            </flux:badge>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <span class="text-zinc-500 font-medium">Username:</span>
                        <div class="mt-1 font-semibold text-zinc-900">{{ $user->username }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Email:</span>
                        <div class="mt-1 font-semibold text-zinc-900">{{ $user->email }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Bergabung:</span>
                        <div class="mt-1 font-semibold text-zinc-900">{{ $user->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Terakhir Update:</span>
                        <div class="mt-1 font-semibold text-zinc-900">{{ $user->updated_at->format('d M Y H:i') }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Status Wajah:</span>
                        <div class="mt-1">
                            @if($user->hasRole('karyawan'))
                                @if($user->faceEnrollment)
                                    <flux:badge size="sm" color="green">
                                        <flux:icon.check-circle class="size-3" />
                                        Terdaftar
                                    </flux:badge>
                                @else
                                    <flux:badge size="sm" color="red">
                                        <flux:icon.x-circle class="size-3" />
                                        Belum Terdaftar
                                    </flux:badge>
                                @endif
                            @else
                                <span class="text-zinc-400">Tidak diperlukan</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Face Enrollment Details -->
    @if($user->hasRole('karyawan') && $user->faceEnrollment)
        <div class="bg-white border border-zinc-200 rounded-lg p-6">
            <flux:heading size="lg" class="mb-6">Detail Pendaftaran Wajah</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <span class="text-zinc-500 font-medium">Biznet User ID:</span>
                    <div class="mt-1 font-semibold text-zinc-900">{{ $user->faceEnrollment->biznet_user_id }}</div>
                </div>
                <div>
                    <span class="text-zinc-500 font-medium">Gallery ID:</span>
                    <div class="mt-1 font-semibold text-zinc-900">{{ $user->faceEnrollment->face_gallery_id }}</div>
                </div>
                <div>
                    <span class="text-zinc-500 font-medium">Tanggal Daftar:</span>
                    <div class="mt-1 font-semibold text-zinc-900">{{ $user->faceEnrollment->enrolled_at?->format('d M Y H:i') ?? 'N/A' }}</div>
                </div>
                <div>
                    <span class="text-zinc-500 font-medium">Status:</span>
                    <div class="mt-1">
                        <flux:badge size="sm" color="{{ $user->faceEnrollment->is_active ? 'green' : 'red' }}">
                            {{ $user->faceEnrollment->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </flux:badge>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Attendance Settings -->
    @if($user->hasRole('karyawan'))
        <div class="bg-white border border-zinc-200 rounded-lg p-6">
            <flux:heading size="lg" class="mb-6">Pengaturan Absensi</flux:heading>
            @if($user->attendance)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <span class="text-zinc-500 font-medium">Lokasi:</span>
                        <div class="mt-1 font-semibold text-zinc-900">{{ $user->attendance->location->name ?? 'Tidak ada' }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Alamat:</span>
                        <div class="mt-1 font-semibold text-zinc-900">{{ $user->attendance->location->address ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Jam Masuk:</span>
                        <div class="mt-1 font-semibold text-zinc-900">{{ $user->attendance->getFormattedClockInTime() }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Jam Keluar:</span>
                        <div class="mt-1 font-semibold text-zinc-900">{{ $user->attendance->getFormattedClockOutTime() }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Status:</span>
                        <div class="mt-1">
                            <flux:badge size="sm" color="{{ $user->attendance->is_active ? 'green' : 'red' }}">
                                {{ $user->attendance->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </flux:badge>
                        </div>
                    </div>
                    <div class="md:col-span-2 lg:col-span-3">
                        <span class="text-zinc-500 font-medium">Hari Kerja:</span>
                        <div class="mt-1 font-semibold text-zinc-900">{{ $user->attendance->getWorkDaysText() }}</div>
                    </div>
                </div>
            @else
                <div class="text-center py-12 text-zinc-500">
                    <flux:icon.calendar class="mx-auto size-12 mb-4 text-zinc-300" />
                    <h3 class="text-lg font-medium mb-2">Belum Ada Pengaturan Absensi</h3>
                    <p>Pengguna ini belum memiliki pengaturan absensi.</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Recent Attendance Records -->
    @if($user->hasRole('karyawan'))
        <div class="bg-white border border-zinc-200 rounded-lg p-6">
            <flux:heading size="lg" class="mb-6">Riwayat Absensi Terbaru</flux:heading>
            @if($user->attendanceRecords && $user->attendanceRecords->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Masuk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Keluar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Lokasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-zinc-200">
                            @foreach($user->attendanceRecords->take(15) as $record)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900">
                                        {{ $record->attendance_date->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900">
                                        {{ $record->clock_in_time ? $record->clock_in_time->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900">
                                        {{ $record->clock_out_time ? $record->clock_out_time->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900">
                                        {{ $record->location->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900">
                                        @if(isset($record->is_approved))
                                            <flux:badge size="sm" color="{{ $record->is_approved ? 'green' : 'amber' }}">
                                                {{ $record->is_approved ? 'Disetujui' : 'Menunggu' }}
                                            </flux:badge>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($user->attendanceRecords->count() > 15)
                    <div class="mt-4 text-center">
                        <flux:text size="sm" class="text-zinc-500">
                            Menampilkan 15 record terbaru dari {{ $user->attendanceRecords->count() }} total record
                        </flux:text>
                    </div>
                @endif
            @else
                <div class="text-center py-12 text-zinc-500">
                    <flux:icon.clock class="mx-auto size-12 mb-4 text-zinc-300" />
                    <h3 class="text-lg font-medium mb-2">Belum Ada Riwayat Absensi</h3>
                    <p>Pengguna ini belum memiliki riwayat absensi.</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Admin/Super Admin specific information -->
    @if($user->hasRole(['admin', 'super admin']))
        <div class="bg-white border border-zinc-200 rounded-lg p-6">
            <flux:heading size="lg" class="mb-6">Informasi Administrator</flux:heading>
            <div class="text-center py-8 text-zinc-500">
                <flux:icon.shield-check class="mx-auto size-12 mb-4 text-zinc-300" />
                <h3 class="text-lg font-medium mb-2">Akun Administrator</h3>
                <p>Pengguna ini memiliki akses administrator dan tidak memerlukan absensi atau pendaftaran wajah.</p>
            </div>
        </div>
    @endif
</div>