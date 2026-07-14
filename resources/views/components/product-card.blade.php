<div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden cursor-pointer"
    @click="
        trackClick({{ $product->id }});
        setProduct(@js($product));
     ">


    <div class="relative w-full aspect-[4/5] bg-sky-200">
        @if ($product->image)
            <img src="{{ asset('storage/' . $product->image) }}" class="absolute inset-0 w-full h-full object-cover">
        @endif
    </div>

    <div class="p-4">
        <p class="text-center font-semibold truncate">
            {{ $product->title }}
        </p>

        <p class="text-center font-semibold text-sky-600">
            Rp {{ number_format($product->price, 0, ',', '.') }}
        </p>

        <a href="{{ route('product.show', $product->id) }}" @click.stop
            class="block w-full text-center py-2 bg-sky-600 text-white rounded hover:bg-sky-700 transition">
            Lihat Detail
        </a>
    </div>

</div>
