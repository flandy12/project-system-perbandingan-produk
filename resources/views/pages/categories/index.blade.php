<x-app-layout>
    <div x-data="categoryCrud({{ $errors->any() ? 'true' : 'false' }})" class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <!-- HEADER -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Category Management</h2>

                    <button @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Tambah Kategori
                    </button>
                </div>

                <!-- TABLE -->
                <table class="min-w-full border text-sm text-center">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-3 py-2">
                               No
                            </th>
                            <th class="border px-3 py-2">Nama</th>
                            <th class="border px-3 py-2">Status</th>
                            <th class="border px-3 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td class="border px-4 py-2 font-medium text-center">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="border px-3 py-2 font-semibold text-center">
                                    {{ $category->name }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <span
                                        class="px-2 py-1 rounded text-xs
                                        {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                <td class="border px-3 py-2 text-center space-x-2">
                                    <button @click="openEdit({{ Js::from($category) }})" class="text-green-600">
                                        Edit
                                    </button>

                                    <form action="{{ route('category.destroy', $category) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus kategori?')" class="text-red-600">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

        <!-- MODAL -->
        <div x-show="open" x-cloak x-transition
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div @click.away="closeModal()" class="bg-white w-full max-w-md p-6 rounded shadow-lg">
                <h3 class="text-lg font-semibold mb-4" x-text="isEdit ? 'Edit Kategori' : 'Tambah Kategori'"></h3>

                <form
                    :action="isEdit
                        ?
                        routeUpdate.replace(':id', form.id) :
                        routeStore"
                    method="POST" class="space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- NAME -->
                    <input type="text" name="name" x-model="form.name" class="w-full border rounded px-3 py-2"
                        placeholder="Nama kategori">
                    @error('name')
                        <p x-show="showErrors" x-cloak class="text-red-600 text-sm">
                            {{ $message }}
                        </p>
                    @enderror

                    <!-- STATUS -->
                    <select name="is_active" x-model="form.is_active" class="w-full border rounded px-3 py-2">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>

                    @error('is_active')
                        <p x-show="showErrors" x-cloak class="text-red-600 text-sm">
                            {{ $message }}
                        </p>
                    @enderror

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

    <!-- ALPINE -->
    <script>
        function categoryCrud(hasError = false) {
            return {
                open: hasError,
                showErrors: hasError,
                isEdit: {{ old('_method') === 'PUT' ? 'true' : 'false' }},

                routeStore: "{{ route('category.store') }}",
                routeUpdate: "{{ route('category.update', ':id') }}",

                form: {
                    id: "{{ old('id') }}",
                    name: "{{ old('name') }}",
                    is_active: "{{ old('is_active', 1) }}",
                },

                openCreate() {
                    this.isEdit = false
                    this.showErrors = false
                    this.form = {
                        id: null,
                        name: '',
                        is_active: 1
                    }
                    this.open = true
                },

                openEdit(category) {
                    this.isEdit = true
                    this.showErrors = false
                    this.form = {
                        id: category.id,
                        name: category.name,
                        is_active: category.is_active ? 1 : 0,
                    }
                    this.open = true
                },

                closeModal() {
                    this.open = false
                    this.showErrors = false
                    this.form = {
                        id: null,
                        name: '',
                        is_active: 1
                    }
                }
            }
        }
    </script>

</x-app-layout>
