<div x-data="{ open: true }" class="flex min-h-screen bg-gray-100">

    <!-- SIDEBAR -->
    <aside :class="open ? 'w-64' : 'w-16'" class="bg-white border-r transition-all duration-200 flex flex-col">

        <!-- LOGO -->
        <div class="h-16 flex items-center justify-between px-4 border-b">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <x-navbar />
            </a>

            {{-- <button @click="open = !open"
                class="text-gray-500 hover:text-gray-700">
                ☰
            </button> --}}
        </div>

        <div class="flex flex-1 flex-col justify-between">
            <!-- MENU -->
            <nav class="flex flex-col px-2 py-4 space-y-1 text-sm">

                <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-nav-link>

                <!-- USER MANAGEMENT -->
                <div x-data="{ openMenu: false }">
                    <button @click="openMenu = !openMenu"
                        class="w-full flex justify-between items-center px-3 py-2 rounded text-left font-semibold hover:bg-gray-100 text-gray-600">
                        User Management
                        <span>▾</span>
                    </button>

                    <div x-show="openMenu" x-collapse class="pl-4 mt-1 space-y-1 flex flex-col bg-gray-100 rounded">
                        <x-nav-link href="{{ route('users.index') }}">
                            Users
                        </x-nav-link>
                        <x-nav-link href="{{ route('roles.index') }}">
                            Roles
                        </x-nav-link>
                        <x-nav-link href="{{ route('permissions.index') }}">
                            Permissions
                        </x-nav-link>
                    </div>
                </div>

                <x-nav-link href="{{ route('products.index') }}">
                    Products
                </x-nav-link>

                <x-nav-link href="{{ route('category.index') }}">
                    Category
                </x-nav-link>

                <!-- SPECIFICATION MANAGEMENT -->
                <div x-data="{ openMenu: false }">
                    <button @click="openMenu = !openMenu"
                        class="w-full flex justify-between items-center px-3 py-2 font-semibold rounded hover:bg-gray-100 text-gray-600">
                        Specification Management
                        <span>▾</span>
                    </button>

                    <div x-show="openMenu" x-collapse class="pl-4 mt-1 space-y-1 bg-gray-100 rounded">
                        <x-nav-link href="{{ route('specification-groups.index') }}">
                            Specification Group
                        </x-nav-link>
                        <x-nav-link href="{{ route('specifications.index') }}">
                            Specification
                        </x-nav-link>
                        <x-nav-link href="{{ route('product-specifications.index') }}">
                            Product Specifications
                        </x-nav-link>
                        <x-nav-link href="{{ route('product-specification-scores.index') }}">
                            Product Specification Scores
                        </x-nav-link>
                        <x-nav-link href="{{ route('score-weights.index') }}">
                            Score Weights
                        </x-nav-link>
                        <x-nav-link href="{{ route('product-final-scores.index') }}">
                            Product Final Scores
                        </x-nav-link>
                    </div>
                </div>

                <x-nav-link href="{{ route('discount.index') }}">
                    Discount
                </x-nav-link>

                <x-nav-link href="{{ route('headline-slide.index') }}">
                    Headline Slider
                </x-nav-link>

            </nav>

            <!-- USER PROFILE -->
            <div class="border-t p-4">
                <div class="flex items-center gap-3">
                    <img class="w-10 h-10 rounded-full" src="{{ Auth::user()->profile_photo_url }}"
                        alt="{{ Auth::user()->name }}">
                    <div class="flex-1">
                        <div class="font-semibold text-sm">
                            {{ Auth::user()->name }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ Auth::user()->email }}
                        </div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-nav-link href="{{ route('profile.show') }}">
                        Profile
                    </x-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full text-left px-3 py-2 rounded text-red-600 hover:bg-red-50">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

</div>
