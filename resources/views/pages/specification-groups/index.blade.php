<x-app-layout>
    <div x-data="specGroupCrud()" class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <!-- HEADER -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Specification Group</h2>

                    <button @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Tambah Group
                    </button>

                </div>

                <!-- TABLE -->
                <table class="min-w-full border text-center">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">No</th>
                            <th class="border px-4 py-2">Nama Group</th>
                            <th class="border px-4 py-2 text-center">Total Spesifikasi</th>
                            <th class="border px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groups as $group)
                            <tr>
                                <td class="border px-4 py-2 font-medium text-center">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="border px-4 py-2 font-medium text-center">
                                    {{ $group->name }}
                                </td>

                                <td class="border px-4 py-2 text-center">
                                    {{ $group->specifications_count }}
                                </td>

                                <td class="border px-4 py-2 text-center space-x-2">

                                    <button
                                        @click="openEdit({
                                            id: {{ $group->id }},
                                            name: @js($group->name)
                                        })"
                                        class="text-green-600">
                                        Edit
                                    </button>

                                    <form action="{{ route('specification-groups.destroy', $group) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus specification group ini?')"
                                            class="text-red-600">
                                            Delete
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-6 text-gray-500">
                                    Belum ada specification group
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- PAGINATION -->
                <div class="mt-4">
                    {{ $groups->links() }}
                </div>

            </div>
        </div>

        <!-- MODAL CREATE / EDIT -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div @click.away="closeModal()" class="bg-white w-full max-w-md p-6 rounded">
                <h3 class="text-lg font-semibold mb-4"
                    x-text="isEdit ? 'Edit Specification Group' : 'Tambah Specification Group'"></h3>

                <form
                    :action="isEdit ? `/specification-groups/${form.id}` : `{{ route('specification-groups.store') }}`"
                    method="POST" class="space-y-4">
                    @csrf

                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <input type="text" name="name" x-model="form.name" class="w-full border rounded px-3 py-2"
                        placeholder="Nama Group (Hardware, Multimedia, dll)" required>

                    @error('name')
                        <div class="text-red-500 text-sm">
                            {{ $message }}
                        </div>
                    @enderror

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
        function specGroupCrud() {
            return {
                open: false,
                isEdit: false,
                form: {
                    id: null,
                    name: ''
                },

                openCreate() {
                    this.isEdit = false
                    this.form = {
                        id: null,
                        name: ''
                    }
                    this.open = true
                },

                openEdit(group) {
                    this.isEdit = true
                    this.form = {
                        id: group.id,
                        name: group.name
                    }
                    this.open = true
                },

                closeModal() {
                    this.open = false
                }
            }
        }
    </script>
</x-app-layout>
