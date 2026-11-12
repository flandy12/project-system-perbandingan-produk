<x-app-layout>
<div class="max-w-7xl mx-auto px-6 py-8 space-y-6">
    <div><p class="text-sm text-blue-700 font-semibold">PENGELOLAAN DATA</p><h1 class="text-2xl font-bold text-gray-900 mt-2">Kesiapan data dan impor penjualan</h1><p class="text-gray-600 mt-2">Periksa sumber dan cakupan penjualan bulanan sebelum digunakan dalam analisis rekomendasi.</p></div>
    @if($errors->any())<div class="p-4 bg-red-50 text-red-800 rounded-xl">@foreach($errors->all() as $error)<p>{{ $error }}</p>
@endforeach</div>
@endif
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-lg font-semibold">Impor Excel</h2><p class="text-gray-600 mt-2">Unduh template, isi penjualan sebenarnya, lalu unggah untuk melihat pratinjau. Nol berarti tidak ada unit terjual. Kolom kosong berarti data belum tersedia.</p>
        <div class="flex flex-wrap gap-4 mt-5"><a href="{{ route('sales-import.template') }}" class="px-5 py-3 rounded-lg border text-blue-700 font-semibold">Unduh template Excel</a><a href="{{ route('product-sales-stats.index') }}" class="px-5 py-3 rounded-lg border">Kelola penjualan manual</a></div>
        @can('create.product.sales')
        <form method="POST" action="{{ route('sales-import.preview') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-4 mt-5">@csrf<input type="file" name="file" accept=".xlsx" required class="border rounded-lg p-2"><button class="px-5 py-3 bg-blue-600 text-white rounded-lg">Periksa berkas</button></form>
        @endcan
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex flex-wrap justify-between gap-4"><div><h2 class="text-lg font-semibold">Cakupan periode</h2><p class="text-sm text-gray-600 mt-1">Periksa kelengkapan harga, indeks fitur tetap, dan laporan penjualan untuk periode yang dipilih.</p></div><form class="flex gap-2"><select name="month" class="rounded-lg border">@for($i=1;$i<=12;$i++)<option value="{{ $i }}" @selected($i==$month)>{{ $i }}</option>@endfor</select><input name="year" type="number" min="2000" max="{{ now()->year }}" value="{{ $year }}" class="rounded-lg border w-28"><button class="px-4 py-2 border rounded-lg">Tampilkan</button></form></div>
        <div class="overflow-x-auto mt-5"><table class="w-full text-sm text-left"><thead class="bg-gray-50"><tr><th class="p-3">Kode / produk</th><th class="p-3">Harga dasar</th><th class="p-3">Indeks fitur tetap</th><th class="p-3">Penjualan (unit)</th><th class="p-3">Sumber dan status</th></tr></thead><tbody class="divide-y align-top">
        @foreach($products as $product) @php($stat=$product->salesStats->first())<tr><td class="p-3"><span class="text-gray-500">{{ $product->product_code ?? 'Kode belum dibuat' }}</span><p class="font-medium mt-1">{{ $product->title }}</p><p class="text-gray-500 mt-1">{{ config('stb_analysis.types.'.$product->comparison_type, 'Jenis perangkat belum dipilih') }}</p></td><td class="p-3">{{ $product->price === null ? 'Belum tersedia' : 'Rp '.number_format($product->price,0,',','.') }}</td><td class="p-3">
            @php($index=$featureIndices[$product->id])
            @if(!$index['required'])<p class="text-amber-800">Jenis perangkat belum memiliki rubrik.</p>@endif
            <p class="font-medium">{{ $index['score'] === null ? 'Belum dapat dihitung' : number_format($index['score'],1,',','.').' / 100' }}</p>
            <p class="text-gray-500 mt-1">{{ $index['complete'] }} / {{ $index['required'] }} kriteria lengkap</p>
            <details class="mt-2"><summary class="text-blue-700 cursor-pointer">Dasar indeks</summary><div class="mt-2 space-y-2 min-w-64">
            @foreach($index['criteria'] as $criterion)
                <p><strong>{{ $criterion['name'] }}</strong><br>
                @if($criterion['reason'])
                    <span class="text-amber-800">{{ $criterion['reason'] }}</span>
                @else
                    {{ $criterion['value'] }} {{ $criterion['unit'] }} · acuan {{ $criterion['reference'] }} · bobot {{ $criterion['weight'] }} · nilai {{ number_format($criterion['score'],2,',','.') }}
                    @foreach($criterion['sources'] as $source)
                        @if(preg_match('~^https?://~i', $source['url'] ?? ''))
                            <a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer" class="block text-blue-700 underline">Halaman sumber</a>
                        @elseif(($source['type'] ?? '') === 'photo' && !empty($source['path']))
                            <a href="{{ asset('storage/'.$source['path']) }}" target="_blank" rel="noopener noreferrer" class="block text-blue-700 underline">Foto kemasan sumber</a>
                        @endif
                    @endforeach
                @endif
                </p>
            @endforeach
            </div></details>
        </td><td class="p-3">{{ $stat ? number_format($stat->total_sold,0,',','.') : 'Belum tersedia' }}</td><td class="p-3">@if(!$stat)Belum ada laporan periode ini
@elseif($stat->is_simulated)Simulasi — dikecualikan dari analisis nyata
@elseif($stat->verification_status !== 'verified' || !$stat->source || !$stat->source_reference)Belum diverifikasi
@else
Terverifikasi: {{ $stat->source }}
@endif @if($stat && $stat->source_reference)<p class="text-gray-500 mt-1 break-words">{{ $stat->source_reference }}</p>
@endif</td></tr>
@endforeach
        </tbody></table></div>
    </div>
    <div class="bg-white rounded-xl border p-6"><h2 class="text-lg font-semibold">Rubrik fitur: {{ config('stb_analysis.feature_version') }}</h2><p class="text-gray-600 mt-2">{{ config('stb_analysis.feature_note') }}</p><p class="text-gray-600 mt-2">Nilai kriteria = minimum(nilai asli ÷ acuan × 100, 100). Indeks = rata-rata berbobot seluruh kriteria wajib. Spesifikasi yang belum diketahui menahan perhitungan, bukan dianggap nol. USB menunjukkan jumlah port, bukan versi USB. Dukungan WiFi dapat memerlukan dongle sesuai spesifikasi produk.</p><p class="text-gray-600 mt-2">Indeks ini tetap saat pembanding berubah. Skor pada halaman perbandingan tetap mengikuti kriteria bersama dua produk. Nilai antarjenis perangkat memakai rubrik berbeda dan tidak dibandingkan langsung. Nilai 100 berarti mencapai acuan rubrik, bukan produk sempurna atau terbaik untuk semua kebutuhan.</p></div>
    <div class="bg-white rounded-xl border p-6"><h2 class="text-lg font-semibold">Riwayat impor Anda</h2><div class="divide-y mt-3">@forelse($batches as $batch)<a href="{{ route('sales-import.show',$batch) }}" class="block py-3 text-blue-700">{{ $batch->filename }} <span class="text-gray-500">— {{ $batch->status === 'applied' ? 'Sudah diterapkan' : 'Pratinjau' }} · {{ $batch->created_at->format('d/m/Y H:i') }}</span></a>@empty<p class="text-gray-500 py-3">Belum ada impor.</p>
@endforelse</div></div>
</div>
</x-app-layout>
