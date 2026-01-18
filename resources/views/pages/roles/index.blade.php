<x-app-layout>
    <div<div x-data="{
        open: {{ $errors->any() ? 'true' : 'false' }},
        showErrors: {{ $errors->any() ? 'true' : 'false' }},
        isEdit: {{ old('_method') === 'PUT' ? 'true' : 'false' }},
        form: {
            id: '{{ old('id') }}',
            name: '{{ old('name') }}',
            permissions: @json(old('permissions', []))
        },
    
        openCreate() {
            this.isEdit = false
            this.showErrors = false
            this.form = { id: null, name: '', permissions: [] }
            this.open = true
        },
    
        openEdit(role) {
            this.isEdit = true
            this.showErrors = false
            this.form = {
                id: role.id,
                name: role.name,
                permissions: role.permissions ?? []
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
                    <h2 class="text-xl font-semibold">Role Management</h2>
                    <button @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Tambah Role
                    </button>
                </div>

                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">Role</th>
                            <th class="border px-4 py-2">Guard</th>
                            <th class="border px-4 py-2">Permission</th>
                            <th class="border px-4 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td class="border px-4 py-2 text-center font-medium">
                                    {{ $role->name }}
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    {{ $role->guard_name }}
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    {{ $role->permissions->pluck('name')->join(', ') }}
                                </td>
                                <td class="border px-4 py-2 text-center space-x-2">
                                    <button
                                        @click="openEdit({ id: {{ $role->id }}, name: @js($role->name), permissions: @js($role->permissions->pluck('name'))})"
                                        class="text-green-600">
                                        Edit
                                    </button>

                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus role?')" class="text-red-600">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div @click.away="closeModal()" class="bg-white w-full max-w-md p-6 rounded">

                <h3 class="text-lg font-semibold mb-4" x-text="isEdit ? 'Edit Role' : 'Tambah Role'"></h3>

                <form :action="isEdit ? `/roles/${form.id}` : `{{ route('roles.store') }}`" method="POST"
                    class="space-y-4">
                    @csrf

                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <input type="text" name="name" x-model="form.name" class="w-full border rounded px-3 py-2"
                        placeholder="Nama Role">

                    @error('name')
                        <div x-show="showErrors" class="text-red-500 text-sm">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="border rounded p-3 max-h-48 overflow-y-auto">
                        <p class="font-semibold mb-2">Permissions</p>

                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($permissions as $permission)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" value="{{ $permission->name }}" x-model="form.permissions"
                                        name="permissions[]" class="rounded border">
                                    {{ $permission->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @error('permissions')
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
