<div class="space-y-6">
    <!-- Today's Attendance Status -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <flux:heading size="lg" class="mb-4">Status Absensi Hari Ini</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Check In Status -->
            <div class="flex items-center p-4 {{ $todaysAttendance['check_in'] ? ($isLateToday ? 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800' : 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800') : 'bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600' }} rounded-lg">
                <div class="p-3 {{ $todaysAttendance['check_in'] ? ($isLateToday ? 'bg-orange-100 dark:bg-orange-900/30' : 'bg-green-100 dark:bg-green-900/30') : 'bg-gray-100 dark:bg-gray-600' }} rounded-lg">
                    <flux:icon name="arrow-right-on-rectangle" class="w-6 h-6 {{ $todaysAttendance['check_in'] ? ($isLateToday ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400') : 'text-gray-400' }}" />
                </div>
                <div class="ml-4">
                    <flux:heading size="sm" class="{{ $todaysAttendance['check_in'] ? ($isLateToday ? 'text-orange-800 dark:text-orange-200' : 'text-green-800 dark:text-green-200') : 'text-gray-600 dark:text-gray-300' }}">Absen Masuk</flux:heading>
                    @if($todaysAttendance['check_in'])
                        <div class="text-lg font-bold {{ $isLateToday ? 'text-orange-900 dark:text-orange-100' : 'text-green-900 dark:text-green-100' }}">
                            {{ \Carbon\Carbon::parse($todaysAttendance['check_in']->check_in_time)->format('H:i') }}
                        </div>
                        @if($isLateToday)
                            <div class="text-sm text-orange-600 dark:text-orange-400">Terlambat</div>
                        @else
                            <div class="text-sm text-green-600 dark:text-green-400">Tepat waktu</div>
                        @endif
                    @else
                        <div class="text-lg font-bold text-gray-500 dark:text-gray-400">Belum absen</div>
                        @if($userAttendance && $userAttendance->check_in_time)
                            <div class="text-sm text-gray-500 dark:text-gray-400">Jadwal: {{ $userAttendance->check_in_time }}</div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Check Out Status -->
            <div class="flex items-center p-4 {{ $todaysAttendance['check_out'] ? 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800' : 'bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600' }} rounded-lg">
                <div class="p-3 {{ $todaysAttendance['check_out'] ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-gray-600' }} rounded-lg">
                    <flux:icon name="arrow-left-on-rectangle" class="w-6 h-6 {{ $todaysAttendance['check_out'] ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400' }}" />
                </div>
                <div class="ml-4">
                    <flux:heading size="sm" class="{{ $todaysAttendance['check_out'] ? 'text-blue-800 dark:text-blue-200' : 'text-gray-600 dark:text-gray-300' }}">Absen Keluar</flux:heading>
                    @if($todaysAttendance['check_out'])
                        <div class="text-lg font-bold text-blue-900 dark:text-blue-100">
                            {{ \Carbon\Carbon::parse($todaysAttendance['check_out']->check_out_time)->format('H:i') }}
                        </div>
                        <div class="text-sm text-blue-600 dark:text-blue-400">Selesai kerja</div>
                    @else
                        <div class="text-lg font-bold text-gray-500 dark:text-gray-400">Belum absen</div>
                        @if($userAttendance && $userAttendance->check_out_time)
                            <div class="text-sm text-gray-500 dark:text-gray-400">Jadwal: {{ $userAttendance->check_out_time }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Next Action -->
        @if($nextAction)
            <div class="flex items-center justify-center">
                @if($nextAction['type'] === 'check_in')
                    <a href="{{ route('attendance.check-in') }}"
                       class="flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                        <flux:icon name="arrow-right-on-rectangle" class="w-5 h-5 mr-2" />
                        Absen Masuk
                    </a>
                @else
                    <a href="{{ route('attendance.check-out') }}"
                       class="flex items-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors">
                        <flux:icon name="arrow-left-on-rectangle" class="w-5 h-5 mr-2" />
                        Absen Keluar
                    </a>
                @endif
            </div>
        @else
            <div class="text-center py-4">
                <flux:icon name="check-circle" class="w-12 h-12 text-green-500 mx-auto mb-2" />
                <flux:text class="text-green-600 dark:text-green-400 font-medium">Absensi hari ini sudah lengkap!</flux:text>
            </div>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Minggu Ini -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg inline-flex mb-2">
                    <flux:icon name="calendar-days" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_minggu_ini'] }}</div>
                <flux:text class="text-gray-600 dark:text-gray-300 text-sm">Minggu Ini</flux:text>
            </div>
        </div>

        <!-- Total Bulan Ini -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-center">
                <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg inline-flex mb-2">
                    <flux:icon name="calendar" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_bulan_ini'] }}</div>
                <flux:text class="text-gray-600 dark:text-gray-300 text-sm">Bulan Ini</flux:text>
            </div>
        </div>

        <!-- Terlambat Bulan Ini -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-center">
                <div class="p-3 bg-red-100 dark:bg-red-900/20 rounded-lg inline-flex mb-2">
                    <flux:icon name="clock" class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['terlambat_bulan_ini'] }}</div>
                <flux:text class="text-gray-600 dark:text-gray-300 text-sm">Terlambat</flux:text>
            </div>
        </div>

        <!-- Lembur Minggu Ini -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-lg inline-flex mb-2">
                    <flux:icon name="sparkles" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['lembur_minggu_ini'] }}</div>
                <flux:text class="text-gray-600 dark:text-gray-300 text-sm">Lembur</flux:text>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Jadwal Kerja -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <flux:heading size="lg" class="mb-4">Jadwal Kerja</flux:heading>
            @if($userAttendance)
                <div class="space-y-4">
                    <!-- Jadwal Kerja Lengkap -->
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex items-center mb-2">
                            <flux:icon name="clock" class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3" />
                            <span class="text-blue-700 dark:text-blue-300 font-medium">Jadwal Kerja</span>
                        </div>
                        <div class="text-blue-900 dark:text-blue-100 font-semibold">
                            {{ $userAttendance->getFormattedSchedule() }}
                        </div>
                    </div>

                    <!-- Hari Kerja -->
                    <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="flex items-center">
                            <flux:icon name="calendar-days" class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" />
                            <span class="text-green-700 dark:text-green-300 font-medium">Hari Kerja</span>
                        </div>
                        <div class="text-green-900 dark:text-green-100 font-semibold">
                            {{ $userAttendance->getWorkDaysText() }}
                        </div>
                    </div>

                    <!-- Lokasi Kerja -->
                    @if($userAttendance->location)
                        <div class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <div class="flex items-center">
                                <flux:icon name="map-pin" class="w-5 h-5 text-purple-600 dark:text-purple-400 mr-3" />
                                <span class="text-purple-700 dark:text-purple-300 font-medium">Lokasi Kerja</span>
                            </div>
                            <div class="text-purple-900 dark:text-purple-100 font-semibold">
                                {{ $userAttendance->location->name }}
                            </div>
                        </div>
                    @endif

                    <!-- Quick Actions -->
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <a href="{{ route('face.enrollment') }}" class="flex items-center justify-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors text-sm">
                                <flux:icon name="face-id" class="w-4 h-4 mr-2" />
                                Daftar Wajah
                            </a>
                            <a href="{{ route('attendance.check-in') }}" class="flex items-center justify-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors text-sm">
                                <flux:icon name="clock" class="w-4 h-4 mr-2" />
                                Lihat Absensi
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <flux:icon name="exclamation-triangle" class="w-12 h-12 text-yellow-500 mx-auto mb-2" />
                    <flux:text class="text-gray-500 dark:text-gray-400">Jadwal kerja belum diatur</flux:text>
                    <flux:text class="text-sm text-gray-400 dark:text-gray-500 mt-1">Hubungi administrator untuk mengatur jadwal kerja Anda</flux:text>
                </div>
            @endif
        </div>

        <!-- Recent Attendance -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <flux:heading size="lg" class="mb-4">Riwayat Absensi</flux:heading>
            @if($recentAttendance->count() > 0)
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @foreach($recentAttendance as $attendance)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-2 {{ $attendance->type === 'check_in' ? 'bg-green-100 dark:bg-green-900/20' : 'bg-orange-100 dark:bg-orange-900/20' }} rounded-lg">
                                    <flux:icon name="{{ $attendance->type === 'check_in' ? 'arrow-right-on-rectangle' : 'arrow-left-on-rectangle' }}"
                                             class="w-4 h-4 {{ $attendance->type === 'check_in' ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}" />
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $attendance->type === 'check_in' ? 'Masuk' : 'Keluar' }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $attendance->location ? $attendance->location->name : 'Lokasi tidak diketahui' }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($attendance->type === 'check_in' ? $attendance->check_in_time : $attendance->check_out_time)->format('H:i') }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($attendance->created_at)->format('d/m') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <flux:icon name="clock" class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                    <flux:text class="text-gray-500 dark:text-gray-400">Belum ada riwayat absensi</flux:text>
                </div>
            @endif
        </div>
    </div>
</div>
