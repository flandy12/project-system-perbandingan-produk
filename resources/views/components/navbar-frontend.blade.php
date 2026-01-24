<!-- Header -->
<div>
    <!-- Top Row -->
    <div class="flex items-center justify-start gap-5 items-center mb-4 py-5">
        <!-- Logo -->
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10">

        <x-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')">
                    {{ __('Home') }}
                </x-nav-link>
        <x-nav-link href="{{ route('gallery.index') }}" :active="request()->routeIs('gallery.index')">
                    {{ __('Product') }}
                </x-nav-link>
    </div>

</div>
