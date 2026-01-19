<x-app-layout>
    <div x-data="productSpecScoreCrud()" class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <!-- HEADER -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Product Specification Scores</h2>

                    <button @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Tambah Score
                    </button>
                </div>

                <!-- TABLE -->
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">No</th>
                            <th class="border px-4 py-2">Produk</th>
                            <th class="border px-4 py-2">Specification</th>
                            <th class="border px-4 py-2 text-center">Raw Value</th>
                            <th class="border px-4 py-2 text-center">Normalized</th>
                            <th class="border px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productSpecificationScores as $item)
                            <tr>
                                <td class="border px-4 py-2 font-medium">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="border px-4 py-2 font-medium">
                                    {{ $item->product->title ?? '-' }}
                                </td>

                                <td class="border px-4 py-2">
                                    {{ $item->specification->name ?? '-' }}
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    {{ number_format($item->raw_value, 2) }}
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                        {{ number_format($item->normalized_score, 2) }}
                                    </span>
                                </td>

                                <td class="border px-4 py-2 text-center space-x-2">
                                    <button
                                        @click="openEdit({
                                            id: {{ $item->id }},
                                            product_id: {{ $item->product_id }},
                                            specification_id: {{ $item->specification_id }},
                                            raw_value: @js($item->raw_value),
                                            normalized_score: @js($item->normalized_score)
                                        })"
                                        class="text-green-600">
                                        Edit
                                    </button>

                                    <form action="{{ route('product-specification-scores.destroy', $item) }}"
                                        method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus score specification ini?')"
                                            class="text-red-600">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-gray-500">
                                    Belum ada score specification
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- PAGINATION -->
                <div class="mt-4">
                    {{ $productSpecificationScores->links() }}
                </div>

            </div>
        </div>

        <!-- MODAL -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div @click.away="closeModal()" class="bg-white w-full max-w-md p-6 rounded">

                <h3 class="text-lg font-semibold mb-4" x-text="isEdit ? 'Edit Score' : 'Tambah Score'">
                </h3>

                <form
                    :action="isEdit
                        ?
                        `/product-specification-scores/${form.id}` :
                        `{{ route('product-specification-scores.store') }}`"
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

                    <!-- SPECIFICATION -->
                    <select name="specification_id" x-model="form.specification_id"
                        class="w-full border rounded px-3 py-2" required :disabled="isEdit">
                        <option value="">-- Pilih Specification --</option>
                        @foreach ($specifications as $spec)
                            <option value="{{ $spec->id }}">
                                {{ $spec->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- RAW VALUE -->
                    <input type="number" step="0.01" name="raw_value" x-model="form.raw_value"
                        class="w-full border rounded px-3 py-2" placeholder="Raw Value" required>

                    <!-- NORMALIZED SCORE -->
                    <input type="number" step="0.01" name="normalized_score" x-model="form.normalized_score"
                        class="w-full border rounded px-3 py-2" placeholder="Normalized Score (0–100)" required>

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
        function productSpecScoreCrud() {
            return {
                open: false,
                isEdit: false,
                form: {
                    id: null,
                    product_id: '',
                    specification_id: '',
                    raw_value: '',
                    normalized_score: ''
                },

                openCreate() {
                    this.isEdit = false
                    this.form = {
                        id: null,
                        product_id: '',
                        specification_id: '',
                        raw_value: '',
                        normalized_score: ''
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
