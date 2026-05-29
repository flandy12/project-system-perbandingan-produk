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

                        <div class="mt-6">
                            <h2 class="text-4xl font-bold text-green-600">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </h2>
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
                                        {{ $averageRating }}
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
                                        {{ number_format($product->sold ?? 0) }}
                                    </h2>
                                </div>

                                <div class="bg-white rounded-2xl p-6 shadow-sm border">
                                    <p class="text-gray-500 text-sm">Views</p>
                                    <h2 class="text-3xl font-bold mt-2">
                                        {{ number_format($product->views ?? 0) }}
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

                @forelse($product->ratings as $review)
                    <div class="border-b py-4">

                        <div class="flex justify-between">

                            <div>

                                <h4 class="font-semibold">
                                    {{ $review->user->name ?? 'Anonymous' }}
                                </h4>

                                <div class="text-yellow-500">
                                    {{ str_repeat('⭐', $review->rating) }}
                                </div>

                            </div>

                            <div class="text-sm text-gray-500">
                                {{ $review->created_at->diffForHumans() }}
                            </div>

                        </div>

                        @if (!empty($review->review))
                            <p class="mt-3 text-gray-600">
                                {{ $review->review }}
                            </p>
                        @endif

                    </div>

                @empty

                    <div class="text-center py-10">

                        <div class="text-5xl mb-4">
                            ⭐
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800">
                            Belum Ada Review
                        </h3>

                        <p class="text-gray-500 mt-2 max-w-md mx-auto">
                            Jadilah yang pertama memberikan ulasan untuk produk ini.
                            Bagikan pengalaman Anda agar pengguna lain dapat membuat keputusan yang lebih baik.
                        </p>

                        @guest
                            <div class="mt-6">
                                <a href="{{ route('login') }}"
                                    class="inline-flex items-center px-5 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Login untuk Menulis Review
                                </a>
                            </div>
                        @else
                            <div class="mt-6">
                                <button
                                    onclick="document.getElementById('review-form').scrollIntoView({ behavior: 'smooth' })"
                                    class="inline-flex items-center px-5 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Tulis Review Pertama
                                </button>
                            </div>
                        @endguest

                    </div>
                @endforelse

            </div>

        </div>
    </div>

    @push('js')
    @endpush

</x-frontend-layout>
