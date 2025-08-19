<x-layouts.app title="Absensi Keluar">
    <div class="min-h-screen">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 py-6 sm:py-8">
            <div class="mb-6 sm:mb-8 text-center sm:text-left">
                <flux:heading size="xl" class="mb-2">Absensi Keluar</flux:heading>
                <flux:text class="text-gray-600">
                    Gunakan face recognition untuk mencatat kehadiran keluar
                </flux:text>
            </div>

            <div class="rounded-lg bg-white p-4 sm:p-6 shadow-sm">
                <livewire:face-attendance-component type="check_out" />
            </div>
        </div>
    </div>
</x-layouts.app>
