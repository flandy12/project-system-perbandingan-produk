@extends('layouts.frontend')

@section('content')
    <div id="default-carousel" class="relative w-full mb-10" data-carousel="slide">
        <!-- Carousel wrapper -->
        <div class="relative h-full overflow-hidden rounded-base md:h-96">
            <!-- Item 1 -->
            <div class="duration-700 ease-in-out" data-carousel-item>
                <img src="https://images.unsplash.com/photo-1761839257789-20147513121a?q=80&w=1169&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDF8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                    class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="...">
            </div>
            <!-- Item 2 -->
            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                <img src="https://unsplash.com/photos/man-running-on-a-red-track-with-white-lines-2nUwx5QSJ84"
                    class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="...">
            </div>
            <!-- Item 3 -->
            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                <img src="https://images.unsplash.com/photo-1726195221456-7e104a23bbff?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1yZWxhdGVkfDQzfHx8ZW58MHx8fHx8"
                    class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="...">
            </div>
            <!-- Item 4 -->
            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                <img src="https://images.unsplash.com/photo-1761839256547-0a1cd11b6dfb?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1yZWxhdGVkfDE3fHx8ZW58MHx8fHx8"
                    class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="...">
            </div>
            <!-- Item 5 -->
            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                <img src="https://images.unsplash.com/photo-1728908053186-54a888346f82?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1yZWxhdGVkfDQxfHx8ZW58MHx8fHx8"
                    class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="...">
            </div>
        </div>
        <!-- Slider indicators -->
        <div class="absolute z-30 flex -translate-x-1/2 bottom-5 left-1/2 space-x-3 rtl:space-x-reverse">
            <button type="button" class="w-3 h-3 rounded-base" aria-current="true" aria-label="Slide 1"
                data-carousel-slide-to="0"></button>
            <button type="button" class="w-3 h-3 rounded-base" aria-current="false" aria-label="Slide 2"
                data-carousel-slide-to="1"></button>
            <button type="button" class="w-3 h-3 rounded-base" aria-current="false" aria-label="Slide 3"
                data-carousel-slide-to="2"></button>
            <button type="button" class="w-3 h-3 rounded-base" aria-current="false" aria-label="Slide 4"
                data-carousel-slide-to="3"></button>
            <button type="button" class="w-3 h-3 rounded-base" aria-current="false" aria-label="Slide 5"
                data-carousel-slide-to="4"></button>
        </div>
        <!-- Slider controls -->
        <button type="button"
            class="absolute top-0 start-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
            data-carousel-prev>
            <span
                class="inline-flex items-center justify-center w-10 h-10 rounded-base bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                <svg class="w-5 h-5 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m15 19-7-7 7-7" />
                </svg>
                <span class="sr-only">Previous</span>
            </span>
        </button>
        <button type="button"
            class="absolute top-0 end-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
            data-carousel-next>
            <span
                class="inline-flex items-center justify-center w-10 h-10 rounded-base bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                <svg class="w-5 h-5 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m9 5 7 7-7 7" />
                </svg>
                <span class="sr-only">Next</span>
            </span>
        </button>
    </div>


    <div x-data="{
        active: 'top',
        modalOpen: false
    }">

        <div class="max-w-7xl mx-auto px-4">

            <!-- FILTER BAR -->
            <div
                class="flex flex-wrap md:flex-nowrap
           items-center justify-start md:justify-end
           gap-3 pb-2 mb-8 z-40">

                <button
                    @click="
        active = 'top';
        document.getElementById('top-penjualan')?.scrollIntoView({ behavior: 'smooth' })
    "
                    :class="active === 'top'
                        ?
                        'bg-sky-100 text-sky-700' :
                        'border border-slate-200'"
                    class="px-4 py-2 text-sm rounded-full whitespace-nowrap">
                    Top Penjualan
                </button>


                <button
                    @click="
        active = 'rekomendasi';
        document.getElementById('rekomendasi')?.scrollIntoView({ behavior: 'smooth' })
    "
                    :class="active === 'rekomendasi'
                        ?
                        'bg-sky-100 text-sky-700' :
                        'bg-white border border-slate-200'"
                    class="px-4 py-2 text-sm rounded-full whitespace-nowrap">
                    Rekomendasi
                </button>


                <button
                    @click="
        active = 'discount';
        document.getElementById('discount')?.scrollIntoView({ behavior: 'smooth' })
    "
                    :class="active === 'discount'
                        ?
                        'bg-sky-100 text-sky-700' :
                        'bg-white border border-slate-200'"
                    class="px-4 py-2 text-sm rounded-full whitespace-nowrap">
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


                <!-- DISCOUNT -->
                <div id="discount" class="mb-12">
                    <div class="flex mb-4 justify-between items-center ">
                        <h2 class="text-lg font-semibold ">Discount</h2>
                        <p class="text-gray-500 hover:text-gray-800 cursor-pointer font-semibold">Lainnya</p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="bg-white rounded-2xl p-4 shadow-sm">
                                <div class="h-28 bg-sky-200 rounded-xl mb-4"></div>
                                <p class="text-center font-semibold">Rp.155.000</p>
                            </div>
                        @endfor
                    </div>
                </div>


                <div class="flex items-center gap-2 mb-6">
                    <span class="text-yellow-400 text-lg">⭐</span>
                    <h2 class="text-xl font-semibold tracking-wide">Fitur Terbaik</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <div @click="modalOpen = true"
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
                <div id="top-penjualan">
                    <div class="flex mb-4 justify-between items-center ">
                        <h2 class="text-lg font-semibold ">Top Penjualan</h2>
                        <p class="text-gray-500 hover:text-gray-800 cursor-pointer font-semibold">Lainnya</p>
                    </div>
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
                <div id="rekomendasi">
                    <div class="flex mb-4 justify-between items-center ">
                        <h2 class="text-lg font-semibold ">Rekomendasi</h2>
                        <p class="text-gray-500 hover:text-gray-800 cursor-pointer font-semibold">Lainnya</p>
                    </div>
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
        <div x-show="modalOpen" " x-transition x-cloak class="fixed inset-0 z-50 flex items-center justify-center">

                                <!-- Overlay -->
                                <div @click="open = false" class="absolute inset-0 bg-black/40 backdrop-blur-sm">
                                </div>

                                <!-- Modal Box -->
                                <div class="relative bg-white rounded-3xl w-full max-w-lg mx-4 p-6">

                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-semibold">STB Merah</h3>
                                        <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
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
