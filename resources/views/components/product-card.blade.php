<div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden cursor-pointer"
     @click="
        trackClick({{ $product->id }});
        selectedProduct={{ $product->toJson() }};
        modalOpen=true
     ">

    <div class="relative w-full aspect-[4/5] bg-sky-200">
        @if ($product->image)
            <img src="{{ asset('storage/' . $product->image) }}"
                 class="absolute inset-0 w-full h-full object-cover">
        @endif
    </div>

    <div class="p-4">
        <p class="text-center font-semibold truncate">
            {{ $product->title }}
        </p>

        <p class="text-center font-semibold text-sky-600">
            Rp {{ number_format($product->price, 0, ',', '.') }}
        </p>
    </div>
</div>
