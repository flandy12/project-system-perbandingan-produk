<x-app-layout>
<div class="max-w-7xl mx-auto px-6 py-8 space-y-6">
<a href="{{ route('stb-analysis.index') }}" class="text-blue-700">← Kembali ke analisis</a>
<div class="bg-white rounded-2xl border p-6"><p class="text-blue-700 text-sm font-semibold">ANALISIS #{{ $run->id }}</p><h1 class="text-3xl font-bold mt-2">{{ config('stb_analysis.types.'.$run->device_type) }}</h1><p class="text-gray-600 mt-3">Periode {{ sprintf('%02d/%d',$run->month,$run->year) }} · {{ $run->sales_source }} · {{ count($run->snapshot['rows']) }}/{{ $run->snapshot['total'] }} produk lengkap · diproses {{ $run->created_at->format('d/m/Y H:i') }}</p>
@if(session('status'))<p class="bg-green-50 p-4 rounded-xl mt-4">{{ session('status') }}</p>@endif
@if($errors->any())<div class="bg-red-50 text-red-800 p-4 rounded-xl mt-4">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
@if($run->is_simulated)
    <div class="bg-amber-50 text-amber-900 rounded-xl p-5 mt-5"><h2 class="font-bold">SIMULASI PENGUJIAN — BUKAN DATA PENJUALAN NYATA</h2><p class="mt-2">Hasil ini memakai asumsi yang ditampilkan dalam snapshot. Harga dan fitur asli tetap dipertahankan. Simulasi tidak dapat dipublikasikan kepada pelanggan dan tidak menjadi hasil penelitian empiris.</p></div>
@endif
<a href="{{ route('stb-analysis.export',$run) }}" class="inline-block border rounded-lg px-5 py-3 mt-5 text-blue-700">Unduh dataset dan hasil JSON</a>
@if($run->status==='ready')
    @if(!$current && !$run->is_simulated)<p class="text-amber-900 bg-amber-50 p-4 rounded-xl mt-4">Dataset sumber berubah. Snapshot tetap tersimpan untuk audit, tetapi rekomendasi ini tidak ditampilkan kepada pelanggan. Jalankan analisis baru.</p>@endif
    @include('pages.stb-analysis.results')
    @if(!$run->is_simulated)
    @can('publish.stb.analysis')
    <div class="mt-6">
    @if($run->published_at)
        <form method="POST" action="{{ route('stb-analysis.unpublish',$run) }}">@csrf @method('DELETE')<button class="border rounded-lg px-5 py-3">Hentikan publikasi</button></form>
    @else
        <p class="text-gray-600 mb-3">Tinjau sumber, kesesuaian model, cakupan dataset, dan evaluasi sebelum diterbitkan. Rekomendasi menampilkan hingga dua pilihan per kelompok; fitur tertinggi menjadi prioritas, disusul penjualan dan harga.</p>
        <form method="POST" action="{{ route('stb-analysis.publish',$run) }}">@csrf<button class="bg-blue-700 text-white rounded-lg px-5 py-3 disabled:opacity-50" @disabled(!$current || $run->result['silhouette']<=0)>Terbitkan rekomendasi</button></form>
    @endif
    </div>
    @endcan
    @endif
@else
    <div class="bg-amber-50 rounded-xl p-5 mt-5"><h2 class="font-semibold">Analisis belum dapat dijalankan</h2><p class="mt-2">{{ $run->snapshot['blocking_reason'] }}</p><p class="text-gray-600 mt-2">{{ $run->is_simulated ? 'Jumlah produk atau variasi data uji belum memenuhi syarat mesin. Produk palsu tidak ditambahkan untuk memaksa hasil.' : 'Lengkapi bukti sumber. Data kosong tidak diisi dengan nol atau simulasi.' }}</p></div>
@endif
</div>
<div class="bg-white border rounded-2xl p-6"><h2 class="text-xl font-semibold">Snapshot dataset dan sumber</h2><p class="text-gray-600 mt-3">{{ $run->snapshot['price_policy'] }} {{ $run->snapshot['feature_policy'] }}</p>@include('pages.stb-analysis.dataset',['snapshot'=>$run->snapshot])<p class="text-xs text-gray-500 break-all mt-5">Versi {{ $run->snapshot['version'] }} · identitas dataset {{ $run->snapshot['fingerprint'] }}</p></div>
</div>
</x-app-layout>
