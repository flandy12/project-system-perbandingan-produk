<header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100 shadow-sm">

    <div class="max-w-7xl mx-auto px-4 lg:px-8">

        <div class="h-20 flex items-center justify-between">

            <!-- Logo -->
            <div class="flex items-center gap-10">

                <a href="{{ route('home') }}" class="flex items-center gap-3">

                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-auto">

                </a>

                <!-- Menu -->
                <nav class="hidden md:flex items-center gap-8">

                    <x-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')"
                        class="text-gray-600 hover:text-sky-600 transition">

                        Home

                    </x-nav-link>

                    <x-nav-link href="{{ route('gallery.index') }}" :active="request()->routeIs('gallery.index')"
                        class="text-gray-600 hover:text-sky-600 transition">

                        Product

                    </x-nav-link>

                    <x-nav-link href="{{ route('versus.index') }}" :active="request()->routeIs('versus.index')"
                        class="text-gray-600 hover:text-sky-600 transition">

                        Compare

                    </x-nav-link>

                    <x-nav-link href="{{ route('contact.index') }}" :active="request()->routeIs('contact.index')"
                        class="text-gray-600 hover:text-sky-600 transition">

                        Contact

                    </x-nav-link>

                </nav>

            </div>

            <!-- Right -->
            <div class="flex items-center gap-4">

                @guest

                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-sky-600 transition font-medium">

                        Login

                    </a>

                    <a href="{{ route('register') }}"
                        class="px-5 py-2.5 rounded-xl bg-sky-600 text-white font-medium hover:bg-sky-700 transition shadow-lg shadow-sky-200">

                        Register

                    </a>

                @endguest


                @auth

                    {{-- Dashboard hanya untuk Admin --}}
                    @if (Auth::user()->hasRole(['super admin', 'admin']))
                        <a href="{{ route('dashboard') }}"
                            class="px-5 py-2.5 rounded-xl bg-sky-600 text-white font-medium hover:bg-sky-700 transition">

                            Dashboard

                        </a>
                    @endif

                    {{-- Profile Dropdown --}}
                    <div x-data="{ open: false }" class="relative">

                        <button @click="open = !open"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-gray-100 transition">

                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}"
                                    class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
                            @else
                                <div
                                    class="w-10 h-10 rounded-full bg-sky-600 text-white flex items-center justify-center font-semibold">

                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}

                                </div>
                            @endif

                            <div class="hidden md:block text-left">

                                <div class="font-semibold text-sm text-gray-800">
                                    {{ Auth::user()->name }}
                                </div>

                                <div class="text-xs text-gray-500">

                                    @if (Auth::user()->roles->count())
                                        {{ Auth::user()->roles->first()->name }}
                                    @else
                                        Member
                                    @endif

                                </div>

                            </div>

                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />

                            </svg>

                        </button>

                        {{-- Dropdown --}}
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50">

                            <div class="p-4 border-b">

                                <div class="font-semibold text-gray-800">
                                    {{ Auth::user()->name }}
                                </div>

                                <div class="text-sm text-gray-500">
                                    {{ Auth::user()->email }}
                                </div>

                            </div>

                            <div class="py-2">

                                @if (Auth::user()->hasRole(['super admin', 'admin']))
                                    <a href="{{ route('dashboard') }}"
                                        class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">

                                        <span>📊</span>
                                        <span>Dashboard</span>

                                    </a>
                                @endif

                                <div class="border-t my-2"></div>

                                <form method="POST" action="{{ route('logout') }}">

                                    @csrf

                                    <button type="submit"
                                        class="w-full text-left flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 transition">

                                        <span>🚪</span>
                                        <span>Logout</span>

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                @endauth

            </div>
        </div>

    </div>

</header>
