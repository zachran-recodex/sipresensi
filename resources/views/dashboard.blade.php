<x-layouts.app title="Dashboard">
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-theme-primary rounded-lg p-6 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <flux:heading size="xl" class="text-white mb-2">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </flux:heading>
                    <flux:text class="text-blue-100">
                        @if(auth()->user()->hasRole(['admin', 'super admin']))
                            Kelola sistem absensi dan pantau kehadiran karyawan
                        @else
                            Catat kehadiran Anda dengan mudah dan akurat
                        @endif
                    </flux:text>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="bg-white/20 rounded-lg p-3" x-data="{ time: new Date().toLocaleTimeString('id-ID') }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID'), 1000)">
                        <flux:text class="text-blue-100 text-sm">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</flux:text>
                        <div class="text-2xl font-bold text-white" x-text="time">
                            {{ \Carbon\Carbon::now()->format('H:i:s') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->hasRole(['admin', 'super admin']))
            <!-- Admin Dashboard -->
            <livewire:administrator.dashboard-stats />
        @else
            <!-- Karyawan Dashboard -->
            <livewire:karyawan.dashboard-attendance />
        @endif
    </div>
</x-layouts.app>
