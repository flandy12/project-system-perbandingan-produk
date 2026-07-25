<x-frontend-layout>

    <div class="bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-8">

            {{-- PRODUCT --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="grid lg:grid-cols-2 gap-10 p-8">

                    <div>
                        <div class="rounded-xl overflow-hidden border">
                            <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-[500px] object-cover"
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

                        <h1 class="text-4xl font-bold text-gray-900">
                            {{ $product->title }}
                        </h1>
                        @php
                            $discount = $product->discounts->first();

                            $discountPercent = $discount?->percentage ?? 0;

                            $finalPrice =
                                $discountPercent > 0
                                    ? $product->price - ($product->price * $discountPercent) / 100
                                    : $product->price;
                        @endphp

                        <div class="mt-6">
                            @if ($discountPercent > 0)
                                <p class="text-xl text-gray-400 line-through">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </p>

                                <h2 class="text-4xl font-bold text-green-600">
                                    Rp {{ number_format($finalPrice, 0, ',', '.') }}
                                </h2>

                                <span
                                    class="inline-flex items-center mt-2 px-3 py-1 rounded-full bg-red-100 text-red-600 text-sm font-semibold">
                                    Hemat {{ $discountPercent }}%
                                </span>
                            @else
                                <h2 class="text-4xl font-bold text-green-600">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </h2>
                            @endif
                        </div>

                        <hr class="my-8">

                        <div>
                            <h3 class="font-bold text-lg mb-3">
                                Deskripsi
                            </h3>

                            <div class="prose max-w-none">
                                {!! $product->description !!}
                            </div>


                            {{-- ANALYTICS --}}
                            <div class="grid md:grid-cols-4 gap-5 mt-8">

                                <div class="bg-white rounded-2xl p-6 shadow-sm border">
                                    <p class="text-gray-500 text-sm">Rating</p>
                                    <h2 class="text-3xl font-bold mt-2">
                                        {{ number_format($averageRating, 1) }}
                                    </h2>
                                </div>

                                <div class="bg-white rounded-2xl p-6 shadow-sm border">
                                    <p class="text-gray-500 text-sm">Review</p>
                                    <h2 class="text-3xl font-bold mt-2">
                                        {{ number_format($totalRatings) }}
                                    </h2>
                                </div>

                                <div class="bg-white rounded-2xl p-6 shadow-sm border">
                                    <p class="text-gray-500 text-sm">Terjual</p>
                                    <h2 class="text-3xl font-bold mt-2">
                                        {{ number_format($totalSold) }}
                                    </h2>
                                </div>

                                <div class="bg-white rounded-2xl p-6 shadow-sm border">
                                    <p class="text-gray-500 text-sm">Views</p>
                                    <h2 class="text-3xl font-bold mt-2">
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
