@php
    $axes = collect($labels)->map(fn($label,$i)=>['label'=>$label,'a'=>$metricsA[$i],'b'=>$metricsB[$i]])->filter(fn($axis)=>$axis['a'] !== null && $axis['b'] !== null)->values();
    $radar = $axes->count() >= 3;
    $point = fn($i,$radius)=>[300 + $radius*cos(-M_PI/2 + 2*M_PI*$i/max(1,$axes->count())),190 + $radius*sin(-M_PI/2 + 2*M_PI*$i/max(1,$axes->count()))];
    $polygon = fn($key)=>$axes->map(function($axis,$i)use($point,$key){$p=$point($i,130*$axis[$key]/100);return implode(',',$p);})->implode(' ');
@endphp
<div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
    <svg viewBox="0 0 600 {{ $radar ? 390 : max(155, $axes->count()*95+40) }}" class="w-full" role="img" aria-label="Grafik skor spesifikasi kedua produk">
        <title>Perbandingan {{ $axes->count() }} kelompok dengan bukti bersama; skala 0–100</title>
        @if($radar)
            @foreach([20,40,60,80,100] as $tick)
                <polygon points="{{ $axes->map(function($axis,$i)use($point,$tick){return implode(',',$point($i,130*$tick/100));})->implode(' ') }}" fill="none" stroke="#e2e8f0"/>
                <text x="306" y="{{ 190-130*$tick/100 }}" fill="#64748b" font-size="11">{{ $tick }}</text>
            @endforeach
            @foreach($axes as $i=>$axis)
                @php($p=$point($i,130)) @php($label=$point($i,164))
                <line x1="300" y1="190" x2="{{ $p[0] }}" y2="{{ $p[1] }}" stroke="#e2e8f0"/>
                <text x="{{ $label[0] }}" y="{{ $label[1] }}" text-anchor="middle" dominant-baseline="middle" font-size="14" fill="#334155">{{ $axis['label'] }}</text>
            @endforeach
            @foreach(['a'=>['#3b82f6','0.15'], 'b'=>['#f04d4d','0.10']] as $key=>$color)
                <polygon points="{{ $polygon($key) }}" fill="{{ $color[0] }}" fill-opacity="{{ $color[1] }}" stroke="{{ $color[0] }}" stroke-width="2.5"/>
                @foreach($axes as $i=>$axis)
                    @php($p=$point($i,130*$axis[$key]/100))
                    <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="4" fill="{{ $color[0] }}" tabindex="0"><title>{{ $key==='a' ? $productA->title : $productB->title }} · {{ $axis['label'] }}: {{ number_format($axis[$key],2,',','.') }} / 100</title></circle>
                @endforeach
            @endforeach
        @else
            @foreach($axes as $i=>$axis)
                <text x="20" y="{{ 28+$i*95 }}" font-size="15" fill="#334155">{{ $axis['label'] }}</text>
                @foreach(['a'=>'#3b82f6','b'=>'#f04d4d'] as $key=>$color)
                    @php($y=40+$i*95+($key==='b'?25:0))
                    <rect x="20" y="{{ $y }}" width="485" height="17" rx="5" fill="#e2e8f0"/>
                    <rect x="20" y="{{ $y }}" width="{{ $axis[$key]*4.85 }}" height="17" rx="5" fill="{{ $color }}"/>
                    <text x="520" y="{{ $y+14 }}" font-size="13" fill="#334155">{{ number_format($axis[$key],1,',','.') }}</text>
                @endforeach
            @endforeach
        @endif
    </svg>
    <div class="flex flex-wrap justify-center gap-x-5 gap-y-2 text-xs px-3 pb-3"><span class="text-blue-600">● {{ $productA->title }}</span><span class="text-red-500">● {{ $productB->title }}</span></div>
</div>
@if(!$radar)<p class="text-xs text-slate-500 mt-2">Grafik batang digunakan karena data bersama kurang dari tiga kelompok. Data kosong tidak dianggap nol.</p>@endif
