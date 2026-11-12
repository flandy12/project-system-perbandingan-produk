<x-frontend-layout>

    <div class="bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-8">

            {{-- PRODUCT --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="grid lg:grid-cols-2 gap-8 lg:gap-10 p-5 sm:p-8">

                    <div>
                        <div class="rounded-xl overflow-hidden border">
                            <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-auto max-h-[500px] object-contain bg-slate-50"
                                alt="{{ $product->title }}">
                        </div>
                    </div>

                    <div>

                        @if (isset($product->category))
                            <div class="mb-3">
                                <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-xs">
                                    {{ $product->category->name }}
                                </span>
                            </div>
                        @endif

                        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 leading-tight">
                            {{ $product->title }}
                        </h1>

                        <div class="mt-6">
                            @if($product->active_discount_percentage && $product->price !== null)
                                <p class="text-sm text-red-600 mb-2"><span class="line-through text-gray-400">Rp {{ number_format($product->price, 0, ',', '.') }}</span> · Diskon {{ $product->active_discount_percentage }}%</p>
                            @endif
                            <h2 class="text-3xl sm:text-4xl font-bold text-sky-600 tracking-tight">
                                {{ $product->formatted_price }}
                            </h2>
                            @if($product->official_url && in_array(strtolower(parse_url($product->official_url, PHP_URL_SCHEME) ?? ''), ['https', 'http']))
                                <a href="{{ $product->official_url }}" target="_blank" rel="noopener noreferrer" aria-label="Kunjungi website resmi" class="official-product-link group mt-6 flex items-center gap-4 rounded-2xl border border-sky-100 bg-sky-50 p-4 sm:p-5 hover:border-sky-300 hover:shadow-md transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-sky-600">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-sky-600 shadow-sm">
                                        <svg aria-hidden="true" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" d="M3 9l2-5h14l2 5M3 9h18v3a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0V9Zm2 6v5h14v-5M9 20v-4h6v4"/></svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-xs font-medium text-sky-700">Website resmi produk</span>
                                        <span class="block font-semibold text-slate-900 mt-1">Lihat produk & ketersediaan</span>
                                        <span class="block text-xs text-slate-500 mt-1 break-all">{{ parse_url($product->official_url, PHP_URL_HOST) }} · Buka di tab baru</span>
                                    </span>
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-600 text-white group-hover:bg-sky-700 transition">
                                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M7 7h10v10"/></svg>
                                    </span>
                                </a>
                            @endif
                        </div>

                        <p class="text-sm text-gray-500 mt-3">Tahun produksi: {{ $product->production_year ?? 'Belum diisi' }}{{ $product->production_year ? ' · data input, belum terverifikasi' : '' }}</p>
                        @include('components.release-reference', ['product' => $product])
                        @include('components.price-reference', ['product' => $product])
                        <hr class="my-8">

                        <div>
                            <h3 class="font-bold text-lg mb-3">
                                Deskripsi
                            </h3>

                            <div class="prose max-w-none">
                                {!! app(\App\Services\SafeProductHtml::class)->clean($product->description ?? '') !!}
                            </div>


                            @if($product->reference_metadata)
                                <details class="mt-5 border rounded-xl p-4">
                                    <summary class="cursor-pointer font-semibold">Spesifikasi dan asal data</summary>
                                    <div class="overflow-x-auto mt-3">
                                        <table class="w-full text-sm text-left">
                                            <thead><tr class="border-b"><th class="py-2 pr-3">Spesifikasi</th><th class="py-2 pr-3">Nilai</th><th class="py-2">Sumber</th></tr></thead>
                                            <tbody>
                                                @foreach($product->specifications as $item)
                                                    @php($reference = $product->reference_metadata['specifications'][$item->specification_id] ?? null)
                                                    @if($reference && ($reference['public'] ?? true) && $item->specification)
                                                        <tr class="border-b">
                                                            <td class="py-2 pr-3">{{ $item->specification->name }}</td>
                                                            <td class="py-2 pr-3">{{ $item->specification->data_type === 'boolean' ? ($item->value == '1' ? 'Didukung' : 'Tidak didukung') : $item->value . ($item->specification->unit ? ' ' . $item->specification->unit : '') }}</td>
                                                            <td class="py-2">
                                                                @if(isset($reference['verified_value']) && (is_numeric($reference['verified_value']) && is_numeric($item->value) ? abs((float) $reference['verified_value'] - (float) $item->value) > 0.000001 : (string) $reference['verified_value'] !== (string) $item->value))
                                                                    <span class="text-amber-700">Nilai berubah; menunggu verifikasi ulang.</span>
                                                                @else
                                                                @foreach($reference['sources'] as $source)
                                                                    @if($source['type'] === 'official')
                                                                        <a class="underline" href="{{ $source['url'] }}">Resmi</a>
                                                                    @elseif($source['type'] === 'seller')
                                                                        <a class="underline" href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer">Penjual</a>
                                                                    @else
                                                                        <a class="underline" href="{{ asset('storage/'.$source['path']) }}">Foto</a>
                                                                    @endif
                                                                @endforeach
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            @endif

                            <div class="grid grid-cols-2 gap-3 mt-8 product-statistics">
                                <div class="bg-white rounded-2xl p-6 shadow-sm border">
                                    <p class="text-gray-500 text-sm">Rating pengguna aplikasi</p>
                                    @if($product->ratings->contains(fn ($rating) => str_starts_with($rating->review ?? '', '[SIMULASI PENGUJIAN]')))
                                        <p class="text-amber-700 text-xs">Termasuk ulasan simulasi pengujian</p>
                                    @endif
                                    <h2 class="product-stat-value">
                                        {{ $totalRatings ? number_format($averageRating, 1) : '—' }}
                                    </h2>
                                </div>

                                <div class="bg-white rounded-2xl p-6 shadow-sm border">
                                    <p class="text-gray-500 text-sm">Review</p>
                                    <h2 class="product-stat-value">
                                        {{ number_format($totalRatings) }}
                                    </h2>
                                </div>

                                <div class="bg-white rounded-2xl p-6 shadow-sm border">
                                    <p class="text-gray-500 text-sm">Terjual terverifikasi</p>
                                    <p class="text-gray-500 text-xs mt-1">{{ $salesPeriodLabel }}</p>
                                    <h2 class="product-stat-value">
                                        {{ $totalSold === null ? '—' : number_format($totalSold, 0, ',', '.') }}
                                    </h2>
                                </div>

                                <div class="bg-white rounded-2xl p-6 shadow-sm border">
                                    <p class="text-gray-500 text-sm">Views</p>
                                    <h2 class="product-stat-value">
                                        {{ number_format($totalViews) }}
                                    </h2>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>
            </div>

            {{-- DISTRIBUSI RATING --}}
            <div class="bg-white rounded-2xl shadow-sm p-8 mt-8">

                <h2 class="text-xl font-bold mb-6">
                    Distribusi Rating
                </h2>

                @for ($i = 5; $i >= 1; $i--)
                    <div class="flex items-center gap-4 mb-4">

                        <div class="w-10">
                            {{ $i }} ⭐
                        </div>

                        <div class="flex-1 bg-gray-200 h-3 rounded-full">
                            <div class="bg-yellow-400 h-3 rounded-full" style="width: {{ $ratingPercent[$i] ?? 0 }}%">
                            </div>
                        </div>

                        <div class="w-12 text-right">
                            {{ $ratingPercent[$i] ?? 0 }}%
                        </div>

                    </div>
                @endfor

            </div>

            {{-- REVIEW --}}
            <div class="bg-white rounded-2xl shadow-sm p-8 mt-8">

                <h2 class="text-xl font-bold mb-6">
                    Review Terbaru
                </h2>

                @foreach ($reviews as $review)
                    <div class="border-b py-4">

                        <div class="flex justify-between">

                            <div>

                                <strong>{{ $review->user->name }}</strong>

                                <div class="flex mt-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049.927l2.26 4.577 5.05.734-3.655 3.562.863 5.03L9.05 12.347 4.533 14.83l.863-5.03L1.74 6.238l5.05-.734L9.05.927z" />
                                        </svg>
                                    @endfor
                                </div>

                            </div>

                            <span>{{ $review->created_at->diffForHumans() }}</span>

                        </div>

                        <p class="mt-2">
                            {{ $review->comment }}
                        </p>

                    </div>
                @endforeach

                {{-- ===================== --}}
                {{-- FORM --}}
                {{-- ===================== --}}
                @auth

                    <form action="{{ route('products.comments.store', $product) }}" method="POST"
                        class="mt-8 border-t pt-8" x-data="{
                            comment: '',
                            rating: {{ old('rating', 0) }},
                            hover: 0
                        }">

                        @csrf

                        <div class="mb-5">

                            <label class="font-semibold block mb-2">
                                Berikan Rating
                            </label>

                            <input type="hidden" name="rating" x-model="rating">

                            <div class="flex gap-1">

                                <template x-for="star in 5" :key="star">

                                    <svg @mouseenter="hover = star" @mouseleave="hover = 0" @click="rating = star"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        class="w-9 h-9 cursor-pointer transition-all duration-150"
                                        :class="star <= (hover || rating) ?
                                            'text-yellow-400 fill-current scale-110' :
                                            'text-gray-300 fill-current'">

                                        <path
                                            d="M12 .587l3.668 7.431L24 9.748l-6 5.847 1.417 8.268L12 19.771 4.583 23.863 6 15.595 0 9.748l8.332-1.73z" />

                                    </svg>

                                </template>

                            </div>

                            @error('rating')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div class="mb-5">

                            <textarea name="comment" x-model="comment" rows="4"
                                class="w-full border rounded-xl p-4 focus:ring-2 focus:ring-blue-500" placeholder="Tulis komentar Anda..."></textarea>

                            @error('comment')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <button type="submit" :disabled="rating === 0"
                            class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-6 py-3 rounded-xl transition">

                            Kirim Review

                        </button>

                    </form>
                @else
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 mt-8">

                        <p>
                            Silakan login untuk memberikan review.
                        </p>

                        <a href="{{ route('login') }}"
                            class="inline-block mt-4 bg-blue-600 text-white px-5 py-2 rounded-lg">

                            Login

                        </a>

                    </div>

                @endauth

            </div>
        </div>
    </div>

    @push('js')
    @endpush

</x-frontend-layout>
