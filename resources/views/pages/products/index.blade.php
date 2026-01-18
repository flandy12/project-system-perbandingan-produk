<x-app-layout>
    <div
        x-data="{
            open: {{ $errors->any() ? 'true' : 'false' }},
            showErrors: {{ $errors->any() ? 'true' : 'false' }},
            isEdit: {{ old('_method') === 'PUT' ? 'true' : 'false' }},
            form: {
                id: '{{ old('id') }}',
                title: '{{ old('title') }}',
                price: '{{ old('price') }}',
                stock: '{{ old('stock') }}',
                category_id: '{{ old('category_id') }}',
                specifications: @json(old('specifications', []))
            },

            openCreate() {
                this.isEdit = false
                this.showErrors = false
                this.form = {
                    id: null,
                    title: '',
                    price: '',
                    stock: '',
                    category_id: '',
                    specifications: []
                }
                this.open = true
            },

            openEdit(product) {
                this.isEdit = true
                this.showErrors = false
                this.form = product
                this.open = true
            },

            closeModal() {
                this.open = false
                this.showErrors = false
            }
        }"
        class="py-6"
    >

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <!-- HEADER -->
                <div class="flex justify-between mb-4">
                    <h2 class="text-xl font-semibold">Product Management</h2>
                    <button @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Tambah Produk
                    </button>
                </div>

                <!-- TABLE -->
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">Produk</th>
                            <th class="border px-4 py-2">Kategori</th>
                            <th class="border px-4 py-2">Harga</th>
                            <th class="border px-4 py-2">Stok</th>
                            <th class="border px-4 py-2">Status</th>
                            <th class="border px-4 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td class="border px-4 py-2 font-medium text-center">
                                    {{ $product->title }}
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    {{ $product->category->name }}
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    Rp {{ number_format($product->price) }}
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    {{ $product->stock }}
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    {{ $product->status }}
                                </td>
                                <td class="border px-4 py-2 text-center space-x-2">
                                    <button
                                        @click="openEdit({
                                            id: {{ $product->id }},
                                            title: @js($product->title),
                                            price: @js($product->price),
                                            stock: @js($product->stock),
                                            category_id: @js($product->category_id),
                                            specifications: @js(
                                                $product->specifications->map(fn($s) => [
                                                    'id' => $s->specification_id,
                                                    'value' => $s->value
                                                ])
                                            )
                                        })"
                                        class="text-green-600"
                                    >
                                        Edit
                                    </button>

                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus produk?')" class="text-red-600">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div @click.away="closeModal()" class="bg-white w-full max-w-xl p-6 rounded">

                <h3 class="text-lg font-semibold mb-4" x-text="isEdit ? 'Edit Produk' : 'Tambah Produk'"></h3>

                <form
                    :action="isEdit ? `/products/${form.id}` : `{{ route('products.store') }}`"
                    method="POST"
                    class="space-y-4"
                >
                    @csrf

                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- TITLE -->
                    <input type="text" name="title" x-model="form.title"
                        class="w-full border rounded px-3 py-2"
                        placeholder="Nama Produk">

                    <!-- CATEGORY -->
                    <select name="category_id" x-model="form.category_id"
                        class="w-full border rounded px-3 py-2">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>

                    <!-- PRICE & STOCK -->
                    <div class="grid grid-cols-2 gap-3">
                        <input type="number" name="price" x-model="form.price"
                            class="border rounded px-3 py-2" placeholder="Harga">

                        <input type="number" name="stock" x-model="form.stock"
                            class="border rounded px-3 py-2" placeholder="Stok">
                    </div>

                    <!-- SPECIFICATIONS -->
                    <div class="border rounded p-3 max-h-64 overflow-y-auto">
                        <p class="font-semibold mb-2">Spesifikasi Produk</p>

                        @foreach ($specificationGroups as $group)
                            <div class="mb-3">
                                <p class="font-medium text-sm">{{ $group->name }}</p>

                                @foreach ($group->specifications as $spec)
                                    <input type="text"
                                        name="specifications[{{ $loop->parent->index }}][value]"
                                        x-model="form.specifications.find(s => s.id === {{ $spec->id }})?.value"
                                        placeholder="{{ $spec->name }} {{ $spec->unit }}"
                                        class="w-full border rounded px-2 py-1 mt-1">
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <!-- ACTION -->
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
</x-app-layout>