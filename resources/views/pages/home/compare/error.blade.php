<x-frontend-layout>

    <div class="max-w-6xl mx-auto py-10 px-4">

        <h1 class="text-2xl font-bold mb-6">
            Compare Product
        </h1>

        {{-- ERROR --}}
        @if($error)

            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ $error }}
            </div>

        @endif

        {{-- EMPTY --}}
        @if($products->isEmpty())

           <a href="{{ route('home') }}"
              class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-600 transition">
               Kembali ke Home
            </a>

        @else

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                @foreach($products as $product)

                    <div class="border rounded-2xl p-5">

                        <img
                            src="{{ asset('storage/' . $product->image) }}"
                            class="w-full h-64 object-cover rounded-xl mb-4"
                        >

                        <h2 class="text-xl font-semibold mb-2">
                            {{ $product->title }}
                        </h2>

                        <p class="text-sky-600 font-bold text-lg mb-4">
                            Rp {{ number_format($product->price,0,',','.') }}
                        </p>

                        <p class="text-gray-600">
                            {{ $product->description }}
                        </p>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

</x-app-layout>