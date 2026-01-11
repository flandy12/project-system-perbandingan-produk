<x-app-layout>
    <div x-data="{
        open: {{ $errors->any() ? 'true' : 'false' }},
        showErrors: {{ $errors->any() ? 'true' : 'false' }},
        isEdit: {{ old('_method') === 'PUT' ? 'true' : 'false' }},
        form: {
            id: '{{ old('id') }}',
            name: '{{ old('name') }}'
        },
    
        openCreate() {
            this.isEdit = false
            this.showErrors = false
            this.form = { id: null, name: '' }
            this.open = true
        },
    
        openEdit(permission) {
            this.isEdit = true
            this.showErrors = false
            this.form = {
                id: permission.id,
                name: permission.name
            }
            this.open = true
        },
    
        closeModal() {
            this.open = false
            this.showErrors = false
        }
    }" class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <div class="flex justify-between mb-4">
                    <h2 class="text-xl font-semibold">Permission Management</h2>
                    <button @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Tambah Permission
                    </button>
                </div>

                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">Permission</th>
                            <th class="border px-4 py-2">Guard</th>
                            <th class="border px-4 py-2">Total Role</th>
                            <th class="border px-4 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $permission)
                            <tr>
                                <td class="border px-4 py-2 text-center font-medium">
                                    {{ $permission->name }}
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    {{ $permission->guard_name }}
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    {{ $permission->roles_count }}
                                </td>
                                <td class="border px-4 py-2 text-center space-x-2">
                                    <button @click="openEdit({{ Js::from($permission) }})" class="text-green-600">
                                        Edit
                                    </button>

                                    <form action="{{ route('permissions.destroy', $permission) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus permission?')" class="text-red-600">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $permissions->links() }}
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div @click.away="closeModal()" class="bg-white w-full max-w-md p-6 rounded">

                <h3 class="text-lg font-semibold mb-4" x-text="isEdit ? 'Edit Permission' : 'Tambah Permission'"></h3>

                <form :action="isEdit ? `/permissions/${form.id}` : `{{ route('permissions.store') }}`" method="POST"
                    class="space-y-4">
                    @csrf

                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <input type="hidden" name="id" :value="form.id">

                    <input type="text" name="name" x-model="form.name" class="w-full border rounded px-3 py-2"
                        placeholder="Nama Permission">

                    @error('name')
                        <div x-show="showErrors" class="text-red-500 text-sm">
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
</x-app-layout>
