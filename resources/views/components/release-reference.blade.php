@php($release = $product->reference_metadata['release_reference'] ?? [])
<details class="text-sm text-gray-500 mt-3">
    <summary class="cursor-pointer">Tahun rilis model: {{ $product->release_year ?? 'Belum terverifikasi' }}{{ $product->release_year ? ' · berdasarkan laporan media' : '' }}</summary>
    <div class="mt-2 space-y-2">
        @if(!empty($release['date']))
            <p>Tanggal peluncuran yang dilaporkan: {{ \Carbon\Carbon::parse($release['date'])->translatedFormat('j F Y') }}.</p>
        @endif
        @if(!empty($release['documented_year']))
            <p>Bukti publikasi terkait: {{ $release['documented_year'] }}. Tahun ini tidak dipakai sebagai tahun rilis.</p>
        @endif
        <p>{{ $release['note'] ?? 'Tahun rilis belum terverifikasi.' }}</p>
        @foreach($release['sources'] ?? [] as $source)
            @if(str_starts_with($source['url'] ?? '', 'https://'))
                <a class="underline block" href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer">Lihat sumber {{ $loop->iteration }}</a>
            @endif
        @endforeach
    </div>
</details>
