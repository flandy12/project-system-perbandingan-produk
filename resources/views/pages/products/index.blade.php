<x-app-layout>
    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <div class="flex justify-between mb-4">
                    <h2 class="text-xl font-semibold">Product Management</h2>
                    <button onclick="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Tambah Produk
                    </button>
                </div>

                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">No</th>
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
                                <td class="border px-4 py-2 text-center">{{ $loop->iteration }}</td>
                                <td class="border px-4 py-2 text-center">{{ $product->title }}</td>
                                <td class="border px-4 py-2 text-center">{{ $product->category->name }}</td>
                                <td class="border px-4 py-2 text-center">Rp {{ number_format($product->price) }}</td>
                                <td class="border px-4 py-2 text-center">{{ $product->stock }}</td>
                                <td class="border px-4 py-2 text-center capitalize">{{ $product->status ?? '' }}</td>
                                <td class="border px-4 py-2 text-center space-x-2">
                                    <button onclick='openEdit(@json($product))' class="text-green-600">
                                        Edit
                                    </button>

                                    <form action="{{ route('products.destroy', $product) }}" method="POST"
                                        class="inline">
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
        <div id="modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center">
            <div class="bg-white w-full max-w-xl p-6 rounded">

                <h3 id="modalTitle" class="text-lg font-semibold mb-4">Tambah Produk</h3>

                <form id="productForm" action="{{ route('products.store') }}" method="POST"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" id="method">

                    <div>
                        <input type="text" name="title" id="title" value="{{ old('title') }}"
                            class="w-full border rounded px-3 py-2 @error('title') border-red-500 @enderror"
                            placeholder="Nama Produk">
                        @error('title')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <select name="category_id" id="category_id"
                            class="w-full border rounded px-3 py-2 @error('category_id') border-red-500 @enderror">
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <input type="file" name="image"
                            class="w-full border rounded px-3 py-2 @error('image') border-red-500 @enderror">
                        @error('image')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <input type="number" name="price" id="price" value="{{ old('price') }}"
                                class="border rounded px-3 py-2 w-full @error('price') border-red-500 @enderror"
                                placeholder="Harga">
                            @error('price')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <input type="number" name="stock" id="stock" value="{{ old('stock') }}"
                                class="border rounded px-3 py-2 w-full @error('stock') border-red-500 @enderror"
                                placeholder="Stok">
                            @error('stock')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="border rounded p-3">
                        <p class="font-semibold mb-2">Spesifikasi Produk</p>
                        @foreach ($specificationGroups as $group)
                            <p class="font-medium text-sm">{{ $group->name }}</p>
                            @foreach ($group->specifications as $spec)
                                <input type="text" name="specifications[{{ $spec->id }}]"
                                    id="spec_{{ $spec->id }}" value="{{ old('specifications.' . $spec->id) }}"
                                    placeholder="{{ $spec->name }}" class="w-full border rounded px-2 py-1 mt-1">
                            @endforeach
                        @endforeach
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="status" value="active" id="status"
                            {{ old('status', $product->status ?? '') === 'active' ? 'checked' : '' }}
                            class="rounded border-gray-300">
                        <label for="status" class="text-sm">Active</label>
                    </div>


                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded">
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

    @push('scripts')
        <script>
            function openCreate() {
                document.getElementById('modal').classList.remove('hidden')
                document.getElementById('modalTitle').innerText = 'Tambah Produk'
                document.getElementById('productForm').action = "{{ route('products.store') }}"
                document.getElementById('method').value = ''
                document.getElementById('productForm').reset()
            }

            function openEdit(product) {
                document.getElementById('modal').classList.remove('hidden')
                document.getElementById('modalTitle').innerText = 'Edit Produk'
                document.getElementById('productForm').action = `/products/${product.id}`
                document.getElementById('method').value = 'PUT'

                title.value = product.title
                price.value = product.price
                stock.value = product.stock
                category_id.value = product.category_id

                if (product.specifications) {
                    product.specifications.forEach(spec => {
                        const el = document.getElementById('spec_' + spec.specification_id)
                        if (el) el.value = spec.value
                    })
                }
            }

            function closeModal() {
                document.getElementById('modal').classList.add('hidden')
            }

            // AUTO OPEN MODAL JIKA VALIDASI ERROR
            @if ($errors->any())
                document.getElementById('modal').classList.remove('hidden')
            @endif
        </script>
    @endpush
</x-app-layout>
