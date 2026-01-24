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

<body>

    <div class="max-w-7xl mx-auto">
        <x-navbar-frontend />
    </div>
    <div class="font-sans text-gray-900 antialiased  h-max-screen">
        {{ $slot }}
    </div>

    @stack('js')

    <script>
        window.trackClick = function(productId) {
            fetch(`/track/product-click/${productId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    keepalive: true
                })
                .then(res => {
                    if (!res.ok) throw res;
                    return res.json();
                })
                .then(d => console.log('Tracked', d))
                .catch(e => console.error('Track error', e));
        }

        window.trackPageView = function() {
            fetch(`/track/page-view`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content')
                },
                keepalive: true
            });
        }

        // auto fire once
        document.addEventListener('DOMContentLoaded', () => {
            trackPageView();
        });
    </script>


    @livewireScripts
</body>

</html>
