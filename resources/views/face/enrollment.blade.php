<x-layouts.app title="Daftar Wajah">
    <div class="min-h-screen">
        <div class="mx-auto max-w-4xl px-6 py-8">
            <div class="mb-8">
                <flux:heading size="xl" class="mb-2">Daftar Wajah</flux:heading>
                <flux:text class="text-gray-600">
                    Daftarkan wajah Anda untuk menggunakan fitur absensi face recognition
                </flux:text>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <livewire:face-enrollment-component />
            </div>
        </div>
    </div>
</x-layouts.app>
