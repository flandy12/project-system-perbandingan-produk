<div x-data="{ sidebarOpen: true }" class="flex min-h-screen bg-slate-50">

    <!-- SIDEBAR -->
    <aside :class="sidebarOpen ? 'w-72' : 'w-20'"
        class="bg-white border-r border-slate-200 shadow-sm transition-all duration-300 flex flex-col">

        <!-- HEADER -->
        <div class="h-16 border-b border-slate-200 flex items-center justify-between px-4">

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">

                <div class="rounded-xl  flex items-center justify-center font-bold">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-6">
                </div>

                <div x-show="sidebarOpen" x-transition>
                    <div class="font-bold text-slate-800">
                        Product Admin
                    </div>
                    <div class="text-xs text-slate-500">
                        Management System
                    </div>
                </div>

            </a>

        </div>

        <!-- SEARCH -->

        <div class="flex-1 overflow-y-auto">

            <!-- MAIN -->
            <div x-show="sidebarOpen"
                class="px-4 mt-2 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">

                Main Menu

            </div>

            <nav class="px-3 space-y-1">

                {{-- Dashboard --}}
                @can('view.dashboard')
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-sky-50 hover:text-sky-600 transition">

                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7m-9 11V9m0 0H5m7 0h7" />
                        </svg>

                        <span x-show="sidebarOpen">Dashboard</span>

                    </a>
                @endcan

                {{-- Product --}}
                @can('view.product')
                    <a href="{{ route('products.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-sky-50 hover:text-sky-600 transition">

                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6" />
                        </svg>

                        <span x-show="sidebarOpen">Products</span>

                    </a>
                @endcan

                {{-- Category --}}
                @can('view.category')
                    <a href="{{ route('category.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-sky-50 hover:text-sky-600 transition">

                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>

                        <span x-show="sidebarOpen">Category</span>

                    </a>
                @endcan

            </nav>

            @canany(['view.user', 'view.role', 'view.permission'])
                <!-- USER MANAGEMENT -->
                <div x-show="sidebarOpen"
                    class="px-4 mt-6 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">

                    System

                </div>
                <div x-data="{ openMenu: false }" class="px-3">

                    <button @click="openMenu =! openMenu"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl hover:bg-slate-100">

                        <span>User Management</span>

                        <svg :class="openMenu ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />

                        </svg>

                    </button>

                    <div x-show="openMenu" x-collapse class="mt-1 ml-4 space-y-1">

                        @can('view.user')
                            <a href="{{ route('users.index') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100">
                                Users
                            </a>
                        @endcan

                        @can('view.role')
                            <a href="{{ route('roles.index') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100">
                                Roles
                            </a>
                        @endcan

                        @can('view.permission')
                            <a href="{{ route('permissions.index') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100">
                                Permissions
                            </a>
                        @endcan

                    </div>
                </div>
            @endcan

            <!-- SPECIFICATION -->
            @canany(['view.specification.group', 'view.specification', 'view.product.specification',
                'view.score.weight'])
                <div x-data="{ openSpec: false }" class="px-3 mt-2">

                    <button @click="openSpec=!openSpec"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl hover:bg-slate-100">

                        <span>Specification</span>

                    </button>

                    <div x-show="openSpec" x-collapse class="mt-1 ml-4 space-y-1">

                        @can('view.specification.group')
                            <a href="{{ route('specification-groups.index') }}"
                                class="block px-4 py-2 rounded-lg hover:bg-slate-100">
                                Specification Group
                            </a>
                        @endcan

                        @can('view.specification')
                            <a href="{{ route('specifications.index') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100">
                                Specification
                            </a>
                        @endcan

                        @can('view.product.specification')
                            <a href="{{ route('product-specifications.index') }}"
                                class="block px-4 py-2 rounded-lg hover:bg-slate-100">
                                Product Specifications
                            </a>
                        @endcan

                        @can('view.score.weight')
                            <a href="{{ route('score-weights.index') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100">
                                Score Weights
                            </a>
                        @endcan

                    </div>

                </div>
            @endcanany

            <!-- CONTENT -->
            @canany(['view.discount', 'view.headline', 'view.top.product', 'view.product.sales'])
                <div x-show="sidebarOpen"
                    class="px-4 mt-6 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">

                    Content

                </div>

                <nav class="px-3 space-y-1">

                    @can('view.discount')
                        <a href="{{ route('discount.index') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100">
                            <span x-show="sidebarOpen">Discount</span>
                        </a>
                    @endcan

                    @can('view.headline')
                        <a href="{{ route('headline-slide.index') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100">
                            <span x-show="sidebarOpen">Headline Slider</span>
                        </a>
                    @endcan

                    @can('view.top.product')
                        <a href="{{ route('top-product.index') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100">
                            <span x-show="sidebarOpen">Top Product</span>
                        </a>
                    @endcan

                    @can('view.product.sales')
                        <a href="{{ route('product-sales-stats.index') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100">
                            <span x-show="sidebarOpen">Sales Statistics</span>
                        </a>
                    @endcan

                </nav>
            @endcan
        </div>

        <!-- PROFILE -->
        <div class="border-t border-slate-200 p-4">

            <div class="bg-slate-50 rounded-2xl p-3">

                <div class="flex items-center gap-3">

                    <img src="{{ Auth::user()->profile_photo_url }}" class="w-11 h-11 rounded-xl object-cover"
                        alt="{{ Auth::user()->name }}">

                    <div x-show="sidebarOpen">

                        <div class="font-semibold text-sm">
                            {{ Auth::user()->name }}
                        </div>

                        <div class="text-xs text-slate-500">
                            Administrator
                        </div>

                    </div>

                </div>

            </div>

            <div class="mt-3 space-y-1">

                <a href="{{ route('profile.show') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">

                    Profile

                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="w-full text-left px-3 py-2 rounded-lg text-red-600 hover:bg-red-50">

                        Logout

                    </button>

                </form>

            </div>

        </div>

    </aside>

</div>
