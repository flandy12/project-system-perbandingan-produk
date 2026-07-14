<x-app-layout>
    <div x-data="salesCrud({{ $errors->any() ? 'true' : 'false' }})" class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded shadow p-6">

                {{-- HEADER --}}
                <div class="flex justify-between items-center mb-4">

                    <h2 class="text-xl font-semibold">
                        Product Sales Statistics
                    </h2>

                    @can('create.product.sales')
                        <button
                            @click="openCreate()"
                            class="px-4 py-2 bg-blue-600 text-white rounded">

                            Tambah Data

                        </button>
                    @endcan

                </div>

                {{-- TABLE --}}
                <table class="min-w-full border">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="border px-4 py-2">
                                Produk
                            </th>

                            <th class="border px-4 py-2">
                                Bulan
                            </th>

                            <th class="border px-4 py-2">
                                Tahun
                            </th>

                            <th class="border px-4 py-2">
                                Total Terjual
                            </th>

                            @canany(['edit.product.sales','delete.product.sales'])
                                <th class="border px-4 py-2">
                                    Aksi
                                </th>
                            @endcanany

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($stats as $stat)

                            <tr>

                                <td class="border px-4 py-2 text-center">
                                    {{ $stat->product->title }}
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    {{ DateTime::createFromFormat('!m',$stat->month)->format('F') }}
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    {{ $stat->year }}
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    {{ number_format($stat->total_sold) }}
                                </td>

                                @canany(['edit.product.sales','delete.product.sales'])

                                <td class="border px-4 py-2 text-center space-x-2">

                                    @can('edit.product.sales')
                                        <button
                                            class="text-green-600"
                                            @click='openEdit(@json($stat))'>

                                            Edit

                                        </button>
                                    @endcan

                                    @can('delete.product.sales')

                                        <form
                                            action="{{ route('product-sales-stats.destroy',$stat) }}"
                                            method="POST"
                                            class="inline">

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                onclick="return confirm('Hapus data?')"
                                                class="text-red-600">

                                                Delete

                                            </button>

                                        </form>

                                    @endcan

                                </td>

                                @endcanany

                            </tr>

                        @endforeach

                    </tbody>

                </table>

                <div class="mt-4">

                    {{ $stats->links() }}

                </div>

            </div>

        </div>

        {{-- MODAL --}}
        @canany(['create.product.sales','edit.product.sales'])

        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center">

            <div
                @click.away="closeModal()"
                class="bg-white rounded p-6 w-full max-w-lg">

                <h3
                    class="text-lg font-semibold mb-4"
                    x-text="isEdit ? 'Edit Data' : 'Tambah Data'">
                </h3>

                <form
                    :action="isEdit ? `/product-sales-stats/${form.id}` : `{{ route('product-sales-stats.store') }}`"
                    method="POST">

                    @csrf

                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="space-y-4">

                        <div>

                            <label class="block mb-1">
                                Produk
                            </label>

                            <select
                                name="product_id"
                                x-model="form.product_id"
                                class="w-full border rounded">

                                <option value="">
                                    Pilih Produk
                                </option>

                                @foreach($products as $product)

                                    <option value="{{ $product->id }}">
                                        {{ $product->title }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="grid grid-cols-2 gap-4">

                            <div>

                                <label class="block mb-1">
                                    Bulan
                                </label>

                                <select
                                    name="month"
                                    x-model="form.month"
                                    class="w-full border rounded">

                                    @for($i=1;$i<=12;$i++)

                                        <option value="{{ $i }}">
                                            {{ DateTime::createFromFormat('!m',$i)->format('F') }}
                                        </option>

                                    @endfor

                                </select>

                            </div>

                            <div>

                                <label class="block mb-1">
                                    Tahun
                                </label>

                                <input
                                    type="number"
                                    name="year"
                                    x-model="form.year"
                                    class="w-full border rounded">

                            </div>

                        </div>

                        <div>

                            <label class="block mb-1">
                                Total Terjual
                            </label>

                            <input
                                type="number"
                                name="total_sold"
                                min="0"
                                x-model="form.total_sold"
                                class="w-full border rounded">

                        </div>

                    </div>

                    <div class="flex justify-end gap-3 mt-6">

                        <button
                            type="button"
                            @click="closeModal()"
                            class="px-4 py-2 border rounded">

                            Batal

                        </button>

                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded">

                            Simpan

                        </button>

                    </div>

                </form>

            </div>

        </div>

        @endcanany

    </div>

    <script>
        function salesCrud(hasError = false){

            return{

                open: hasError,

                isEdit: false,

                form:{
                    id:null,
                    product_id:'',
                    month:new Date().getMonth()+1,
                    year:new Date().getFullYear(),
                    total_sold:0
                },

                openCreate(){

                    this.isEdit=false;

                    this.form={
                        id:null,
                        product_id:'',
                        month:new Date().getMonth()+1,
                        year:new Date().getFullYear(),
                        total_sold:0
                    }

                    this.open=true;

                },

                openEdit(stat){

                    this.isEdit=true;

                    this.form={
                        id:stat.id,
                        product_id:stat.product_id,
                        month:stat.month,
                        year:stat.year,
                        total_sold:stat.total_sold
                    }

                    this.open=true;

                },

                closeModal(){

                    this.open=false;

                }

            }

        }
    </script>

</x-app-layout>