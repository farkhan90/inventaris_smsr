<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventaris Barang SMSR</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <div class="p-6 pt-3 ml-5">
                <a href="/" wire:navigate>
                    {{-- <x-application-logo class="w-20 h-20" /> --}}
                    <h2 class="text-2xl font-bold">Inventaris</h2>
                </a>
            </div>

            {{-- MENU --}}
            <x-menu activate-by-route>
                <x-menu-item title="Beranda" icon="o-home" link="/" />

                {{-- TAMPILKAN MENU INI HANYA UNTUK ADMIN --}}
                @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                    <x-menu-item title="Kelola User" icon="o-users" link="{{ route('users.index') }}" />
                @endif

                {{-- LOGOUT --}}
                <x-menu-separator />

            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
</body>

</html>
