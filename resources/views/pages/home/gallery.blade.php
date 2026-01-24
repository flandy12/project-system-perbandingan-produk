<x-frontend-layout>
    <div class="relative max-w-7xl mx-auto px-4 mb-12">
        <div id="top-penjualan" class="mb-16">
            <h2 class="text-lg font-semibold mb-4">Product</h2>

            @if (!empty($products))
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 cursor-pointer">
                    @foreach ($products as $product)
                        <div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden">

                            <!-- Image wrapper with fixed ratio -->
                            <div class="relative w-full aspect-[4/5] bg-sky-200">
                                @if ($product->image ?? false)
                                    <img src="{{ asset('storage/' . $product->image) }}"
                                        class="absolute inset-0 w-full h-full object-cover" alt="{{ $product->title }}">
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="p-4">
                                <p class="text-center font-semibold truncate">
                                    {{ $product->title }}
                                </p>

                                <p class="text-center font-semibold text-sky-600">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
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

</x-frontend-layout>
