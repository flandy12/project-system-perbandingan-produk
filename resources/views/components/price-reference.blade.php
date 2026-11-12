@php($priceReference = $product->reference_metadata['price_reference'] ?? null)
@if($priceReference)
    <details class="text-xs text-gray-500 mt-2 mb-3">
        <summary class="cursor-pointer">{{ $product->active_discount_percentage ? 'Sumber harga dasar: ' : '' }}{{ $product->price_label }} · sumber dan tanggal</summary>
        <p class="mt-2"><a href="{{ $priceReference['url'] }}" class="underline" target="_blank" rel="noopener noreferrer">{{ $priceReference['seller'] }}</a> · dicatat {{ $priceReference['checked_at'] }}</p>
        <p class="mt-1">{{ $priceReference['note'] }}</p>
        @if($product->active_discount_percentage)
            <p class="mt-1">Harga setelah diskon merupakan promo toko di aplikasi ini. Harga dasar dari sumber tetap Rp {{ number_format($product->price, 0, ',', '.') }}.</p>
        @endif
        <p class="mt-1">Harga dapat berubah. Konfirmasikan varian, stok, dan harga akhir pada penjual.</p>
    </details>
@endif
