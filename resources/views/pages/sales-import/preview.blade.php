<x-app-layout>
<div class="max-w-7xl mx-auto px-6 py-8 space-y-6">
    <a href="{{ route('sales-import.index') }}" class="text-blue-700">Kembali ke pengelolaan data</a>
    <div><h1 class="text-2xl font-bold">Pratinjau impor penjualan</h1><p class="text-gray-600 mt-2">{{ $batch->filename }} · {{ count($batch->rows) }} baris</p></div>
    @if(session('success'))<div class="p-4 rounded-xl bg-green-50 text-green-800">{{ session('success') }}</div>
@endif
    @if($errors->any())<div class="p-4 rounded-xl bg-red-50 text-red-800">{{ $errors->first() }}</div>
@endif
    @php($invalid=collect($batch->rows)->filter(fn($row)=>count($row['errors'])>0)->count())
    <div class="p-5 rounded-xl bg-white border"><p>{{ $invalid }} baris bermasalah. {{ collect($batch->rows)->where('conflict',true)->count() }} baris sudah ada.</p><p class="text-gray-600 mt-2">Semua kesalahan harus diperbaiki sebelum penyimpanan. Catatan yang sudah ada akan dilewati, sehingga tidak tertimpa atau terhitung dua kali.</p></div>
    <div class="bg-white border rounded-xl overflow-x-auto"><table class="w-full text-sm text-left"><thead class="bg-gray-50"><tr><th class="p-3">Baris</th><th class="p-3">Produk</th><th class="p-3">Periode</th><th class="p-3">Unit</th><th class="p-3">Sumber</th><th class="p-3">Pemeriksaan</th></tr></thead><tbody class="divide-y">@foreach($batch->rows as $row)<tr><td class="p-3">{{ $row['line'] }}</td><td class="p-3">{{ $row['title'] ?? $row['data']['kode_produk'] }}</td><td class="p-3">{{ $row['data']['bulan'] }}/{{ $row['data']['tahun'] }}</td><td class="p-3">{{ $row['data']['unit_terjual'] ?? 'Kosong' }}</td><td class="p-3">{{ $row['data']['sumber'] }}<p class="text-gray-500">{{ $row['data']['referensi_sumber'] }}</p><p class="mt-1">{{ $row['data']['status_verifikasi'] }}{{ $row['data']['is_simulasi'] == 1 ? ' · Simulasi' : '' }}</p></td><td class="p-3">@foreach($row['errors'] as $error)<p class="text-red-700">{{ $error }}</p>
@endforeach @if(!$row['errors']){{ $row['conflict'] ? 'Duplikat — dilewati' : 'Valid — akan disimpan' }}
@endif</td></tr>
@endforeach</tbody></table></div>
    @if($batch->status === 'preview' && !$invalid) @can('create.product.sales')<form method="POST" action="{{ route('sales-import.apply',$batch) }}">@csrf<button class="px-6 py-3 bg-blue-600 text-white rounded-lg">Simpan data valid</button></form>
@endcan @elseif($batch->status==='applied')<p class="text-green-800">Batch selesai: {{ $batch->summary['inserted'] }} disimpan, {{ $batch->summary['skipped'] }} dilewati.</p>
@else<p class="text-red-700">Perbaiki berkas Excel, lalu unggah kembali.</p>
@endif
</div>
</x-app-layout>
