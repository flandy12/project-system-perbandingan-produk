<x-app-layout>
    <div x-data="{
        open: {{ $errors->any() ? 'true' : 'false' }},
        showErrors: {{ $errors->any() ? 'true' : 'false' }},
        isEdit: {{ old('_method') === 'PUT' ? 'true' : 'false' }},
        form: {
            id: '{{ old('id') }}',
            name: '{{ old('name') }}',
            email: '{{ old('email') }}',
            role: '{{ old('role') }}',
            is_active: '{{ old('is_active', 1) }}'
        },
    
        openCreate() {
            this.isEdit = false;
            this.showErrors = false;
            this.form = { id: null, name: '', email: '', role: '', is_active: 1 };
            this.open = true;
        },
    
        openEdit(user) {
            this.isEdit = true;
            this.showErrors = false;
            this.form = {
                id: user.id,
                name: user.name,
                email: user.email,
                role: user.roles?.length ? user.roles[0].name : '',
                is_active: user.is_active ? 1 : 0
            };
            this.open = true;
        },
    
        closeModal() {
            this.open = false;
            this.showErrors = false;
        }
    }" class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <div class="flex justify-between mb-4">
                    <h2 class="text-xl font-semibold">Daftar User</h2>
                    @can('user.create')
                        <button @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                            Tambah User
                        </button>
                    @endcan
                </div>

                <!-- TABLE -->
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">Nama</th>
                            <th class="border px-4 py-2">Email</th>
                            <th class="border px-4 py-2">Role</th>
                            <th class="border px-4 py-2">is_active</th>
                            @can(['user.update', 'user.delete'])
                                <th class="border px-4 py-2">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="border px-4 py-2 text-center capitalize">{{ $user->name }}</td>
                                <td class="border px-4 py-2 text-center">{{ $user->email }}</td>
                                <td class="border px-4 py-2 text-center capitalize">
                                    {{ $user->roles->pluck('name')->join(', ') }}
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    <span
                                        class="px-2 py-1 rounded text-sm text-center
                            {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700 text-center' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                @can(['user.update', 'user.delete'])
                                    <td class="border px-4 py-2 space-x-2 text-center">
                                        <button @click="openEdit({{ Js::from($user) }})" class="text-green-600">
                                            Edit
                                        </button>

                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                            class="inline">
                                            @csrf @method('DELETE')
                                            <button onclick="return confirm('Hapus user?')" class="text-red-600">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div @click.away="open=false" class="bg-white w-full max-w-lg p-6 rounded">

                <h3 class="text-lg font-semibold mb-4" x-text="isEdit ? 'Edit User' : 'Tambah User'"></h3>

                <form :action="isEdit ? `/users/${form.id}` : `{{ route('users.store') }}`" method="POST"
                    class="space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <input type="text" name="name" x-model="form.name" class="w-full border rounded px-3 py-2"
                        placeholder="Nama">
                    @error('name')
                        <div x-show="showErrors" class="text-red-500">{{ $message }}</div>
                    @enderror

                    <input type="email" name="email" x-model="form.email" class="w-full border rounded px-3 py-2"
                        placeholder="Email">
                    @error('email')
                        <div x-show="showErrors" class="text-red-500">{{ $message }}</div>
                    @enderror

                    <template x-if="!isEdit">
                        <div x-data="{ show: false }" class="relative">
                            <input :type="show ? 'text' : 'password'" name="password"
                                class="w-full border rounded px-3 py-2 pr-10" placeholder="Password">

                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-2 flex items-center text-gray-500 hover:text-gray-700">
                                <span x-text="show ? '🙈' : '👁️'"></span>
                            </button>
                        </div>
                    </template>

                    @error('password')
                        <div x-show="showErrors" class="text-red-500">{{ $message }}</div>
                    @enderror

                    <select name="role" x-model="form.role" class="w-full border rounded px-3 py-2">
                        <option value="">-- Pilih Role --</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <div x-show="showErrors" class="text-red-500">{{ $message }}</div>
                    @enderror

                    <select name="is_active" x-model="form.is_active" class="w-full border rounded px-3 py-2">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>

                    @error('is_active')
                        <div x-show="showErrors" class="text-red-500">{{ $message }}</div>
                    @enderror

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="open=false" class="px-4 py-2 border rounded">
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
