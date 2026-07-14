<x-app-layout>
    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <div class="flex justify-between mb-4">
                    <h2 class="text-xl font-semibold">Product Management</h2>
                    @can('create.product')
                        <button onclick="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                            Tambah Produk
                        </button>
                    @endcan
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
                            @canany(['edit.product', 'delete.product'])
                                <th class="border px-4 py-2">Aksi</th>
                            @endcanany
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
                                @canany(['edit.product', 'delete.product'])
                                    <td class="border px-4 py-2 text-center space-x-2">

                                        @can('edit.roduct')
                                            <button onclick='openEdit(@json($product))' class="text-green-600">
                                                Edit
                                            </button>
                                        @endcan

                                        @can('delete.product')
                                            <form action="{{ route('products.destroy', $product) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')

                                                <button onclick="return confirm('Hapus produk?')" class="text-red-600">
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
                    {{ $products->links() }}
                </div>
            </div>
        </div>

        <!-- MODAL -->
        @canany(['create.product', 'edit.roduct'])
        <div id="modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center">
            <div class="bg-white w-full max-w-xl p-6 rounded">

                <h3 id="modalTitle" class="text-lg font-semibold mb-4">Tambah Produk</h3>

                <form id="productForm" action="{{ route('products.store') }}" method="POST"
                    enctype="multipart/form-data">

                    @csrf

                    <input type="hidden" name="_method" id="method">

                    <div class="max-h-[75vh] overflow-y-auto px-1 space-y-6">

                        {{-- INFORMASI PRODUK --}}
                        <div class="bg-gray-50 border rounded-xl p-5 space-y-4">

                            <h3 class="font-semibold text-lg text-gray-800">
                                Informasi Produk
                            </h3>

                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    Nama Produk
                                </label>

                                <input type="text" name="title" id="title" value="{{ old('title') }}"
                                    placeholder="Masukkan nama produk"
                                    class="w-full rounded-lg border-gray-300 focus:border-sky-500 focus:ring-sky-500 @error('title') border-red-500 @enderror">

                                @error('title')
                                    <p class="text-red-500 text-sm mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    Kategori
                                </label>

                                <select name="category_id" id="category_id"
                                    class="w-full rounded-lg border-gray-300 focus:border-sky-500 focus:ring-sky-500 @error('category_id') border-red-500 @enderror">

                                    <option value="">
                                        Pilih Kategori
                                    </option>

                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach

                                </select>

                                @error('category_id')
                                    <p class="text-red-500 text-sm mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>

                        {{-- GAMBAR --}}
                        <div x-data="{
                            preview: '{{ isset($product) && $product->image ? asset('storage/' . $product->image) : '' }}'
                        }" class="bg-gray-50 border rounded-xl p-5">

                            <h3 class="font-semibold text-lg text-gray-800 mb-4">
                                Gambar Produk
                            </h3>

                            <label for="image"
                                class="border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center cursor-pointer hover:border-sky-400 transition">

                                <!-- Preview Gambar -->
                                <template x-if="preview">
                                    <div class="w-full flex flex-col items-center">

                                        <img :src="preview"
                                            class="w-full max-h-72 object-contain rounded-xl shadow mb-4">

                                        <span class="text-sm text-sky-600">
                                            Klik untuk mengganti gambar
                                        </span>

                                    </div>
                                </template>

                                <!-- Placeholder -->
                                <template x-if="!preview">
                                    <div class="flex flex-col items-center">

                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400 mb-3"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />

                                        </svg>

                                        <p class="text-gray-600 font-medium">
                                            Klik untuk upload gambar
                                        </p>

                                        <p class="text-sm text-gray-400">
                                            JPG, PNG, WEBP
                                        </p>

                                    </div>
                                </template>

                                <input type="file" id="image" name="image" accept="image/*" class="hidden"
                                    @change="
                const file = $event.target.files[0];

                if(file){
                    preview = URL.createObjectURL(file);
                }
            ">

                            </label>

                            <!-- Tombol Hapus Preview -->
                            <div x-show="preview" class="mt-3 text-center">

                                <button type="button"
                                    @click="
                preview = '';
                document.getElementById('image').value = '';
            "
                                    class="text-red-500 hover:text-red-700 text-sm">

                                    Hapus Gambar

                                </button>

                            </div>

                            @error('image')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- HARGA & INVENTORI --}}
                        <div class="bg-gray-50 border rounded-xl p-5">

                            <h3 class="font-semibold text-lg text-gray-800 mb-4">
                                Harga & Inventori
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        Harga
                                    </label>

                                    <input type="number" name="price" id="price" value="{{ old('price') }}"
                                        placeholder="Masukkan harga"
                                        class="w-full rounded-lg border-gray-300 focus:border-sky-500 focus:ring-sky-500 @error('price') border-red-500 @enderror">

                                    @error('price')
                                        <p class="text-red-500 text-sm mt-1">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        Stok
                                    </label>

                                    <input type="number" name="stock" id="stock" value="{{ old('stock') }}"
                                        placeholder="Masukkan stok"
                                        class="w-full rounded-lg border-gray-300 focus:border-sky-500 focus:ring-sky-500 @error('stock') border-red-500 @enderror">

                                    @error('stock')
                                        <p class="text-red-500 text-sm mt-1">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                            </div>

                        </div>

                        {{-- TAHUN & STATUS --}}
                        <div class="bg-gray-50 border rounded-xl p-5">

                            <h3 class="font-semibold text-lg text-gray-800 mb-4">
                                Informasi Tambahan
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        Tahun Pembuatan
                                    </label>

                                    <select name="production_year" id="production_year"
                                        class="w-full rounded-lg border-gray-300 focus:border-sky-500 focus:ring-sky-500">

                                        <option value="">
                                            Pilih Tahun
                                        </option>

                                        @for ($year = date('Y'); $year >= 2000; $year--)
                                            <option value="{{ $year }}"
                                                {{ old('production_year', $product->production_year ?? '') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor

                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        Status Produk
                                    </label>

                                    <select name="status" id="status"
                                        class="w-full rounded-lg border-gray-300 focus:border-sky-500 focus:ring-sky-500">

                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>

                                        <option value="disable" {{ old('status') == 'disable' ? 'selected' : '' }}>
                                            Disable
                                        </option>

                                    </select>
                                </div>

                            </div>

                        </div>

                        {{-- SPESIFIKASI --}}
                        <div class="bg-gray-50 border rounded-xl p-5">

                            <h3 class="font-semibold text-lg text-gray-800 mb-5">
                                Spesifikasi Produk
                            </h3>

                            @foreach ($specificationGroups as $group)
                                <div class="mb-6">

                                    <div class="border-b pb-2 mb-4">
                                        <h4 class="font-semibold text-sky-700">
                                            {{ $group->name }}
                                        </h4>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                        @foreach ($group->specifications as $spec)
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">
                                                    {{ $spec->name }}
                                                </label>

                                                <input type="text" name="specifications[{{ $spec->id }}]"
                                                    value="{{ old('specifications.' . $spec->id) }}"
                                                    placeholder="Masukkan {{ $spec->name }}"
                                                    class="w-full rounded-lg border-gray-300 focus:border-sky-500 focus:ring-sky-500">
                                            </div>
                                        @endforeach

                                    </div>

                                </div>
                            @endforeach

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div class="sticky bottom-0 bg-white border-t pt-4 mt-6 flex justify-end gap-3">

                        <button type="button" onclick="closeModal()"
                            class="px-5 py-2.5 border rounded-lg hover:bg-gray-50">

                            Batal

                        </button>

                        <button type="submit"
                            class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-lg shadow">

                            Simpan Produk

                        </button>

                    </div>

                </form>
            </div>
        </div>
        @endcanany
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
