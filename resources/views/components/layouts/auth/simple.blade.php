<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased">
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-8 p-8 md:p-12">
            <div class="flex w-full max-w-md flex-col gap-6">
                <!-- Logo + Text Center -->
                <a href="{{ route('home') }}"
                   class="flex items-center justify-center gap-4 font-medium"
                   wire:navigate>

                    <!-- Logo -->
                    <span class="flex h-16 w-16 items-center justify-center rounded-lg bg-primary">
                                <x-app-logo-icon class="size-12 fill-current text-white" />
                            </span>

                    <!-- App Name + Subtitle -->
                    <div class="flex flex-col justify-center text-center text-theme-primary">
                                <span class="font-bold text-2xl leading-tight">
                                    {{ config('app.name', 'Sipresensi') }}
                                </span>
                        <span class="text-base leading-tight">
                                    Sistem Presensi
                                </span>
                    </div>
                </a>

                <!-- Slot content -->
                <div class="flex flex-col gap-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
