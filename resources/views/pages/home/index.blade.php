<x-frontend-layout>

    {{-- ================= CAROUSEL ================= --}}
    @if (!empty($headlines))
        <div x-data="carousel()" x-init="start()" class="relative max-w-7xl mx-auto px-4 mb-12">
            <div class="relative h-96 overflow-hidden rounded-xl">

                @foreach ($headlines as $i => $headline)
                    <div x-show="active === {{ $i }}" x-transition.opacity class="absolute inset-0">
                        <img src="{{ asset('storage/' . $headline->image) }}" class="w-full h-full object-cover">

                        <div class="absolute inset-0 bg-black/50 flex items-center">
                            <div class="px-10 text-white max-w-xl">
                                <h2 class="text-3xl font-bold mb-2">
                                    {{ $headline->title }}
                                </h2>
                                <p class="italic mb-4">
                                    {{ $headline->subtitle }}
                                </p>
                                <a href="{{ $headline->link }}"
                                    class="inline-block px-4 py-2 bg-blue-600 rounded hover:bg-blue-700">
                                    Read More
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Controls -->
            <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/40 p-2 rounded-full">
                ‹
            </button>
            <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/40 p-2 rounded-full">
                ›
            </button>
        </div>
    @endif

    {{-- ================= FILTER & CONTENT ================= --}}
    <div x-data="{ active: 'top', modalOpen: false }" class="max-w-7xl mx-auto px-4">

        {{-- FILTER --}}
        <div class="flex gap-3 mb-10">
            <button @click="active='top'; document.getElementById('top-penjualan').scrollIntoView({behavior:'smooth'})"
                :class="active === 'top' ? 'bg-sky-100 text-sky-700' : 'border'" class="px-4 py-2 rounded-full">Top
                Penjualan</button>

            <button
                @click="active='rekomendasi'; document.getElementById('rekomendasi').scrollIntoView({behavior:'smooth'})"
                :class="active === 'rekomendasi' ? 'bg-sky-100 text-sky-700' : 'border'"
                class="px-4 py-2 rounded-full">Rekomendasi</button>

            <button @click="active='discount'; document.getElementById('discount').scrollIntoView({behavior:'smooth'})"
                :class="active === 'discount' ? 'bg-sky-100 text-sky-700' : 'border'"
                class="px-4 py-2 rounded-full">Discount</button>
        </div>

        {{-- DISCOUNT --}}
        @if (!empty($discounts && count($discounts) > 0))
            <div id="discount" class="mb-16">
                <h2 class="text-lg font-semibold mb-4">Discount</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @foreach ($dicounts as $discount)
                        <div class="bg-white rounded-xl p-4 shadow">
                            <div class="h-28 bg-sky-200 rounded mb-3"></div>
                            <p class="text-center font-semibold">Rp.{{ number_format($discount->price, 0, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div id="discount" class="mb-16">
                <h2 class="text-lg font-semibold mb-4">Discount</h2>
                <p class="text-gray-500 italic">Tidak ada produk dengan diskon saat ini.</p>
            </div>
        @endif


        {{-- FEATURE --}}
        <div class="mb-20">
            <h2 class="text-xl font-bold mb-6">Fitur Terbaik</h2>

            <div class="grid md:grid-cols-3 gap-8">
                <div @click="modalOpen=true"
                    class="bg-white p-6 rounded-2xl shadow border cursor-pointer hover:shadow-lg">
                    <div class="h-36 bg-sky-200 rounded mb-4"></div>
                    <p class="text-xl font-bold text-center">Rp.355.000</p>
                    <p class="text-center text-sm text-gray-500 mb-4">STB Merah</p>
                    <p class="text-center text-sky-600 text-sm">Lihat Detail →</p>
                </div>
            </div>
        </div>

        {{-- TOP --}}
        <div id="top-penjualan" class="mb-16">
            <h2 class="text-lg font-semibold mb-4">Top Penjualan</h2>

            @if ($topSales->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @foreach ($topSales as $product)
                        <div class="bg-white rounded-xl p-4 shadow hover:shadow-lg transition">
                            <div class="h-28 bg-sky-200 rounded mb-3 overflow-hidden">
                                @if ($product->image ?? false)
                                    <img src="{{ asset('storage/' . $product->image) }}"
                                        class="w-full h-full object-cover">
                                @endif
                            </div>

                            <p class="text-center font-semibold">
                                Rp.{{ number_format($product->price, 0, ',', '.') }}
                            </p>

                            <p class="text-xs text-center text-gray-500 mt-1">
                                Terjual: {{ $product->salesStat->total_sold ?? 0 }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic">Belum ada data penjualan.</p>
            @endif
        </div>


        {{-- REKOMENDASI --}}
        @if (!empty($recommendedProducts))
            <div id="rekomendasi" class="mb-16">
                <h2 class="text-lg font-semibold mb-4">Rekomendasi</h2>

                @if ($recommendedProducts->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @foreach ($recommendedProducts as $product)
                            <div class="bg-white rounded-xl p-4 shadow hover:shadow-lg transition">
                                <div class="h-28 bg-sky-200 rounded mb-3 overflow-hidden">
                                    @if ($product->image ?? false)
                                        <img src="{{ asset('storage/' . $product->image) }}"
                                            class="w-full h-full object-cover">
                                    @endif
                                </div>
                    
                                <p class="text-center font-semibold">
                                    Rp.{{ number_format($product->price, 0, ',', '.') }}
                                </p>

                                <p class="text-xs text-center text-gray-500 mt-1">
                                    Score: {{ number_format($product->finalScore->final_score ?? 0, 3) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 italic">Belum ada rekomendasi.</p>
                @endif
            </div>
        @endif



        {{-- MODAL --}}
        <div x-show="modalOpen" x-transition x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <div @click="modalOpen=false" class="absolute inset-0 bg-black/40"></div>

            <div class="relative bg-white rounded-2xl w-full max-w-lg p-6">
                <div class="flex justify-between mb-4">
                    <h3 class="font-semibold">STB Merah</h3>
                    <button @click="modalOpen=false">✕</button>
                </div>

                <div class="h-40 bg-sky-200 rounded mb-4"></div>

                <p class="text-xl font-bold mb-3">Rp.355.000</p>

                <ul class="text-sm space-y-1 mb-4">
                    <li>Chipset : SUNPLUS 1509C</li>
                    <li>Memory : 512 Mb</li>
                    <li>Flash : 8 Mb</li>
                    <li>Video : 1080p</li>
                </ul>

                <button class="w-full py-2 border rounded hover:bg-gray-50">
                    Bandingkan
                </button>
            </div>
        </div>

    </div>

    {{-- ================= JS ================= --}}
    <script>
        function carousel() {
            return {
                active: 0,
                total: {{ count($headlines ?? []) }},
                start() {
                    setInterval(() => this.next(), 5000)
                },
                next() {
                    this.active = (this.active + 1) % this.total
                },
                prev() {
                    this.active = (this.active - 1 + this.total) % this.total
                }
            }
        }
    </script>

</x-frontend-layout>
