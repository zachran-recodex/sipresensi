<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Karyawan -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                    <flux:icon name="users" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <flux:heading size="sm" class="text-gray-600 dark:text-gray-300">Total Karyawan</flux:heading>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_karyawan']) }}</div>
                </div>
            </div>
        </div>

        <!-- Total Admin -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                    <flux:icon name="shield-check" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                    <flux:heading size="sm" class="text-gray-600 dark:text-gray-300">Total Admin</flux:heading>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_admin']) }}</div>
                </div>
            </div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
                    <flux:icon name="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <flux:heading size="sm" class="text-gray-600 dark:text-gray-300">Hadir Hari Ini</flux:heading>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['hadir_hari_ini']) }}</div>
                </div>
            </div>
        </div>

        <!-- Total Absensi Bulan Ini -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                    <flux:icon name="calendar-days" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                </div>
                <div class="ml-4">
                    <flux:heading size="sm" class="text-gray-600 dark:text-gray-300">Absensi Bulan Ini</flux:heading>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_absensi_bulan_ini']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <flux:heading size="lg" class="mb-4">Aksi Cepat</flux:heading>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ route('administrator.manage-users') }}" class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                    <flux:icon name="users" class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3" />
                    <span class="text-blue-700 dark:text-blue-300 font-medium">Kelola User</span>
                </a>
                <a href="{{ route('administrator.manage-locations') }}" class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                    <flux:icon name="map-pin" class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" />
                    <span class="text-green-700 dark:text-green-300 font-medium">Kelola Lokasi</span>
                </a>
                <a href="{{ route('administrator.attendance-reports') }}" class="flex items-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                    <flux:icon name="chart-bar" class="w-5 h-5 text-purple-600 dark:text-purple-400 mr-3" />
                    <span class="text-purple-700 dark:text-purple-300 font-medium">Laporan</span>
                </a>
                <a href="{{ route('administrator.manage-attendances') }}" class="flex items-center p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors">
                    <flux:icon name="clipboard-document-list" class="w-5 h-5 text-orange-600 dark:text-orange-400 mr-3" />
                    <span class="text-orange-700 dark:text-orange-300 font-medium">Absensi</span>
                </a>
            </div>
        </div>

        <!-- Belum Absen Hari Ini -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <flux:heading size="lg" class="mb-4">Belum Absen Hari Ini</flux:heading>
            @if($notCheckedIn->count() > 0)
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($notCheckedIn->take(5) as $user)
                        <div class="flex items-center p-2 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <flux:avatar size="sm" variant="solid" initials="{{ $user->initials() }}" class="mr-3" />
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                @if($user->attendance)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        Jadwal: {{ $user->attendance->check_in_time }} - {{ $user->attendance->check_out_time }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if($notCheckedIn->count() > 5)
                        <div class="text-center text-sm text-gray-500 dark:text-gray-400 mt-2">
                            Dan {{ $notCheckedIn->count() - 5 }} karyawan lainnya
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-4">
                    <flux:icon name="check-circle" class="w-12 h-12 text-green-500 mx-auto mb-2" />
                    <flux:text class="text-gray-500 dark:text-gray-400">Semua karyawan sudah absen hari ini!</flux:text>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <flux:heading size="lg">Absensi Terbaru</flux:heading>
        </div>
        <div class="p-6">
            @if($recentAttendance->count() > 0)
                <div class="space-y-3">
                    @foreach($recentAttendance as $attendance)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                                <flux:avatar size="sm" variant="solid" initials="{{ $attendance->user->initials() }}" class="mr-3" />
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $attendance->user->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $attendance->location ? $attendance->location->name : 'Lokasi tidak diketahui' }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center space-x-2">
                                    @if($attendance->type === 'check_in')
                                        <flux:badge color="green" size="sm">Masuk</flux:badge>
                                    @else
                                        <flux:badge color="orange" size="sm">Keluar</flux:badge>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ \Carbon\Carbon::parse($attendance->created_at)->format('H:i') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <flux:icon name="clock" class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                    <flux:text class="text-gray-500 dark:text-gray-400">Belum ada data absensi</flux:text>
                </div>
            @endif
        </div>
    </div>
</div>
