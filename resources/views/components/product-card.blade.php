<div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden cursor-pointer h-full"
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

        @php
            $discount = $product->discounts->first();

            $discountPercent = $discount?->percentage ?? 0;

            $finalPrice =
                $discountPercent > 0 ? $product->price - ($product->price * $discountPercent) / 100 : $product->price;
        @endphp

        @if ($discountPercent > 0)
            <div class="text-center flex g-3 justify-center">
                <p class="text-sm text-gray-400 line-through">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </p>

                <p class="font-semibold text-red-600">
                    Rp {{ number_format($finalPrice, 0, ',', '.') }}
                </p>

                <span class="inline-block mt-1 px-2 py-1 text-xs bg-red-100 text-red-600 rounded">
                    -{{ $discountPercent }}%
                </span>
            </div>
        @else
            <p class="text-center font-semibold text-sky-600">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </p>
        @endif
        <a href="{{ route('product.show', $product->id) }}" @click.stop
            class="block w-full text-center py-2 bg-sky-600 text-white rounded hover:bg-sky-700 transition">
            Lihat Detail
        </a>
    </div>

</div>
