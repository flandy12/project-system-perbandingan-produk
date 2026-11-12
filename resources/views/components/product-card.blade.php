<div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden cursor-pointer"
    @click="
        trackClick({{ $product->id }});
        setProduct(@js(['id' => $product->id, 'title' => $product->title, 'image' => $product->image, 'price' => $product->selling_price, 'originalPrice' => $product->price, 'discount' => $product->active_discount_percentage, 'description' => $product->catalogue_summary, 'highlights' => $product->catalogue_highlights, 'priceLabel' => ($product->active_discount_percentage ? 'Sumber harga dasar: ' : '') . $product->price_label]));
     ">


    <div class="relative w-full aspect-[4/5] bg-sky-200">
        @if ($product->image)
            <img src="{{ asset('storage/' . $product->image) }}" class="absolute inset-0 w-full h-full object-cover">
        @endif
    </div>

    <div class="p-4">
        @if($product->recommendation_reason)
            <p class="text-center text-xs text-sky-700 bg-sky-50 rounded-lg px-2 py-2 mb-3">{{ $product->recommendation_reason }}</p>
        @endif
        <p class="text-center font-semibold truncate">
            {{ $product->title }}
        </p>

        @if($product->active_discount_percentage && $product->price !== null)
            <p class="text-center text-xs text-red-600 mb-1"><span class="line-through text-gray-400">Rp {{ number_format($product->price, 0, ',', '.') }}</span> · Diskon {{ $product->active_discount_percentage }}%</p>
        @endif
        <p class="text-center font-semibold text-sky-600">
            {{ $product->formatted_price }}
        </p>

        <p class="text-center text-xs text-gray-500 mb-2">{{ $product->active_discount_percentage ? 'Sumber harga dasar: ' : '' }}{{ $product->price_label }}</p>
        <p class="text-center text-xs text-gray-500 mb-2">Tahun produksi: {{ $product->production_year ?? 'Belum diisi' }}{{ $product->production_year ? ' · data input, belum terverifikasi' : '' }}</p>
        @if(isset($product->ratings_count))
            <p class="text-center text-xs text-gray-500 mb-2">{{ $product->ratings_count ? number_format($product->ratings_avg_rating, 1, ',', '.') . ' / 5 · ' . $product->ratings_count . ' ulasan' : 'Belum ada ulasan' }}</p>
        @endif
        <a href="{{ route('product.show', $product->id) }}" @click.stop
            class="block w-full text-center py-2 bg-sky-600 text-white rounded hover:bg-sky-700 transition">
            Lihat Detail
        </a>
    </div>

</div>
