<x-frontend-layout>
    <div x-data="compare" x-init="init()">
    <div class="relative max-w-7xl mx-auto px-4 mb-12">
        <div id="top-penjualan" class="mb-16    ">
            <div class="flex justify-between mb-6">
                <h2 class="text-lg font-semibold mb-4">Product</h2>
                <div class="flex flex-wrap gap-3 mb-6" x-data="{ open: false }">
                    <form method="GET" action="{{ url()->current() }}" onsubmit="return cleanQuery(this)">
                        <select name="sort" aria-label="Urutan produk" class="border rounded px-3 py-2 text-sm w-48">
                            @foreach(['' => 'Urutkan', 'newest' => 'Produksi terbaru', 'oldest' => 'Produksi tertua', 'best' => 'Rating tertinggi', 'worst' => 'Rating terendah'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('sort', '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="price_min" min="0" placeholder="Harga Min" aria-label="Harga minimum"
                            value="{{ request('price_min') }}" class="border rounded px-3 py-2 text-sm w-32">
                        <input type="number" name="price_max" min="0" placeholder="Harga Max" aria-label="Harga maksimum"
                            value="{{ request('price_max') }}" class="border rounded px-3 py-2 text-sm w-32">
                        <select name="year" aria-label="Tahun produksi" class="border rounded px-3 py-2 text-sm w-48">
                            <option value="">Semua tahun produksi</option>
                            @foreach($productionYears as $year)
                                <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                            @endforeach
                            <option value="unknown" @selected(request('year') === 'unknown')>Tahun belum diisi</option>
                        </select>

                        <button type="submit" class="bg-[#111727] text-white px-4 py-2 rounded">
                            Filter
                        </button>
                    </form>

                </div>
            </div>

            <p class="text-xs text-gray-500 mb-4">Urutan rating memakai ulasan pengguna aplikasi; produk tanpa ulasan berada di belakang. Tahun produksi memakai data pada formulir produk, bukan tanggal penambahan katalog.</p>
            @if($errors->any())
                <p class="text-sm text-red-600 mb-4">{{ $errors->first() }}</p>
            @endif
            @if ($products->count())
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 cursor-pointer">
                    @foreach ($products as $product)
                        @include('components.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic">Tidak ada produk yang cocok dengan filter.</p>
            @endif
        </div>
    </div>


    <div class="max-w-7xl mx-auto px-4 mb-6">{{ $products->withQueryString()->links() }}</div>
    @include('components.compare-modal')
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
