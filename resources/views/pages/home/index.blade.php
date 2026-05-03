<x-frontend-layout>

    {{-- ================= CAROUSEL ================= --}}
    @if (!empty($headlines))
        <div x-data="carousel()" x-init="start()" class="relative max-w-7xl mx-auto px-4 mb-12">
            <div class="relative h-96 overflow-hidden rounded-xl">

                @foreach ($headlines as $i => $headline)
                    <div x-show="active === {{ $i }}" x-transition.opacity class="absolute inset-0">
                        <img src="{{ asset('storage/' . $headline->image) }}" class="h-full object-cover w-full">

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

            <button @click="prev()"
                class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/40 p-2 rounded-full">‹</button>
            <button @click="next()"
                class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/40 p-2 rounded-full">›</button>
        </div>
    @endif


    {{-- ================= ROOT STATE ================= --}}
    <div x-data="compare" x-init="init()" class="max-w-7xl mx-auto px-4">

        {{-- FILTER --}}
        <div class="flex gap-3 mb-10">
            <button @click="scrollTo('top-penjualan','top')" :class="active === 'top' ? activeClass : defaultClass"
                class="px-4 py-2 rounded-full">
                Top Penjualan
            </button>

            <button
                @click="active='rekomendasi'; document.getElementById('rekomendasi').scrollIntoView({behavior:'smooth'})"
                :class="active === 'rekomendasi' ? 'bg-sky-100 text-sky-700' : 'border'"
                class="px-4 py-2 rounded-full">Rekomendasi</button>

            <button @click="active='discount'; document.getElementById('discount').scrollIntoView({behavior:'smooth'})"
                :class="active === 'discount' ? 'bg-sky-100 text-sky-700' : 'border'"
                class="px-4 py-2 rounded-full">Discount</button>
        </div>


        {{-- DISCOUNT --}}
        <div id="discount" class="mb-16">
            <h2 class="text-lg font-semibold mb-4">Discount</h2>

            @if ($discounts->count())
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    @foreach ($discounts as $product)
                        @include('components.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic">Tidak ada produk diskon.</p>
            @endif
        </div>


        {{-- TOP SALES --}}
        <div id="top-penjualan" class="mb-16">
            <h2 class="text-lg font-semibold mb-4">Top Penjualan</h2>

            @if ($topSales->count())
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @foreach ($topSales as $product)
                        @include('components.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic">Belum ada data.</p>
            @endif
        </div>


        {{-- REKOMENDASI --}}
        <div id="rekomendasi" class="mb-16">
            <h2 class="text-lg font-semibold mb-4">Rekomendasi</h2>

            @if ($recommendedProducts->count())
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    @foreach ($recommendedProducts as $product)
                        @include('components.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic">Belum ada rekomendasi.</p>
            @endif
        </div>


        {{-- ALL PRODUCTS --}}
        <div id="products" class="mb-16">
            <h2 class="text-lg font-semibold mb-4">Product</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    @include('components.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>


        <div x-show="modalOpen" x-transition x-cloak class="fixed inset-0 z-50 flex items-center justify-center">

            <!-- overlay -->
            <div @click="modalOpen=false" class="absolute inset-0 bg-black/40"></div>

            <!-- modal -->
            <div class="relative bg-white rounded-2xl w-full max-w-lg p-6">

                <!-- header -->
                <div class="flex justify-between mb-4">
                    <h3 class="font-semibold text-lg" x-text="selectedProduct ? selectedProduct.title : ''">
                    </h3>

                    <button @click="modalOpen=false">✕</button>
                </div>

                <!-- image -->
                <div class="h-56 rounded mb-4 overflow-hidden bg-gray-100">
                    <img :src="selectedProduct && selectedProduct.image ?
                        '/storage/' + selectedProduct.image :
                        '/no-image.png'"
                        class="w-full h-full object-cover">
                </div>

                <!-- price -->
                <p class="text-xl font-bold mb-3 text-sky-600" x-text="formatPrice(selectedProduct?.price)">
                </p>

                <!-- description -->
                <p class="text-sm text-gray-600 mb-4" x-text="selectedProduct ? selectedProduct.description : ''">
                </p>

                <!-- actions -->
                <div class="flex gap-3">

                    <!-- detail -->
                    <a :href="selectedProduct ? '/product/' + selectedProduct.id : '#'"
                        class="flex-1 text-center py-2 bg-sky-600 text-white rounded hover:bg-sky-700">
                        Lihat Detail
                    </a>

                    <!-- compare -->
                    <button @click="selectedProduct && toggle(String(selectedProduct.id))"
                        :class="isSelected(selectedProduct?.id) ?
                            'bg-green-500 text-white border-green-500' :
                            'border'"
                        class="flex-1 py-2 rounded hover:bg-gray-50 transition">

                        <span
                            x-text="isSelected(selectedProduct?.id) 
                    ? '✓ Dipilih' 
                    : 'Bandingkan'">
                        </span>

                    </button>

                </div>
            </div>
        </div>

    </div>


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
