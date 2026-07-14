<x-app-layout>
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white p-6 rounded-xl shadow">

            <!-- HEADER -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">
                    📊 CMS Top Penjualan
                </h2>
            </div>

            <!-- FILTER -->
            <form method="GET" class="grid md:grid-cols-3 gap-4 mb-6">

                <div>
                    <label class="text-sm font-medium">Bulan</label>
                    <select name="month" class="w-full border rounded-lg px-3 py-2">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}"
                                {{ $month == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Tahun</label>
                    <input type="number"
                           name="year"
                           value="{{ e($year) }}"
                           class="w-full border rounded-lg px-3 py-2">
                </div>

                <div class="flex items-end">
                    <button class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">
                        Filter
                    </button>
                </div>

            </form>

            <!-- TABLE -->
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">

                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="border px-4 py-3 text-center">Rank</th>
                            <th class="border px-4 py-3">Produk</th>
                            <th class="border px-4 py-3 text-center">Harga</th>
                            <th class="border px-4 py-3 text-center">Total Terjual</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($sales as $item)
                            <tr class="hover:bg-gray-50">

                                <td class="border px-4 py-3 text-center font-bold">
                                    #{{ $loop->iteration + ($sales->currentPage()-1) * $sales->perPage() }}
                                </td>

                                <td class="border px-4 py-3 flex items-center gap-3">

                                    <div>
                                        <div class="font-semibold">
                                            {{ e($item->title) }}
                                        </div>
                                    </div>

                                </td>

                                <td class="border px-4 py-3 text-center text-indigo-600 font-semibold">
                                    Rp {{ number_format($item->price,0,',','.') }}
                                </td>

                                <td class="border px-4 py-3 text-center">
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">
                                        {{ number_format($item->total_sold) }} Terjual
                                    </span>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-gray-500">
                                    Tidak ada data penjualan pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <!-- PAGINATION -->
            <div class="mt-6">
                {{ $sales->links() }}
            </div>

        </div>
    </div>
</div>
</x-app-layout>