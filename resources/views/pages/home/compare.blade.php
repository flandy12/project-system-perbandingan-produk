<x-frontend-layout>

<div class="max-w-7xl mx-auto px-4 py-10 bg-slate-50">

    {{-- HEADER --}}
    <div class="grid grid-cols-3 items-center mb-8 text-center border p-20 bg-white">

        {{-- A --}}
        <div>
            <div class="w-28 h-28 mx-auto mb-2 bg-gray-100 rounded-xl overflow-hidden">
                @if($productA->image)
                    <img src="{{ asset('storage/' . $productA->image) }}" class="w-full h-full object-cover">
                @endif
            </div>
            <p class="font-semibold text-gray-700">{{ $productA->title }}</p>
        </div>

        {{-- VS --}}
        <div>
            <div class="inline-flex items-center justify-center w-12 h-12 bg-[#F04D4D] text-white rounded-full font-bold shadow">
                VS
            </div>
        </div>

        {{-- B --}}
        <div>
            <div class="w-28 h-28 mx-auto mb-2 bg-gray-100 rounded-xl overflow-hidden">
                @if($productB->image)
                    <img src="{{ asset('storage/' . $productB->image) }}" class="w-full h-full object-cover">
                @endif
            </div>
            <p class="font-semibold text-black">{{ $productB->title }}</p>
        </div>

    </div>

    {{-- MAIN --}}
    <div class="grid md:grid-cols-2 gap-10 items-start">

        {{-- LEFT --}}
        <div class="bg-white rounded-2xl shadow p-6">

            <canvas id="radarChart"></canvas>

            {{-- SCORE --}}
            <div class="flex justify-center gap-10 mt-6 text-center">
                <div>
                    <p class="text-3xl font-bold text-blue-500">{{ round($percentA) }}</p>
                    <p class="text-sm text-gray-500">Poin</p>
                </div>

                <div>
                    <p class="text-3xl font-bold text-red-500">{{ round($percentB) }}</p>
                    <p class="text-sm text-gray-500">Poin</p>
                </div>
            </div>

            {{-- PROGRESS --}}
            <div class="mt-4">
                <div class="w-full bg-gray-200 h-2 rounded-full">
                    <div class="h-2 bg-[#16A34A] rounded-full" style="width: {{ $percentA }}%"></div>
                </div>
            </div>

        </div>

        {{-- RIGHT --}}
        <div>

            {{-- TITLE --}}
            <h3 class="font-bold text-lg mb-4">
                Mengapa <span class="text-[#F04D4D]">{{ $productB->title }}</span> lebih baik?
            </h3>

            {{-- REASONS --}}
            <div class="bg-white rounded-xl shadow p-5 mb-6">
                <ul class="space-y-3 text-sm">
                    @foreach ($reasons as $reason)
                        <li class="flex items-start gap-2">
                            <span class="text-[#13baf7] font-bold">✔</span>
                            <span>{{ $reason }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- QUICK COMPARISON --}}
            <div class="bg-white rounded-xl shadow p-5">

                <h4 class="font-semibold mb-3 text-gray-700">Perbandingan Cepat</h4>

                <div class="space-y-3 text-sm">

                    {{-- PRICE --}}
                    <div class="flex justify-between">
                        <span>{{ $productA->price }}</span>
                        <span class="text-gray-400">Harga</span>
                        <span>{{ $productB->price }}</span>
                    </div>

                    {{-- SOLD --}}
                    <div class="flex justify-between">
                        <span>{{ $productA->sold }}</span>
                        <span class="text-gray-400">Terjual</span>
                        <span>{{ $productB->sold }}</span>
                    </div>

                    {{-- RATING --}}
                    <div class="flex justify-between">
                        <span>{{ $productA->rating }}</span>
                        <span class="text-gray-400">Rating</span>
                        <span>{{ $productB->rating }}</span>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('radarChart');

new Chart(ctx, {
    type: 'radar',
    data: {
        labels: ['Kualitas', 'Daya', 'Fitur', 'Konektivitas'],
        datasets: [
            {
                label: '{{ $productA->title }}',
                data: @json(array_values($metricsA)),
                backgroundColor: 'rgba(59,130,246,0.2)',
                borderColor: '#3b82f6'
            },
            {
                label: '{{ $productB->title }}',
                data: @json(array_values($metricsB)),
                backgroundColor: 'rgba(240,77,77,0.2)',
                borderColor: '#F04D4D'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { r: { beginAtZero: true, max: 100 } }
    }
});
</script>
@endpush

</x-frontend-layout>