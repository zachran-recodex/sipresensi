<x-layouts.app title="Absensi Masuk">
    <div class="min-h-screen">
        <div class="mx-auto max-w-4xl px-6 py-8">
            <div class="mb-8">
                <flux:heading size="xl" class="mb-2">Absensi Masuk</flux:heading>
                <flux:text class="text-gray-600">
                    Gunakan face recognition untuk mencatat kehadiran masuk
                </flux:text>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <livewire:face-attendance-component type="check_in" />
            </div>
        </div>
    </div>
</x-layouts.app>
