@php($result=$run->result)
@if(!($publicView ?? false))
<div class="grid md:grid-cols-3 gap-4 mt-5"><div class="rounded-xl bg-blue-50 p-5"><p class="text-sm text-gray-600">Kelompok</p><p class="text-3xl font-bold text-blue-800 mt-2">{{ $result['k'] }}</p></div><div class="rounded-xl bg-gray-50 p-5"><p class="text-sm text-gray-600">Silhouette (−1 hingga 1)</p><p class="text-3xl font-bold mt-2">{{ number_format($result['silhouette'],3,',','.') }}</p></div><div class="rounded-xl bg-gray-50 p-5"><p class="text-sm text-gray-600">SSE · jarak kuadrat total</p><p class="text-3xl font-bold mt-2">{{ number_format($result['sse'],4,',','.') }}</p></div></div>
<p class="text-gray-600 mt-4">{{ $result['selection'] }} {{ $result['iterations'] }} iterasi pada proses terpilih. Kelompok menunjukkan kemiripan; tidak ada kelompok yang otomatis terbaik.</p>
@else
<p class="text-gray-600 mt-4">{{ $result['k'] }} kelompok berdasarkan kemiripan harga, fitur, dan penjualan. Profil harga, fitur, dan penjualan setiap kelompok ditampilkan di bawah; tidak ada kelompok yang otomatis terbaik.</p>
@endif
@foreach($result['warnings'] as $warning)<p class="text-amber-900 bg-amber-50 p-3 rounded-lg mt-3">{{ $warning }}</p>@endforeach
@if(!($publicView ?? false))
    @include('pages.stb-analysis.charts', ['charts'=>$charts ?? null])
@endif
<div class="grid md:grid-cols-2 gap-5 mt-6">
@foreach($result['centroids_raw'] as $cluster=>$center)
<div class="rounded-xl border p-5"><h2 class="text-xl font-semibold">Kelompok {{ $cluster+1 }}</h2><p class="text-gray-500 text-sm mt-2">Profil rata-rata anggota</p><p class="text-gray-800 mt-2">Harga Rp {{ number_format($center[0],0,',','.') }} · fitur {{ number_format($center[1],1,',','.') }}/100 · penjualan {{ number_format($center[2],1,',','.') }} unit</p>
<div class="divide-y mt-4">@foreach($run->snapshot['rows'] as $i=>$row)
@if($result['assignments'][$i]===$cluster)
<div class="py-3"><a class="font-medium text-blue-700" href="{{ route('product.show',$row['product_id']) }}">{{ $row['title'] }}</a><p class="text-sm text-gray-600 mt-1">Harga tercatat Rp {{ number_format($row['values'][0],0,',','.') }} · fitur {{ number_format($row['values'][1],1,',','.') }}/100 · {{ number_format($row['values'][2],0,',','.') }} unit. Profil paling dekat dengan rata-rata kelompok ini. @if(!($publicView ?? false)) Jarak normalisasi {{ number_format($result['distances'][$i],4,',','.') }}. @endif</p></div>
@endif
@endforeach</div></div>
@endforeach
</div>
@if(!($publicView ?? false))
<details class="mt-6 rounded-xl border p-5"><summary class="font-semibold cursor-pointer">Normalisasi dan evaluasi jumlah kelompok</summary><p class="text-gray-600 mt-4">Setiap variabel: (nilai − minimum) ÷ (maksimum − minimum), bobot sama. Variabel konstan bernilai 0. Rentang dihitung dari produk lengkap dalam snapshot ini, sehingga kelompok dapat berubah jika dataset berubah. Rubrik fitur tetap; angka normalisasi bukan skor produk / 100.</p>
<div class="overflow-x-auto mt-4"><table class="w-full text-sm text-left"><thead><tr><th class="p-2">Variabel</th><th class="p-2">Minimum</th><th class="p-2">Maksimum</th></tr></thead><tbody>@foreach($result['ranges'] as $j=>$range)<tr><td class="p-2">{{ $run->snapshot['columns'][$j] }}</td><td class="p-2">{{ $range['min'] }}</td><td class="p-2">{{ $range['max'] }}</td></tr>@endforeach</tbody></table></div>
<div class="overflow-x-auto mt-4"><table class="w-full text-sm text-left"><thead><tr><th class="p-2">Produk</th><th class="p-2">Harga 0–1</th><th class="p-2">Fitur 0–1</th><th class="p-2">Penjualan 0–1</th></tr></thead><tbody>@foreach($result['normalized'] as $i=>$row)<tr><td class="p-2">{{ $run->snapshot['rows'][$i]['code'] }}</td>@foreach($row as $value)<td class="p-2">{{ number_format($value,4,',','.') }}</td>@endforeach</tr>@endforeach</tbody></table></div>
<table class="w-full text-sm text-left mt-4"><thead><tr><th class="p-2">K diuji</th><th class="p-2">SSE</th><th class="p-2">Silhouette</th></tr></thead><tbody>@foreach($result['evaluation'] as $candidate)<tr><td class="p-2">{{ $candidate['k'] }}</td><td class="p-2">{{ number_format($candidate['sse'],4,',','.') }}</td><td class="p-2">{{ number_format($candidate['silhouette'],4,',','.') }}</td></tr>@endforeach</tbody></table>
<p class="text-sm text-gray-600 mt-4">SSE biasanya menurun saat K bertambah; tabel ini membantu pemeriksaan Elbow tanpa mengklaim titik siku otomatis. Silhouette menilai pemisahan kelompok, bukan kepuasan pelanggan atau akurasi rekomendasi. Implementasi PHP memakai inisialisasi titik terjauh, hingga 10 titik awal, maksimal 100 iterasi; proses dengan SSE terendah dipilih. <a href="https://scikit-learn.org/stable/modules/clustering.html" class="underline text-blue-700" target="_blank" rel="noopener noreferrer">Referensi konsep K-Means dan Silhouette</a>.</p></details>

@endif
