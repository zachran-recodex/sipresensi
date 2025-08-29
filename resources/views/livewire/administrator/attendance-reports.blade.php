<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col space-y-4 md:flex-row md:justify-between md:items-center md:space-y-0">
        <div>
            <flux:heading size="xl">Laporan Absensi</flux:heading>
            <flux:subheading>Monitor dan analisis data kehadiran karyawan</flux:subheading>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <flux:icon name="document-text" class="w-6 h-6 text-blue-600" />
                </div>
                <div class="ml-3">
                    <flux:heading size="sm" class="text-blue-800">Total Absensi</flux:heading>
                    <div class="text-2xl font-bold text-blue-900">{{ number_format($stats['total_records']) }}</div>
                </div>
            </div>
        </div>

        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <flux:icon name="arrow-right-on-rectangle" class="w-6 h-6 text-green-600" />
                </div>
                <div class="ml-3">
                    <flux:heading size="sm" class="text-green-800">Absen Masuk</flux:heading>
                    <div class="text-2xl font-bold text-green-900">{{ number_format($stats['check_ins']) }}</div>
                </div>
            </div>
        </div>

        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <flux:icon name="arrow-left-on-rectangle" class="w-6 h-6 text-orange-600" />
                </div>
                <div class="ml-3">
                    <flux:heading size="sm" class="text-orange-800">Absen Keluar</flux:heading>
                    <div class="text-2xl font-bold text-orange-900">{{ number_format($stats['check_outs']) }}</div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-gray-100 rounded-lg">
                    <flux:icon name="users" class="w-6 h-6 text-gray-600" />
                </div>
                <div class="ml-3">
                    <flux:heading size="sm" class="text-gray-800">Total Karyawan</flux:heading>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['unique_users']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg border border-gray-200">
        <flux:heading size="lg" class="mb-4">Filter & Pencarian</flux:heading>

        <!-- Quick Date Filters -->
        <div class="mb-4">
            <flux:text class="text-sm font-medium text-gray-700 mb-2">Rentang Tanggal Cepat:</flux:text>
            <div class="flex flex-wrap gap-2">
                <flux:button variant="primary" size="sm" wire:click="setDateRange('today')">Hari Ini</flux:button>
                <flux:button variant="primary" size="sm" wire:click="setDateRange('yesterday')">Kemarin</flux:button>
                <flux:button variant="primary" size="sm" wire:click="setDateRange('this_week')">Minggu Ini</flux:button>
                <flux:button variant="primary" size="sm" wire:click="setDateRange('last_week')">Minggu Lalu</flux:button>
                <flux:button variant="primary" size="sm" wire:click="setDateRange('this_month')">Bulan Ini</flux:button>
                <flux:button variant="primary" size="sm" wire:click="setDateRange('last_month')">Bulan Lalu</flux:button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <!-- Search -->
            <div class="sm:col-span-2 xl:col-span-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama, username, email..."
                    clearable
                    icon="magnifying-glass"
                />
            </div>

            <!-- User Filter -->
            <flux:select wire:model.live="selectedUser" placeholder="Pilih Karyawan">
                <option value="">Semua Karyawan</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->username }})</option>
                @endforeach
            </flux:select>

            <!-- Date Range -->
            <flux:input
                type="date"
                wire:model.live="startDate"
                placeholder="Tanggal Mulai"
            />

            <flux:input
                type="date"
                wire:model.live="endDate"
                placeholder="Tanggal Selesai"
            />

            <!-- Type Filter -->
            <flux:select wire:model.live="attendanceType" placeholder="Jenis Absensi">
                <option value="">Semua Jenis Absen</option>
                <option value="check_in">Absen Masuk</option>
                <option value="check_out">Absen Keluar</option>
            </flux:select>

            <flux:button variant="primary" wire:click="clearFilters" icon="x-mark">
                Hapus Filter
            </flux:button>
        </div>
    </div>

    <!-- Attendance Records Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Karyawan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jenis Absen
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Waktu
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Lokasi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Detail
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendanceRecords as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ strtoupper(substr($record->user->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $record->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $record->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($record->type === 'check_in')
                                    <flux:badge color="green" size="sm">
                                        <flux:icon name="arrow-right-on-rectangle" class="mr-1" size="sm" />
                                        Absen Masuk
                                    </flux:badge>
                                @else
                                    <flux:badge color="orange" size="sm">
                                        <flux:icon name="arrow-left-on-rectangle" class="mr-1" size="sm" />
                                        Absen Keluar
                                    </flux:badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $record->recorded_at->format('d/m/Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $record->recorded_at->format('H:i:s') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    @if(is_array($record->coordinates))
                                        @if(isset($record->coordinates['coordinates']))
                                            📍 {{ $record->coordinates['coordinates']['latitude'] ?? 'N/A' }}, {{ $record->coordinates['coordinates']['longitude'] ?? 'N/A' }}
                                        @elseif(isset($record->coordinates['latitude']))
                                            📍 {{ $record->coordinates['latitude'] ?? 'N/A' }}, {{ $record->coordinates['longitude'] ?? 'N/A' }}
                                        @endif
                                    @else
                                        <div class="text-xs text-gray-500">{{ $record->coordinates ?: 'N/A' }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    @if($record->confidence_level)
                                        <div class="text-xs text-gray-500">
                                            Confidence: {{ number_format($record->confidence_level * 100, 1) }}%
                                        </div>
                                    @endif
                                    @if($record->mask_detected)
                                        <flux:badge color="yellow" size="sm">
                                            😷 Masker
                                        </flux:badge>
                                    @endif
                                    @if($record->notes)
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $record->notes }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center">
                                    <flux:icon name="document-text" class="w-12 h-12 text-gray-400 mb-3" />
                                    <flux:heading size="sm" class="text-gray-500 mb-1">No attendance records found</flux:heading>
                                    <flux:text class="text-gray-400">Try adjusting your filters or date range</flux:text>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($attendanceRecords->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $attendanceRecords->links() }}
            </div>
        @endif
    </div>
</div>
