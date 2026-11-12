<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-100">
    <x-banner />

    <!-- APP WRAPPER -->
    <div x-data="{ cmsMenu: false }" @keydown.escape.window="cmsMenu = false" class="flex min-h-screen">
        <button type="button" @click="cmsMenu = !cmsMenu" :aria-expanded="cmsMenu" aria-label="Menu pengelolaan" class="md:hidden fixed bottom-5 left-5 z-[60] rounded-full bg-sky-600 text-white px-5 py-3 shadow-lg">Menu</button>
        <div x-show="cmsMenu" x-cloak @click="cmsMenu = false" class="md:hidden fixed inset-0 bg-slate-900/40 z-40"></div>

        <!-- SIDEBAR -->
        @include('sidebar')

        <!-- CONTENT AREA -->
        <div class="flex-1 min-w-0 flex flex-col">

            <!-- PAGE HEADER -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="px-6 py-4">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- PAGE CONTENT -->
            <main class="flex-1 p-6">
                {{ $slot }}
            </main>

        </div>
    </div>

    @stack('modals')
    @stack('scripts')
    @livewireScripts
</body>

</html>
