@extends('layouts.app')

@section('content')
    <div x-data="{ open: false }">

        <div class="max-w-7xl mx-auto px-4">

            <!-- FILTER BAR -->
            <div
                class="flex flex-wrap md:flex-nowrap
           items-center justify-start md:justify-end
           gap-3
           pb-2 mb-8 bg-slate-50 z-40">

                <button class="px-4 py-2 text-sm rounded-full bg-sky-100 text-sky-700 font-medium whitespace-nowrap">
                    Top Penjualan
                </button>

                <button
                    class="px-4 py-2 text-sm rounded-full bg-white border border-slate-200 hover:bg-slate-50 whitespace-nowrap">
                    Rekomendasi
                </button>

                <button
                    class="px-4 py-2 text-sm rounded-full bg-white border border-slate-200 hover:bg-slate-50 whitespace-nowrap">
                    Discount
                </button>

                <!-- Dropdown Filter -->
                <div x-data="{ open: false }" class="relative shrink-0" x-cloak>

                    <button @click="open = !open" @click.outside="open = false"
                        class="px-4 py-2 text-sm rounded-full bg-white border border-slate-200
                       flex items-center gap-2 hover:bg-slate-50 whitespace-nowrap">
                        Berdasarkan
                        <svg class="w-4 h-4 text-slate-500 transition-transform" :class="{ 'rotate-180': open }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" x-transition
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg
                       border border-slate-200 z-50">
                        <div class="p-3 space-y-1 text-sm">
                            <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-50">
                                Harga Termurah
                            </button>
                            <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-50">
                                Harga Termahal
                            </button>
                            <hr class="my-2">
                            <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-50">
                                Fitur Terlengkap
                            </button>
                            <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-50">
                                Paling Banyak Dibeli
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- FITUR TERBAIK -->

            <section class="mb-16">

                <div class="flex items-center gap-2 mb-6">
                    <span class="text-yellow-400 text-lg">⭐</span>
                    <h2 class="text-xl font-semibold tracking-wide">Fitur Terbaik</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <div @click="open = true"
                        class="bg-white rounded-3xl p-6 shadow-md border border-sky-100
           cursor-pointer hover:shadow-lg transition">

                        <div class="h-36 bg-sky-200 rounded-2xl mb-5"></div>

                        <p class="text-xl font-bold text-center mb-1">Rp.355.000</p>
                        <p class="text-center text-sm text-slate-500 mb-4">STB Merah</p>

                        <ul class="text-sm space-y-1 text-slate-700 mb-4">
                            <li>• Memory : 512 Mb</li>
                            <li>• Flash : 8 Mb</li>
                            <li>• Video : 1080 Full HD</li>
                        </ul>

                        <p class="text-center text-sky-600 text-sm font-medium">
                            Lihat Detail →
                        </p>
                    </div>


                    <!-- Secondary -->
                    <div class="bg-slate-50 rounded-3xl p-6">
                        <div class="h-36 bg-sky-100 rounded-2xl mb-5"></div>
                        <p class="text-lg font-semibold text-center">Rp.295.000</p>
                        <p class="text-center text-sm text-slate-500">STB Kuning</p>
                    </div>

                    <div class="bg-slate-50 rounded-3xl p-6">
                        <div class="h-36 bg-sky-100 rounded-2xl mb-5"></div>
                        <p class="text-lg font-semibold text-center">Rp.245.000</p>
                        <p class="text-center text-sm text-slate-500">STB Hijau</p>
                    </div>

                </div>
            </section>

            <!-- LISTING SECTIONS -->
            <section class="space-y-14">

                <!-- TOP PENJUALAN -->
                <div>
                    <h2 class="text-lg font-semibold mb-4">Top Penjualan</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="bg-white rounded-2xl p-4 shadow-sm">
                                <div class="h-28 bg-sky-200 rounded-xl mb-4"></div>
                                <p class="text-center font-semibold">Rp.155.000</p>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- REKOMENDASI -->
                <div>
                    <h2 class="text-lg font-semibold mb-4">Rekomendasi</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="bg-white rounded-2xl p-4 shadow-sm">
                                <div class="h-28 bg-sky-200 rounded-xl mb-4"></div>
                                <p class="text-center font-semibold">Rp.155.000</p>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- DISCOUNT -->
                <div>
                    <h2 class="text-lg font-semibold mb-4">Discount</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="bg-white rounded-2xl p-4 shadow-sm">
                                <div class="h-28 bg-sky-200 rounded-xl mb-4"></div>
                                <p class="text-center font-semibold">Rp.155.000</p>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Banyak Pembelian -->
                <div>
                    <h2 class="text-lg font-semibold mb-4">Banyak Pembelian</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="bg-white rounded-2xl p-4 shadow-sm">
                                <div class="h-28 bg-sky-200 rounded-xl mb-4"></div>
                                <p class="text-center font-semibold">Rp.155.000</p>
                            </div>
                        @endfor
                    </div>
                </div>

            </section>

        </div>

        <!-- MODAL -->
        <div x-show="open" x-transition x-cloak class="fixed inset-0 z-50 flex items-center justify-center">

            <!-- Overlay -->
            <div @click="open = false" class="absolute inset-0 bg-black/40 backdrop-blur-sm">
            </div>

            <!-- Modal Box -->
            <div class="relative bg-white rounded-3xl w-full max-w-lg mx-4 p-6">

                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">STB Merah</h3>
                    <button @click="open = false" class="text-slate-400 hover:text-slate-600">
                        ✕
                    </button>
                </div>

                <div class="h-40 bg-sky-200 rounded-2xl mb-5"></div>

                <p class="text-xl font-bold mb-3">Rp.355.000</p>

                <ul class="text-sm space-y-2 text-slate-700 mb-6">
                    <li>✔ Chipset : SUNPLUS 1509C</li>
                    <li>✔ Memory : 512 Mb</li>
                    <li>✔ Flash : 8 Mb</li>
                    <li>✔ Video : MPEG-2 / MPEG-4 / H.264</li>
                    <li>✔ Audio : MPEG-2</li>
                    <li>✔ USB Port : 1x Front</li>
                    <li>✔ IPTV : Support</li>
                </ul>

                <div class="flex gap-3">
                    <button class="flex-1 py-2 rounded-full border border-slate-200 hover:bg-slate-50">
                        Bandingkan
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection
