@if($charts)
<section class="mt-6 rounded-2xl border border-gray-200 p-5 md:p-6 bg-gray-50" aria-label="Grafik analisis" x-data="{ cluster: 'all' }">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div><p class="text-sm font-semibold text-blue-700">VISUALISASI DATA</p><h2 class="text-2xl font-bold text-gray-900 mt-2">Profil produk dan evaluasi kelompok</h2><p class="text-sm text-gray-600 mt-2">Snapshot #{{ $run->id }} · {{ sprintf('%02d/%d',$run->month,$run->year) }} · {{ $run->is_simulated ? 'SIMULASI PENGUJIAN' : 'DATA ANALISIS' }}. Arahkan kursor atau fokuskan titik untuk membaca nilai produk.</p></div>
        <label class="text-sm font-medium">Sorot kelompok<select x-model="cluster" class="block rounded-lg mt-2"><option value="all">Semua kelompok</option>@foreach($charts['legend'] as $legend)<option value="{{ $legend['cluster'] }}">Kelompok {{ $legend['cluster']+1 }}</option>@endforeach</select></label>
    </div>
    <div class="flex flex-wrap gap-4 mt-5 text-sm" aria-label="Legenda kelompok">@foreach($charts['legend'] as $legend)<span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background: {{ $legend['color'] }}"></span>Kelompok {{ $legend['cluster']+1 }} · {{ $legend['count'] }} produk</span>@endforeach</div>
    <p class="text-sm text-gray-600 mt-3">Warna konsisten pada kedua grafik. Sebaran harga–penjualan adalah proyeksi dua variabel; K-Means tetap memakai tiga variabel termasuk fitur. Nomor kelompok tidak menunjukkan peringkat kualitas.</p>
    <div class="grid lg:grid-cols-2 gap-5 mt-5">
        <div class="bg-white rounded-xl border p-4"><h3 class="font-semibold">Sebaran harga dan {{ $run->is_simulated ? 'penjualan sintetis' : 'penjualan bulanan' }}</h3>
            <svg viewBox="0 0 600 305" class="w-full mt-4" role="img" aria-label="Sebaran harga versus penjualan per kelompok">
                <title>Sebaran produk snapshot {{ $run->id }}. Harga pada sumbu mendatar dan unit penjualan pada sumbu tegak.</title>
                @foreach($charts['y_ticks'] as $tick)<line x1="80" x2="555" y1="{{ $tick['position'] }}" y2="{{ $tick['position'] }}" stroke="#e2e8f0" stroke-dasharray="4 4"/><text x="70" y="{{ $tick['position']+4 }}" text-anchor="end" fill="#64748b" font-size="12">{{ number_format($tick['value'],1,',','.') }}</text>@endforeach
                @foreach($charts['x_ticks'] as $tick)<line x1="{{ $tick['position'] }}" x2="{{ $tick['position'] }}" y1="35" y2="245" stroke="#e2e8f0" stroke-dasharray="4 4"/><text x="{{ $tick['position'] }}" y="265" text-anchor="middle" fill="#64748b" font-size="12">{{ number_format($tick['value']/1000,1,',','.') }}</text>@endforeach
                <path d="M80 35 V245 H555" stroke="#94a3b8" fill="none"/>
                <text x="318" y="294" text-anchor="middle" fill="#475569" font-size="13">Harga (Rp ribu)</text><text x="20" y="140" transform="rotate(-90 20 140)" text-anchor="middle" fill="#475569" font-size="13">Penjualan (unit)</text>
                @foreach($charts['points'] as $point)<g :opacity="cluster === 'all' || cluster === '{{ $point['cluster'] }}' ? 1 : 0.12">
                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="6" fill="{{ $point['color'] }}" stroke="white" stroke-width="2" tabindex="0" role="img" aria-label="{{ $point['code'] }}: {{ $point['title'] }}, kelompok {{ $point['cluster']+1 }}, Rp {{ number_format($point['values'][0],0,',','.') }}, fitur {{ $point['values'][1] }}, {{ $point['values'][2] }} unit"><title>{{ $point['code'] }} — {{ $point['title'] }} | Kelompok {{ $point['cluster']+1 }} | Rp {{ number_format($point['values'][0],0,',','.') }} | Fitur {{ $point['values'][1] }}/100 | {{ $point['values'][2] }} unit {{ $run->is_simulated ? '(simulasi)' : '' }}</title></circle>
                    @if(count($charts['points'])<=20)<text x="{{ $point['x'] }}" y="{{ $point['y']-11 }}" text-anchor="middle" font-size="10" fill="#334155">{{ $point['code'] }}</text>@endif
                </g>@endforeach
            </svg>
        </div>
        <div class="bg-white rounded-xl border p-4"><h3 class="font-semibold">Indeks fitur {{ $run->is_simulated ? 'uji' : 'produk' }} per kelompok</h3><p class="text-xs text-gray-500 mt-1">Kode produk mengikuti tabel snapshot. Nilai 100 berarti mencapai acuan rubrik.</p>
            <div class="overflow-y-auto mt-3" style="max-height: 340px"><svg viewBox="0 0 600 {{ $charts['bar_height'] }}" class="w-full" role="img" aria-label="Batang indeks fitur produk dengan warna kelompok">
                <title>Indeks fitur setiap produk pada snapshot {{ $run->id }}, skala nol hingga seratus.</title>
                @foreach(range(0,100,25) as $value)<line x1="{{ 130+$value*4.25 }}" x2="{{ 130+$value*4.25 }}" y1="20" y2="{{ $charts['bar_height']-45 }}" stroke="#e2e8f0" stroke-dasharray="4 4"/><text x="{{ 130+$value*4.25 }}" y="{{ $charts['bar_height']-25 }}" text-anchor="middle" fill="#64748b" font-size="12">{{ $value }}</text>@endforeach
                @foreach($charts['points'] as $point)<g :opacity="cluster === 'all' || cluster === '{{ $point['cluster'] }}' ? 1 : 0.12"><text x="120" y="{{ $point['bar_y']+15 }}" text-anchor="end" font-size="12" fill="#475569">{{ $point['code'] }}</text><rect x="130" y="{{ $point['bar_y'] }}" width="{{ $point['bar_width'] }}" height="22" rx="4" fill="{{ $point['color'] }}" tabindex="0" role="img" aria-label="{{ $point['title'] }}: fitur {{ $point['values'][1] }} dari 100, kelompok {{ $point['cluster']+1 }}"><title>{{ $point['code'] }} — {{ $point['title'] }} | Fitur {{ $point['values'][1] }}/100 | Kelompok {{ $point['cluster']+1 }}</title></rect><text x="{{ min(570,135+$point['bar_width']) }}" y="{{ $point['bar_y']+15 }}" font-size="11" fill="#334155">{{ number_format($point['values'][1],1,',','.') }}</text></g>@endforeach
                <text x="340" y="{{ $charts['bar_height']-5 }}" text-anchor="middle" fill="#475569" font-size="13">Indeks fitur (0–100)</text>
            </svg></div>
        </div>
    </div>
    <div class="mt-6"><h3 class="text-lg font-semibold">Kurva evaluasi jumlah kelompok</h3><p class="text-sm text-gray-600 mt-2">SSE dan Silhouette memakai sumbu terpisah. Garis tegak menandai K={{ $run->result['k'] }} yang digunakan pada hasil ini; kurva memperlihatkan semua K yang diuji pada snapshot yang sama.</p></div>
    <div class="grid lg:grid-cols-2 gap-5 mt-4">
        @foreach(['sse'=>['Kurva SSE (pemeriksaan Elbow)','#2563eb'], 'silhouette'=>['Kurva Silhouette','#ea580c']] as $metric=>$style)
            @php($curve=$charts[$metric])
            <div class="bg-white rounded-xl border p-4"><h4 class="font-semibold">{{ $style[0] }}</h4><svg viewBox="0 0 600 305" class="w-full mt-4" role="img" aria-label="{{ $style[0] }} untuk seluruh K yang diuji">
                <title>{{ $style[0] }}. Titik K={{ $run->result['k'] }} ditandai sebagai pilihan proses.</title>
                @foreach($curve['ticks'] as $tick)<line x1="80" x2="555" y1="{{ $tick['position'] }}" y2="{{ $tick['position'] }}" stroke="#e2e8f0" stroke-dasharray="4 4"/><text x="70" y="{{ $tick['position']+4 }}" text-anchor="end" fill="#64748b" font-size="12">{{ number_format($tick['value'],3,',','.') }}</text>@endforeach
                <line x1="{{ $curve['selected_x'] }}" x2="{{ $curve['selected_x'] }}" y1="35" y2="245" stroke="#64748b" stroke-dasharray="5 5"/>
                <path d="M80 35 V245 H555" stroke="#94a3b8" fill="none"/><polyline points="{{ $curve['path'] }}" stroke="{{ $style[1] }}" stroke-width="2.5" fill="none" stroke-linejoin="round"/>
                @foreach($curve['points'] as $point)<circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="{{ $point['selected'] ? 7 : 4 }}" fill="{{ $style[1] }}" stroke="{{ $point['selected'] ? '#0f172a' : 'white' }}" stroke-width="2" tabindex="0" role="img" aria-label="K={{ $point['k'] }}, {{ strtoupper($metric) }} {{ number_format($point['value'],4,',','.') }}{{ $point['selected'] ? ', dipilih' : '' }}"><title>K={{ $point['k'] }} · {{ strtoupper($metric) }} {{ number_format($point['value'],4,',','.') }} {{ $point['selected'] ? '· K dipilih' : '' }}</title></circle><text x="{{ $point['x'] }}" y="265" text-anchor="middle" font-size="12" fill="#475569">{{ $point['k'] }}</text>@endforeach
                <text x="318" y="294" text-anchor="middle" fill="#475569" font-size="13">Jumlah kelompok (K)</text><text x="20" y="140" transform="rotate(-90 20 140)" text-anchor="middle" fill="#475569" font-size="13">{{ $metric==='sse' ? 'SSE' : 'Silhouette' }}</text>
            </svg><p class="text-xs text-gray-500">{{ $metric==='sse' ? 'SSE lebih rendah berarti jarak dalam kelompok lebih kecil. Titik siku tidak ditentukan otomatis.' : 'Silhouette lebih tinggi berarti kelompok lebih terpisah. Sumbu menyesuaikan rentang nilai data, dalam batas −1 hingga 1.' }}</p></div>
        @endforeach
    </div>
</section>
@endif
