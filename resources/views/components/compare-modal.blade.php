<div x-show="modalOpen" x-transition x-cloak class="fixed inset-0 z-50 flex items-center justify-center">

            <!-- overlay -->
            <div @click="modalOpen=false" class="absolute inset-0 bg-black/40"></div>

            <!-- modal -->
            <div class="relative bg-white rounded-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">

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
                        class="w-full h-full object-contain">
                </div>

                <!-- price -->
                <p x-show="selectedProduct?.discount" class="text-sm text-red-600 mb-1"><span class="line-through text-gray-400" x-text="formatPrice(selectedProduct?.originalPrice)"></span> · Diskon <span x-text="selectedProduct?.discount"></span>%</p>
                <p class="text-xl font-bold mb-3 text-sky-600" x-text="formatPrice(selectedProduct?.price)">
                </p>

                <p class="text-xs text-gray-500 mb-3" x-text="selectedProduct?.priceLabel || ''"></p>
                <!-- description -->
                <p class="text-sm text-gray-600 mb-4" x-text="selectedProduct ? selectedProduct.description : ''">
                </p>

                <ul class="space-y-2 text-sm text-gray-700 mb-5">
                    <template x-for="(feature, index) in (selectedProduct?.highlights || [])" :key="index">
                        <li class="flex gap-2"><span class="text-sky-600">✓</span><span x-text="feature"></span></li>
                    </template>
                </ul>
                <div x-show="items.length === 1" class="text-sm bg-sky-50 text-sky-800 rounded p-3 mb-4">
                    <p>Satu produk dipilih. Pilih produk kedua untuk melihat perbandingan.</p>
                    <button class="underline mt-1" @click="clearSelection()">Batalkan pilihan</button>
                </div>

                <!-- actions -->
                <div class="flex gap-3">

                    <!-- detail -->
                    <a :href="selectedProduct ? '/item/' + selectedProduct.id : '#'"
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
