<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between gap-4"><h2 class="font-semibold text-xl text-slate-800">Dashboard</h2><a href="{{ url('/gallery') }}" class="text-sm text-sky-700 hover:underline">Lihat katalog ↗</a></div></x-slot>
    <div class="max-w-7xl mx-auto space-y-6">
        <section class="rounded-2xl bg-slate-900 text-white p-6 md:p-8">
            <p class="text-sky-300 text-xs font-semibold uppercase tracking-widest">Matrix · Pusat pengelolaan</p>
            <h1 class="text-2xl md:text-3xl font-semibold mt-3">Selamat datang, {{ auth()->user()->name }}.</h1>
            <p class="text-slate-300 mt-3 max-w-2xl">Pantau aktivitas pengunjung, kelola katalog, dan siapkan data rekomendasi dalam satu tempat.</p>
            <div class="flex flex-wrap gap-3 mt-6">
                @can('view.product')<a href="{{ route('products.index') }}" class="rounded-lg bg-sky-500 hover:bg-sky-600 px-4 py-3 font-semibold text-sm">Kelola produk →</a>@endcan
                @can('view.product.sales')<a href="{{ route('sales-import.index') }}" class="rounded-lg border border-slate-600 hover:bg-slate-800 px-4 py-3 text-sm">Kesiapan data & impor</a>@endcan
                @can('view.stb.analysis')<a href="{{ route('stb-analysis.index') }}" class="rounded-lg border border-slate-600 hover:bg-slate-800 px-4 py-3 text-sm">Analisis K-Means</a>@endcan
            </div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
                <div><h2 class="text-lg font-semibold text-slate-900">Aktivitas pengunjung</h2><p class="text-sm text-slate-500 mt-1">{{ $start->format('d/m/Y') }} – {{ $end->format('d/m/Y') }} · hari tanpa kunjungan ditampilkan sebagai 0</p></div>
                <form method="GET" class="flex items-center gap-2"><label for="days" class="sr-only">Periode aktivitas</label><select id="days" name="days" class="rounded-lg border-slate-300 text-sm">@foreach([7,30,90] as $period)<option value="{{ $period }}" @selected($days===$period)>{{ $period }} hari terakhir</option>@endforeach</select><button class="rounded-lg bg-slate-900 text-white px-4 py-2 text-sm">Terapkan</button></form>
            </div>
            <div class="grid sm:grid-cols-3 gap-4">
                @foreach([['Kunjungan website',$totalVisits,'Jumlah akses yang tercatat'],['Pengunjung unik',$uniqueVisitors,'Berdasarkan alamat IP'],['Klik produk',$totalClicks,'Ketertarikan, bukan transaksi']] as [$label,$value,$note])
                <div class="min-w-0 rounded-xl bg-slate-50 border border-slate-100 p-5"><p class="text-sm text-slate-500">{{ $label }}</p><p class="text-3xl font-semibold text-slate-900 mt-2 break-words tabular-nums">{{ number_format($value,0,',','.') }}</p><p class="text-xs text-slate-500 mt-2">{{ $note }}</p></div>
                @endforeach
            </div>
            <div class="mt-6 h-64"><canvas id="visitChart" role="img" aria-label="Grafik kunjungan harian">Grafik kunjungan harian</canvas></div>
            <details class="mt-3 text-sm text-slate-600"><summary class="cursor-pointer">Lihat angka kunjungan harian</summary><div class="max-h-48 overflow-auto mt-3"><table class="w-full text-left"><thead><tr><th class="p-2">Tanggal</th><th class="p-2">Kunjungan</th></tr></thead><tbody>@foreach($dailyVisits as $day)<tr class="border-t"><td class="p-2">{{ $day['date'] }}</td><td class="p-2">{{ $day['total'] }}</td></tr>@endforeach</tbody></table></div></details>
        </section>
        <div class="grid xl:grid-cols-2 gap-6">
            <section class="rounded-2xl border bg-white p-6"><h2 class="text-lg font-semibold">Ringkasan katalog</h2><div class="grid grid-cols-2 gap-4 mt-5">@foreach([['Total produk',$catalogue['total']],['Produk aktif',$catalogue['active']],['Harga belum diisi',$catalogue['missing_price']],['Tautan resmi belum diisi',$catalogue['missing_link']]] as [$label,$value])<div class="bg-slate-50 rounded-xl p-4"><p class="text-sm text-slate-500">{{ $label }}</p><p class="text-2xl font-semibold mt-2 tabular-nums">{{ $value }}</p></div>@endforeach</div>@can('view.product')<a href="{{ route('products.index') }}" class="inline-block text-sm text-sky-700 mt-5">Lengkapi katalog →</a>@endcan</section>
            <section class="rounded-2xl border bg-white p-6"><h2 class="text-lg font-semibold">Produk paling sering dilihat</h2><p class="text-sm text-slate-500 mt-1">Berdasarkan klik pada periode terpilih.</p><div class="mt-4 divide-y">@forelse($topProducts as $product)<a href="{{ url('/item/'.$product->id) }}" class="flex items-center justify-between gap-4 py-3 hover:text-sky-700"><span class="text-sm">{{ $product->title }}</span><span class="shrink-0 bg-sky-50 text-sky-700 px-3 py-1 rounded-lg text-sm tabular-nums">{{ number_format($product->total,0,',','.') }} klik</span></a>@empty<p class="py-6 text-slate-500 text-sm">Belum ada klik produk pada periode ini.</p>@endforelse</div></section>
        </div>
        @can('view.stb.analysis')<section class="rounded-2xl border bg-white p-6"><div class="flex justify-between gap-3"><h2 class="text-lg font-semibold">Analisis terakhir</h2><a href="{{ route('stb-analysis.index') }}" class="text-sm text-sky-700">Semua riwayat →</a></div><div class="divide-y mt-3">@forelse($runs as $run)<a href="{{ route('stb-analysis.show',$run) }}" class="flex flex-wrap justify-between gap-2 py-4 text-sm"><span>#{{ $run->id }} · {{ config('stb_analysis.types.'.$run->device_type) }} · {{ sprintf('%02d/%d',$run->month,$run->year) }}</span><span class="{{ $run->is_simulated ? 'text-amber-700' : 'text-sky-700' }}">{{ $run->is_simulated ? 'Simulasi' : 'Data nyata' }} · {{ $run->status==='ready' ? 'Hasil tersedia' : 'Dataset belum cukup' }}</span></a>@empty<p class="text-slate-500 py-4">Belum ada riwayat analisis.</p>@endforelse</div></section>@endcan
    </div>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    <script>
        if (typeof Chart !== 'undefined') new Chart(document.getElementById('visitChart'), {
            type: 'line', data: {labels: @json($dailyVisits->pluck('date')), datasets: [{label: 'Kunjungan',data: @json($dailyVisits->pluck('total')),borderColor:'#0284c7',backgroundColor:'rgba(2,132,199,0.08)',fill:true,tension:0,pointRadius:2}]},
            options: {responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}},x:{grid:{display:false},ticks:{maxTicksLimit:8}}}}
        });
    </script>
    @endpush
</x-app-layout>
