<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    // No form handling needed - using admin contact only
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Lupa kata sandi" description="Hubungi administrator untuk reset kata sandi Anda" />

    <!-- Main Admin Contact Information -->
    <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
        <div class="flex items-start space-x-3">
            <flux:icon name="information-circle" class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" />
            <div class="text-left">
                <flux:heading size="md" class="text-blue-800 mb-3">
                    Reset Kata Sandi
                </flux:heading>
                <flux:text class="text-blue-700 mb-4">
                    Untuk mereset kata sandi Anda, silahkan hubungi administrator sistem. 
                    Mereka akan membantu Anda mengatur ulang kata sandi dengan aman.
                </flux:text>
                <div class="bg-white p-4 rounded-lg space-y-3">
                    <flux:text class="font-semibold text-blue-800">
                        Kontak {{ config('services.admin_contact.name') }}:
                    </flux:text>
                    @if (config('services.admin_contact.email'))
                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                            <div class="text-2xl">📧</div>
                            <div>
                                <div class="font-medium text-blue-800">Email</div>
                                <a href="mailto:{{ config('services.admin_contact.email') }}" 
                                   class="text-blue-600 hover:text-blue-800 underline">
                                    {{ config('services.admin_contact.email') }}
                                </a>
                            </div>
                        </div>
                    @endif
                    @if (config('services.admin_contact.whatsapp') ?: config('services.admin_contact.phone'))
                        <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                            <div class="text-2xl">📱</div>
                            <div>
                                <div class="font-medium text-green-800">WhatsApp</div>
                                <a href="https://wa.me/{{ str_replace(['+', ' ', '-'], '', config('services.admin_contact.whatsapp') ?: config('services.admin_contact.phone')) }}" 
                                   target="_blank" 
                                   class="text-green-600 hover:text-green-800 underline">
                                    {{ config('services.admin_contact.whatsapp') ?: config('services.admin_contact.phone') }}
                                </a>
                            </div>
                        </div>
                    @endif
                    @if (config('services.admin_contact.department'))
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="text-2xl">🏢</div>
                            <div>
                                <div class="font-medium text-gray-800">Bagian</div>
                                <div class="text-gray-600">{{ config('services.admin_contact.department') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        <span>Atau, kembali ke</span>
        <flux:link :href="route('login')" wire:navigate>masuk</flux:link>
    </div>
</div>
