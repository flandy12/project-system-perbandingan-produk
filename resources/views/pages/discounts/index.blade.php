<x-app-layout>
    <div x-data="discountCrud()" class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <!-- HEADER -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Discount Management</h2>

                    <button @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Tambah Discount
                    </button>

                </div>

                <!-- TABLE -->
                <div class="overflow-x-auto">
                    <table class="min-w-full border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-4 py-2">Product</th>
                                <th class="border px-4 py-2 text-center">Diskon</th>
                                <th class="border px-4 py-2 text-center">Periode</th>
                                <th class="border px-4 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($discounts as $discount)
                                @php
                                    $isActive = now()->between($discount->start_date, $discount->end_date);
                                @endphp

                                <tr>
                                    <td class="border px-4 py-2 font-medium text-center">
                                        {{ $discount->product?->title ?? '-' }}
                                    </td>

                                    <td class="border px-4 py-2 text-center">
                                        {{ $discount->percentage }}%
                                    </td>

                                    <td class="border px-4 py-2 text-center text-sm">
                                        {{ $discount->start_date->format('d M Y') }}
                                        -
                                        {{ $discount->end_date->format('d M Y') }}
                                    </td>

                                    <td class="border px-4 py-2 text-center space-x-2">

                                        <button
                                            @click="openEdit({
                                                id: {{ $discount->id }},
                                                product_id: {{ $discount->product_id }},
                                                percentage: {{ $discount->percentage }},
                                                start_date: '{{ $discount->start_date->format('Y-m-d') }}',
                                                end_date: '{{ $discount->end_date->format('Y-m-d') }}'
                                            })"
                                            class="text-green-600">
                                            Edit
                                        </button>


                                        <form action="{{ route('discount.index') }}/{{ $discount->id }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="return confirm('Hapus discount ini?')"
                                                class="text-red-600">
                                                Delete
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-gray-500">
                                        Belum ada discount
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="mt-4">
                    {{ $discounts->links() }}
                </div>

            </div>
        </div>

        <!-- MODAL CREATE / EDIT -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div @click.away="closeModal()" class="bg-white w-full max-w-md p-6 rounded">
                <h3 class="text-lg font-semibold mb-4" x-text="isEdit ? 'Edit Discount' : 'Tambah Discount'"></h3>

                <form :action="isEdit ? `/discount/${form.id}` : `{{ route('discount.store') }}`" method="POST"
                    class="space-y-4">
                    @csrf

                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- PRODUCT -->
                    <select name="product_id" x-model="form.product_id" class="w-full border rounded px-3 py-2"
                        required>
                        <option value="">Pilih Product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->title }}
                            </option>
                        @endforeach
                    </select>

                    <!-- PERCENTAGE -->
                    <input type="number" name="percentage" x-model="form.percentage" min="1" max="100"
                        class="w-full border rounded px-3 py-2" placeholder="Diskon (%)" required>

                    <!-- START DATE -->
                    <input type="date" name="start_date" x-model="form.start_date"
                        class="w-full border rounded px-3 py-2" required>

                    <!-- END DATE -->
                    <input type="date" name="end_date" x-model="form.end_date"
                        class="w-full border rounded px-3 py-2" required>

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

    <!-- ALPINE SCRIPT -->
    <script>
        function discountCrud() {
            return {
                open: false,
                isEdit: false,
                form: {
                    id: null,
                    product_id: '',
                    percentage: '',
                    start_date: '',
                    end_date: ''
                },

                openCreate() {
                    this.isEdit = false
                    this.form = {
                        id: null,
                        product_id: '',
                        percentage: '',
                        start_date: '',
                        end_date: ''
                    }
                    this.open = true
                },

                openEdit(discount) {
                    this.isEdit = true
                    this.form = discount
                    this.open = true
                },

                closeModal() {
                    this.open = false
                }
            }
        }
    </script>
</x-app-layout>
