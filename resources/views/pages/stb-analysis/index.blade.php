<x-app-layout>
<div class="max-w-7xl mx-auto px-6 py-8 space-y-6">
    <div><p class="text-blue-700 text-sm font-semibold">ANALISIS REKOMENDASI</p><h1 class="text-3xl font-bold mt-2 text-gray-900">Kelompokkan STB sesuai data</h1><p class="text-gray-600 mt-3">Harga, kemampuan terdokumentasi, dan penjualan bulanan dalam satu jenis perangkat. Katalog dan perbandingan tetap tersedia.</p></div>
    @if(session('status'))<p class="bg-green-50 text-green-800 p-4 rounded-xl">{{ session('status') }}</p>@endif
    @if($errors->any())
        <div class="bg-red-50 text-red-800 p-4 rounded-xl">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    @endif
    <div class="bg-white border rounded-2xl p-6 shadow-sm">
        <h2 class="text-xl font-semibold">1. Pilih cakupan dataset</h2>
        <p class="text-gray-600 mt-2">Gunakan bulan yang sudah selesai dan satu sumber laporan dengan cakupan toko, wilayah, serta definisi unit yang sama. Angka marketplace sepanjang waktu tidak dapat menggantikan penjualan bulanan.</p>
        <form method="GET" class="grid md:grid-cols-5 gap-4 mt-5">
            <label class="text-sm">Bulan<select name="month" class="block w-full rounded-lg mt-2">@for($i=1;$i<=12;$i++)<option value="{{ $i }}" @selected($filters['month']==$i)>{{ $i }}</option>@endfor</select></label>
            <label class="text-sm">Tahun<input name="year" type="number" min="2000" max="{{ now()->year }}" value="{{ $filters['year'] }}" class="w-full rounded-lg mt-2"></label>
            <label class="text-sm">Jenis perangkat<select name="type" class="block w-full rounded-lg mt-2">@foreach(config('stb_analysis.types') as $key=>$label)<option value="{{ $key }}" @selected($filters['type']==$key)>{{ $label }}</option>@endforeach</select></label>
            <label class="text-sm">Sumber penjualan<select name="source" class="block w-full rounded-lg mt-2"><option value="">Pilih laporan terverifikasi</option>@foreach($sources as $source)<option value="{{ $source }}" @selected($filters['source']==$source)>{{ $source }}</option>@endforeach</select></label>
            <button class="self-end bg-gray-900 text-white px-5 py-3 rounded-lg">Periksa dataset</button>
        </form>
        <div class="flex flex-wrap gap-6 bg-blue-50 rounded-xl p-5 mt-5"><p><strong class="text-2xl text-blue-800">{{ count($dataset['rows']) }}</strong> produk siap</p><p><strong class="text-2xl">{{ count($dataset['excluded']) }}</strong> belum memenuhi syarat</p><p>Periode {{ sprintf('%02d/%d',$filters['month'],$filters['year']) }}</p></div>
        @if(!$sources->count())<p class="text-amber-800 mt-4">Belum ada sumber penjualan nyata terverifikasi pada periode ini. Lengkapi laporan melalui <a class="underline" href="{{ route('sales-import.index') }}">impor penjualan</a>.</p>@endif
        <p class="text-gray-600 text-sm mt-4">{{ $dataset['price_policy'] }} {{ $dataset['feature_policy'] }} Data kosong tidak dianggap nol. Minimal 3 produk lengkap; setiap model harus dicatat satu kali, dengan varian perangkat yang dapat dibuktikan berbeda.</p>
        @include('pages.stb-analysis.dataset', ['snapshot'=>$dataset])
        @can('create.stb.analysis')
            <form method="POST" action="{{ route('stb-analysis.store') }}" class="flex flex-wrap gap-4 items-end mt-6">@csrf
                @foreach(['year','month','type','source'] as $key)<input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">@endforeach
                <label class="text-sm">Jumlah kelompok<select name="k" class="block rounded-lg mt-2"><option value="">Otomatis — Silhouette tertinggi</option>@for($k=2;$k<=6;$k++)<option value="{{ $k }}">{{ $k }} kelompok</option>@endfor</select></label>
                <button class="bg-blue-700 text-white rounded-lg px-5 py-3 disabled:opacity-50" @disabled(!$filters['source'])>Jalankan analisis</button>
            </form>
        @endcan
    </div>
    @can('create.stb.analysis')
    <details class="bg-white rounded-2xl border p-6"><summary class="text-xl font-semibold cursor-pointer">2. Lengkapi bukti harga periode</summary>
        <p class="text-gray-600 mt-4">Catat nilai yang benar-benar terlihat pada sumber, tanggal pengamatan, dan URL atau nomor dokumen. Harga ini menjadi data analisis, tanpa mengganti harga katalog. Gunakan basis harga perangkat saja secara konsisten.</p>
        <form method="POST" action="{{ route('stb-analysis.price') }}" class="grid md:grid-cols-2 gap-4 mt-5">@csrf
            <label>Produk<select name="product_id" required class="block w-full rounded-lg mt-2">@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_code }} — {{ $product->title }}</option>@endforeach</select></label>
            <label>Harga perangkat (Rp)<input name="price" type="number" min="0.01" step="0.01" required class="block w-full rounded-lg mt-2"></label>
            <label>Tanggal pengamatan<input name="observed_on" type="date" max="{{ today()->toDateString() }}" required class="block w-full rounded-lg mt-2"></label>
            <label>Nama sumber harga<input name="source" required maxlength="255" class="block w-full rounded-lg mt-2" placeholder="Nama toko atau dokumen"></label>
            <label class="md:col-span-2">Referensi bukti<input name="source_reference" required maxlength="2000" class="block w-full rounded-lg mt-2" placeholder="URL listing yang cocok dengan model, atau nomor dokumen"></label>
            <label class="md:col-span-2 flex gap-3"><input name="confirmed" value="1" type="checkbox" required class="mt-1">Saya memeriksa kesesuaian model, nilai harga, dan tanggal dengan bukti sumber.</label>
            <button class="justify-self-start px-5 py-3 rounded-lg bg-blue-700 text-white">Simpan bukti harga</button>
        </form>
        <div class="mt-6 divide-y">@foreach($observations as $price)<p class="py-3 text-sm">{{ $price->product?->title }} · Rp {{ number_format($price->price,0,',','.') }} · {{ $price->observed_on->format('d/m/Y') }}<br><span class="text-gray-500">{{ $price->source }} · {{ $price->source_reference }}</span></p>@endforeach</div>
    </details>
    @endcan
    @can('create.stb.analysis')
    <div class="bg-white rounded-2xl border p-6">
        <p class="text-amber-800 font-semibold text-sm">RUANG UJI — DATA SIMULASI</p>
        <h2 class="text-xl font-semibold mt-2">Uji alur dengan data berbasis katalog</h2>
        <p class="text-gray-600 mt-3">Harga katalog dan fitur bersumber digunakan sebagai acuan. Harga yang kosong memakai median sejenis; fitur kosong memakai asumsi rubrik yang ditampilkan per produk. Penjualan dibuat sintetis untuk pengujian. Data ini bukan bukti penjualan, tidak mengubah data asli, dan tidak dapat diterbitkan sebagai rekomendasi pelanggan.</p>
        <p class="text-gray-600 text-sm mt-3">Periode mengikuti cakupan di atas. Setiap jenis perangkat tetap dianalisis terpisah. Android hanya memiliki satu produk sehingga K-Means akan menampilkan kebutuhan penambahan produk, bukan membuat perangkat palsu.</p>
        <form method="POST" action="{{ route('stb-analysis.simulate') }}" class="flex flex-wrap gap-4 items-end mt-5">@csrf
            @foreach(['year','month','type'] as $key)<input name="{{ $key }}" type="hidden" value="{{ $filters[$key] }}">@endforeach
            <label class="text-sm">Skenario<select name="scenario" class="block rounded-lg mt-2">@foreach(config('stb_simulation.scenarios') as $value=>$name)<option value="{{ $value }}">{{ $name }}</option>@endforeach</select></label>
            <label class="text-sm">Jumlah kelompok uji<select name="k" class="block rounded-lg mt-2"><option value="">Otomatis</option>@for($i=2;$i<=6;$i++)<option value="{{ $i }}">{{ $i }} kelompok</option>@endfor</select></label>
            <button class="border border-amber-600 text-amber-900 px-5 py-3 rounded-lg">Jalankan simulasi terpisah</button>
        </form>
    </div>
    @endcan
    <div class="bg-white rounded-2xl border p-6"><h2 class="text-xl font-semibold">Riwayat analisis</h2><div class="divide-y mt-4">@forelse($runs as $run)<a href="{{ route('stb-analysis.show',$run) }}" class="block py-4 text-blue-700">#{{ $run->id }} · {{ $run->is_simulated ? '[SIMULASI]' : '[DATA NYATA]' }} · {{ config('stb_analysis.types.'.$run->device_type) }} · {{ sprintf('%02d/%d',$run->month,$run->year) }} <span class="text-gray-500">— {{ $run->status==='ready' ? 'Hasil tersedia' : 'Dataset belum cukup' }} {{ $run->published_at ? '· Diterbitkan' : '' }}</span></a>@empty<p class="text-gray-500 py-4">Belum ada proses analisis. Tidak ada hasil simulasi yang ditambahkan ke dataset nyata.</p>@endforelse</div></div>
</div>
</x-app-layout>
