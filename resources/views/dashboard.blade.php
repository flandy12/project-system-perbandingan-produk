<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                {{-- <x-welcome /> --}}
                <div class="p-6 space-y-8">

                    <h1 class="text-2xl font-bold">User Analytics</h1>

                    {{-- KPI --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white p-5 rounded-xl shadow">
                            <p class="text-gray-500">Total Visits</p>
                            <p class="text-3xl font-bold">{{ $totalVisits }}</p>
                        </div>

                        <div class="bg-white p-5 rounded-xl shadow">
                            <p class="text-gray-500">Unique Visitors</p>
                            <p class="text-3xl font-bold">{{ $uniqueVisitors }}</p>
                        </div>

                        <div class="bg-white p-5 rounded-xl shadow">
                            <p class="text-gray-500">Product Clicks</p>
                            <p class="text-3xl font-bold">{{ $totalClicks }}</p>
                        </div>
                    </div>

                    {{-- LINE CHART --}}
                    <div class="bg-white p-6 rounded-xl shadow">
                        <h2 class="font-semibold mb-3">Daily Visits (Last 30 Days)</h2>
                        <div>
                            <canvas id="visitChart"></canvas>
                        </div>
                    </div>

                    {{-- BAR CHART --}}
                    <div class="bg-white p-6 rounded-xl shadow">
                        <h2 class="font-semibold mb-3">Top Product Clicks</h2>
                        <div >
                            <canvas id="productChart"></canvas>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const visitLabels = @json($dailyVisits->pluck('date'));
            const visitData = @json($dailyVisits->pluck('total'));

            new Chart(document.getElementById('visitChart'), {
                type: 'line',
                data: {
                    labels: visitLabels,
                    datasets: [{
                        label: 'Visits',
                        data: visitData,
                        tension: 0.3
                    }]
                }
            });

            const productLabels = @json($topProducts->pluck('title'));
            const productData = @json($topProducts->pluck('total'));

            new Chart(document.getElementById('productChart'), {
                type: 'bar',
                data: {
                    labels: productLabels,
                    datasets: [{
                        label: 'Clicks',
                        data: productData
                    }]
                }
            });
        </script>
    @endpush
</x-app-layout>
