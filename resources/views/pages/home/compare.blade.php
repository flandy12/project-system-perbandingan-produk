<x-frontend-layout>

<div class="max-w-7xl mx-auto px-4 py-10 bg-slate-50">

    {{-- HEADER --}}
    <div class="grid grid-cols-3 items-center mb-8 text-center border p-20 bg-white">

        {{-- A --}}
        <div>
            <div class="w-28 h-28 mx-auto mb-2 bg-gray-100 rounded-xl overflow-hidden">
                @if($productA->image)
                    <img src="{{ asset('storage/' . $productA->image) }}" class="w-full h-full object-cover">
                @endif
            </div>
            <p class="font-semibold text-gray-700">{{ $productA->title }}</p>
        </div>

        {{-- VS --}}
        <div>
            <div class="inline-flex items-center justify-center w-12 h-12 bg-[#F04D4D] text-white rounded-full font-bold shadow">
                VS
            </div>
        </div>

        {{-- B --}}
        <div>
            <div class="w-28 h-28 mx-auto mb-2 bg-gray-100 rounded-xl overflow-hidden">
                @if($productB->image)
                    <img src="{{ asset('storage/' . $productB->image) }}" class="w-full h-full object-cover">
                @endif
            </div>
            <p class="font-semibold text-black">{{ $productB->title }}</p>
        </div>

    </div>

    {{-- MAIN --}}
    <div class="grid md:grid-cols-2 gap-10 items-start">

        {{-- LEFT --}}
        <div class="bg-white rounded-2xl shadow p-6">

            @if($hasData)
                @if($hasDemoData)
                    <p class="text-sm text-amber-700 mb-3">Data simulasi untuk demonstrasi grafik, bukan penilaian produk sebenarnya.</p>
                @endif
                @include('components.comparison-chart')
                <p class="text-sm text-gray-500 mt-4">Berdasarkan {{ $comparedCount }} spesifikasi yang memiliki skor pada kedua produk. Setiap kelompok memiliki bobot yang sama. Skor mengikuti aturan penilaian yang digunakan dan bukan hasil uji performa perangkat.</p>
                @if(count($missingGroups))
                    <p class="text-sm text-amber-700 mt-2">Kelompok tanpa skor bersama: {{ implode(', ', $missingGroups) }}. Kelompok ini tidak dihitung.</p>
                @endif
            @else
                <div class="text-center py-12 text-gray-600">
                    <p class="font-semibold">Data skor belum tersedia</p>
                    <p class="text-sm mt-2">Grafik akan tampil setelah skor spesifikasi yang sama diisi untuk kedua produk.</p>
                </div>
            @endif

            @if(collect([$productA, $productB])->contains(fn($item) => ($item->reference_metadata['catalogue_key'] ?? null) === 'apple-kuning' && collect($item->reference_metadata['specifications'] ?? [])->contains(fn($proof) => ($proof['key'] ?? null) === 'usb' && ($proof['reference'] ?? null) === null)))
                <p class="text-sm text-amber-700 mt-3">Jumlah USB Apple Kuning berbeda antar sumber, sehingga tidak dimasukkan ke skor.</p>
            @endif

            <p class="text-sm text-gray-500 mt-3">Skor berlaku untuk pasangan ini. Mengganti produk pembanding dapat mengubah kriteria yang tersedia dan skor akhir. Harga tidak masuk penilaian.</p>
            <details class="mt-4 text-sm">
                <summary class="cursor-pointer font-semibold">Lihat dasar perhitungan skor</summary>
                <p class="text-gray-500 my-3">Skor kriteria = nilai ÷ acuan × 100 (maksimum 100). Rata-rata kriteria dihitung per kelompok, lalu rata-rata kelompok menjadi skor pasangan. Acuan adalah rubrik aplikasi, bukan nilai uji produsen.</p>
                <div class="overflow-x-auto"><table class="w-full text-xs text-left">
                    <thead><tr class="border-b"><th class="py-2 pr-3">Kriteria / kelompok</th><th class="py-2 pr-3">{{ $productA->title }}</th><th class="py-2">{{ $productB->title }}</th></tr></thead>
                    <tbody>@foreach($scoreCriteria as $criterion)
                        <tr class="border-b"><td class="py-2 pr-3">{{ $criterion['name'] }}<span class="block text-gray-500">{{ $criterion['group'] }}</span></td>
                        @foreach(['A', 'B'] as $side)
                            <td class="py-2 pr-3">{{ $criterion['raw'.$side] }} {{ $criterion['unit'] }}<span class="block text-gray-500">Acuan: {{ $criterion['reference'.$side] ?? '—' }} · skor {{ number_format($criterion[strtolower($side)], 2, ',', '.') }}</span></td>
                        @endforeach</tr>
                    @endforeach</tbody>
                </table></div>
                <ul class="mt-3 space-y-1">@foreach($labels as $index => $label)
                    <li>{{ $label }}: {{ $metricsA[$index] === null ? '—' : number_format($metricsA[$index], 2, ',', '.') }} vs {{ $metricsB[$index] === null ? '—' : number_format($metricsB[$index], 2, ',', '.') }}</li>
                @endforeach</ul>
            </details>
            {{-- SCORE --}}
            <div class="flex justify-center gap-10 mt-6 text-center">
                <div>
                    <p class="text-3xl font-bold text-blue-500">{{ $percentA === null ? '—' : number_format($percentA, 1, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">Skor pasangan / 100</p>
                </div>

                <div>
                    <p class="text-3xl font-bold text-red-500">{{ $percentB === null ? '—' : number_format($percentB, 1, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">Skor pasangan / 100</p>
                </div>
            </div>


        </div>

        {{-- RIGHT --}}
        <div>

            @foreach([$productA, $productB] as $item)
                @if($item->reference_metadata)
                    <p class="text-sm text-gray-500 mb-3">
                        {{ $item->title }}:
                        @if($item->reference_metadata['source_url'] ?? null)
                            <a href="{{ $item->reference_metadata['source_url'] }}" class="underline">sumber resmi</a> dan foto kemasan.
                        @else
                            data dari foto kemasan; spesifikasi lainnya belum terverifikasi.
                        @endif
                    </p>
                @endif
            @endforeach

            {{-- TITLE --}}
            <h3 class="font-bold text-lg mb-4">
                @if(!$hasData)
                    Hasil perbandingan belum tersedia
                @elseif($comparisonStatus === 'different_type')
                    Perbandingan perangkat dengan jenis berbeda
                @elseif($comparisonStatus === 'insufficient')
                    Data perbandingan masih terbatas
                @elseif($winner === null)
                    Skor pada data bersama sama
                @else
                    Mengapa <span class="{{ $winner === 'a' ? 'text-blue-500' : 'text-red-500' }}">{{ $winner === 'a' ? $productA->title : $productB->title }}</span> unggul berdasarkan skor?
                @endif
            </h3>

            {{-- REASONS --}}
            <div class="bg-white rounded-xl shadow p-5 mb-6">
                <ul class="space-y-3 text-sm">
                    @foreach ($reasons as $reason)
                        <li class="flex items-start gap-2">
                            <span class="text-[#13baf7] font-bold">✔</span>
                            <span>{{ $reason }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            @foreach([$productA, $productB] as $priceProduct)
                <p class="text-xs font-semibold text-gray-600">{{ $priceProduct->title }}</p>
                @include('components.price-reference', ['product' => $priceProduct])
            @endforeach
            {{-- QUICK COMPARISON --}}
            <div class="bg-white rounded-xl shadow p-5">

                <h4 class="font-semibold mb-3 text-gray-700">Perbandingan Cepat</h4>

                <div class="space-y-3 text-sm">

                    {{-- PRICE --}}
                    <div class="flex justify-between">
                        <span>{{ $productA->formatted_price }}</span>
                        <span class="text-gray-400">Harga</span>
                        <span>{{ $productB->formatted_price }}</span>
                    </div>

                    {{-- SOLD --}}
                    <div class="flex justify-between">
                        <span>{{ $productA->sales_stats_count ? number_format($productA->sold ?? 0, 0, ',', '.') : '—' }}</span>
                        <span class="text-gray-400 text-center text-sm">Terjual terverifikasi<br><small>{{ $salesPeriodLabel }}</small></span>
                        <span>{{ $productB->sales_stats_count ? number_format($productB->sold ?? 0, 0, ',', '.') : '—' }}</span>
                    </div>

                    {{-- RATING --}}
                    <div class="flex justify-between">
                        <span>{{ $productA->rating === null ? '—' : number_format($productA->rating, 1, ',', '.') . ' / 5' }}</span>
                        <span class="text-gray-400">Rating pengguna aplikasi</span>
                        <span>{{ $productB->rating === null ? '—' : number_format($productB->rating, 1, ',', '.') . ' / 5' }}</span>
                    </div>

                </div>

            </div>

        </div>

    </div>

    @if(count($specificationRows))
        <div class="bg-white rounded-2xl shadow p-6 mt-8">
            <h3 class="font-bold text-lg mb-4">Fitur unggulan setiap produk</h3>
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                @foreach([$productA, $productB] as $item)
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-3">{{ $item->title }}</h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            @foreach($item->catalogue_highlights as $highlight)
                                <li class="flex gap-2"><span class="text-sky-600">✓</span><span>{{ $highlight }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            <h3 class="font-bold text-lg mb-2">Perbedaan spesifikasi dan fitur</h3>
            <p class="text-sm text-gray-500 mb-4">Nilai yang lebih tinggi ditandai untuk kapasitas dan jumlah port. Fitur yang hanya tercatat pada satu produk tetap ditampilkan; data kosong tidak dianggap sebagai ketiadaan fitur.</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead><tr class="border-b"><th class="py-3 pr-4">Spesifikasi</th><th class="py-3 pr-4">{{ $productA->title }}</th><th class="py-3">{{ $productB->title }}</th></tr></thead>
                    <tbody>
                        @foreach($specificationRows as $row)
                            <tr class="border-b">
                                <td class="py-3 pr-4 font-medium">{{ $row['name'] }}</td>
                                <td class="py-3 pr-4 {{ $row['advantage'] === 'a' ? 'text-blue-600 font-semibold' : '' }}">{{ $row['a'] }} @if($row['advantage'] === 'a')<span class="block text-xs">Keunggulan spesifikasi</span>@endif</td>
                                <td class="py-3 {{ $row['advantage'] === 'b' ? 'text-red-600 font-semibold' : '' }}">{{ $row['b'] }} @if($row['advantage'] === 'b')<span class="block text-xs">Keunggulan spesifikasi</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>


</x-frontend-layout>
