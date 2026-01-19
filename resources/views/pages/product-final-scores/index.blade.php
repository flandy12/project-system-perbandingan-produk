<x-app-layout>
    <div x-data="productFinalScoreCrud()" class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <!-- HEADER -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Product Final Scores</h2>

                    <button @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Hitung / Tambah Score
                    </button>
                </div>

                <!-- TABLE -->
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">No</th>
                            <th class="border px-4 py-2">Produk</th>
                            <th class="border px-4 py-2 text-center">Specification</th>
                            <th class="border px-4 py-2 text-center">Click</th>
                            <th class="border px-4 py-2 text-center">Sales</th>
                            <th class="border px-4 py-2 text-center">Final Score</th>
                            <th class="border px-4 py-2 text-center">Calculated At</th>
                            <th class="border px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scores as $item)
                            <tr>
                                <td class="border px-4 py-2 font-medium">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="border px-4 py-2 font-medium">
                                    {{ $item->product->title ?? '-' }}
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    {{ number_format($item->specification_score, 2) }}
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    {{ number_format($item->click_score, 2) }}
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    {{ number_format($item->sales_score, 2) }}
                                </td>

                                <td class="border px-4 py-2 text-center font-semibold">
                                    {{ number_format($item->final_score, 2) }}
                                </td>

                                <td class="border px-4 py-2 text-center text-sm text-gray-600">
                                    {{ $item->calculated_at?->format('d M Y H:i') }}
                                </td>

                                <td class="border px-4 py-2 text-center space-x-2">
                                    <button
                                        @click="openEdit({
                                            id: {{ $item->id }},
                                            product_id: {{ $item->product_id }},
                                            specification_score: @js($item->specification_score),
                                            click_score: @js($item->click_score),
                                            sales_score: @js($item->sales_score)
                                        })"
                                        class="text-green-600">
                                        Edit
                                    </button>

                                    <form action="{{ route('product-final-scores.destroy', $item) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus final score produk ini?')"
                                            class="text-red-600">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-6 text-gray-500">
                                    Belum ada final score produk
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- PAGINATION -->
                <div class="mt-4">
                    {{ $scores->links() }}
                </div>

            </div>
        </div>

        <!-- MODAL -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div @click.away="closeModal()" class="bg-white w-full max-w-md p-6 rounded">

                <h3 class="text-lg font-semibold mb-4" x-text="isEdit ? 'Edit Final Score' : 'Tambah Final Score'">
                </h3>

                <form
                    :action="isEdit
                        ?
                        `/product-final-scores/${form.id}` :
                        `{{ route('product-final-scores.store') }}`"
                    method="POST" class="space-y-4">

                    @csrf

                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- PRODUCT -->
                    <select name="product_id" x-model="form.product_id" class="w-full border rounded px-3 py-2" required
                        :disabled="isEdit">
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->title }}
                            </option>
                        @endforeach
                    </select>

                    <!-- SPEC SCORE -->
                    <input type="number" step="0.01" name="specification_score" x-model="form.specification_score"
                        class="w-full border rounded px-3 py-2" placeholder="Specification Score" required>

                    <!-- CLICK SCORE -->
                    <input type="number" step="0.01" name="click_score" x-model="form.click_score"
                        class="w-full border rounded px-3 py-2" placeholder="Click Score" required>

                    <!-- SALES SCORE -->
                    <input type="number" step="0.01" name="sales_score" x-model="form.sales_score"
                        class="w-full border rounded px-3 py-2" placeholder="Sales Score" required>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeModal()" class="px-4 py-2 border rounded">
                            Batal
                        </button>

                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ALPINE -->
    <script>
        function productFinalScoreCrud() {
            return {
                open: false,
                isEdit: false,
                form: {
                    id: null,
                    product_id: '',
                    specification_score: '',
                    click_score: '',
                    sales_score: ''
                },

                openCreate() {
                    this.isEdit = false
                    this.form = {
                        id: null,
                        product_id: '',
                        specification_score: '',
                        click_score: '',
                        sales_score: ''
                    }
                    this.open = true
                },

                openEdit(data) {
                    this.isEdit = true
                    this.form = data
                    this.open = true
                },

                closeModal() {
                    this.open = false
                }
            }
        }
    </script>
</x-app-layout>
