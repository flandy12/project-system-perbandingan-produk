<x-frontend-layout>
<div class="max-w-7xl mx-auto px-6 py-10 space-y-6">
<div><p class="text-blue-700 font-semibold text-sm">PILIH SESUAI KEBUTUHAN</p><h1 class="text-3xl font-bold mt-3">Dasar rekomendasi STB</h1><p class="text-gray-600 mt-4">Produk dikelompokkan berdasarkan harga yang tercatat, indeks kemampuan terdokumentasi, dan penjualan pada bulan yang sama. Setiap jenis perangkat dianalisis terpisah. Dalam setiap kelompok, pilihan diurutkan menurut indeks fitur, kemudian unit penjualan, lalu harga observasi terendah; ini merupakan aturan pilihan aplikasi, bukan peringkat yang dihasilkan K-Means.</p></div>
@forelse($runs as $run)
<div class="bg-white border rounded-2xl p-6"><h2 class="text-2xl font-semibold">{{ config('stb_analysis.types.'.$run->device_type) }}</h2><p class="text-gray-600 mt-3">{{ sprintf('%02d/%d',$run->month,$run->year) }} · {{ $run->sales_source }} · cakupan {{ count($run->snapshot['rows']) }}/{{ $run->snapshot['total'] }} produk · diproses {{ $run->created_at->format('d/m/Y') }}. {{ $run->snapshot['price_policy'] }} {{ $run->snapshot['feature_policy'] }}</p>
@include('pages.stb-analysis.results', ['publicView'=>true])
</div>
@empty
<div class="bg-blue-50 rounded-2xl p-8"><h2 class="text-xl font-semibold">Rekomendasi berbasis K-Means belum diterbitkan</h2><p class="text-gray-600 mt-3">Dataset harga, fitur, dan penjualan nyata masih perlu dilengkapi atau hasil perlu ditinjau pengelola. Pilihan katalog tetap dapat digunakan untuk memilih jenis perangkat dan membandingkan spesifikasi.</p><a href="{{ route('gallery.index') }}" class="inline-block bg-blue-700 text-white px-5 py-3 rounded-lg mt-5">Jelajahi katalog</a></div>
@endforelse
</div>
</x-frontend-layout>
