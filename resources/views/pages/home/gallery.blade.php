<x-frontend-layout>
    <div class="relative max-w-7xl mx-auto px-4 mb-12">
        <div id="top-penjualan" class="mb-16    ">
            <div class="flex justify-between mb-6">
                <h2 class="text-lg font-semibold mb-4">Product</h2>
                <div class="flex flex-wrap gap-3 mb-6" x-data="{ open: false }">
                    <form method="GET" action="{{ url()->current() }}" onsubmit="return cleanQuery(this)">
                        <!-- Sort -->
                        <select name="sort" class="border rounded px-3 py-2 text-sm w-32">
                            <option value="">Urutkan</option>
                            <option value="newest">Terbaru</option>
                            <option value="oldest">Terlama</option>
                            <option value="best">Terbaik</option>
                            <option value="worst">Terburuk</option>
                        </select>

                        <!-- Harga -->
                        <input type="number" name="price_min" placeholder="Harga Min"
                            class="border rounded px-3 py-2 text-sm w-32">

                        <input type="number" name="price_max" placeholder="Harga Max"
                            class="border rounded px-3 py-2 text-sm w-32">

                        <!-- Tahun Saja -->
                        <input type="number" name="year" min="1900" max="2100" placeholder="2026"
                            class="border rounded px-3 py-2 text-sm">

                        <button type="submit" class="bg-[#111727] text-white px-4 py-2 rounded">
                            Filter
                        </button>
                    </form>

                </div>
            </div>

            @if (!empty($products))
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 cursor-pointer">
                    @foreach ($products as $product)
                        @include('components.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic">Belum ada data penjualan.</p>
            @endif
        </div>
    </div>


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


    @push('js')
        <script>
            function cleanQuery(form) {
                const inputs = form.querySelectorAll('input, select');
                let hasValue = false;

                inputs.forEach(input => {
                    if (input.value && input.value.trim() !== '') {
                        hasValue = true;
                    }
                });

                if (!hasValue) {
                    window.location.href = form.action; // redirect tanpa query
                    return false;
                }

                return true;
            }
        </script>
    @endpush
</x-frontend-layout>
