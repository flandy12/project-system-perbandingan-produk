<x-app-layout>
    <div x-data="specCrud()" class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <!-- HEADER -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Specification</h2>

                    <button @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Tambah Specification
                    </button>
                </div>

                <!-- TABLE -->
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">No</th>
                            <th class="border px-4 py-2">Nama</th>
                            <th class="border px-4 py-2">Group</th>
                            <th class="border px-4 py-2 text-center">Tipe Data</th>
                            <th class="border px-4 py-2 text-center">Unit</th>
                            <th class="border px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($specifications as $spec)
                            <tr>
                                <td class="border px-4 py-2 font-medium text-center">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="border px-4 py-2 font-medium text-center">
                                    {{ $spec->name }}
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    {{ $spec->group->name ?? '-' }}
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded bg-gray-200">
                                        {{ ucfirst($spec->data_type) }}
                                    </span>
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    {{ $spec->unit ?? '-' }}
                                </td>

                                <td class="border px-4 py-2 text-center space-x-2">
                                    <button
                                        @click="openEdit({
                                            id: {{ $spec->id }},
                                            name: @js($spec->name),
                                            group_id: {{ $spec->specification_group_id }},
                                            data_type: @js($spec->data_type),
                                            unit: @js($spec->unit)
                                        })"
                                        class="text-green-600">
                                        Edit
                                    </button>

                                    <form action="{{ route('specifications.destroy', $spec) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus specification ini?')"
                                            class="text-red-600">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-gray-500">
                                    Belum ada specification
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- PAGINATION -->
                <div class="mt-4">
                    {{ $specifications->links() }}
                </div>

            </div>
        </div>

        <!-- MODAL -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div @click.away="closeModal()" class="bg-white w-full max-w-md p-6 rounded">

                <h3 class="text-lg font-semibold mb-4" x-text="isEdit ? 'Edit Specification' : 'Tambah Specification'">
                </h3>

                <form
                    :action="isEdit
                        ?
                        `/specifications/${form.id}` :
                        `{{ route('specifications.store') }}`"
                    method="POST" class="space-y-4">

                    @csrf

                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <input type="text" name="name" x-model="form.name" class="w-full border rounded px-3 py-2"
                        placeholder="Nama Specification" required>

                    <select name="specification_group_id" x-model="form.group_id"
                        class="w-full border rounded px-3 py-2" required>
                        <option value="">-- Pilih Group --</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}">
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="data_type" x-model="form.data_type" class="w-full border rounded px-3 py-2" required>
                        <option value="">-- Tipe Data --</option>
                        <option value="numeric">Numeric</option>
                        <option value="boolean">Boolean</option>
                        <option value="text">Text</option>
                    </select>

                    <input type="text" name="unit" x-model="form.unit" class="w-full border rounded px-3 py-2"
                        placeholder="Unit (opsional)">

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
        function specCrud() {
            return {
                open: false,
                isEdit: false,
                form: {
                    id: null,
                    name: '',
                    group_id: '',
                    data_type: '',
                    unit: ''
                },

                openCreate() {
                    this.isEdit = false
                    this.form = {
                        id: null,
                        name: '',
                        group_id: '',
                        data_type: '',
                        unit: ''
                    }
                    this.open = true
                },

                openEdit(spec) {
                    this.isEdit = true
                    this.form = spec
                    this.open = true
                },

                closeModal() {
                    this.open = false
                }
            }
        }
    </script>
</x-app-layout>
