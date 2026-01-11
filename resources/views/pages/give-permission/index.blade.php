<x-app-layout>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold mb-4">
                Give Permission to Role:
                <span class="text-blue-600">{{ $role->name }}</span>
            </h2>

            {{-- Error Validation --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route('roles.permissions.update', $role) }}"
                method="POST"
            >
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    @foreach ($permissions as $module => $items)
                        <div class="border rounded p-4">
                            <h3 class="font-semibold mb-3 capitalize">
                                {{ str_replace('_', ' ', $module) }}
                            </h3>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach ($items as $permission)
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->name }}"
                                            {{ in_array($permission->name, $rolePermissions, true) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-blue-600"
                                        >
                                        <span class="text-sm">
                                            {{ $permission->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('roles.index') }}"
                       class="px-4 py-2 border rounded">
                        Batal
                    </a>

                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded">
                        Simpan Permission
                    </button>
                </div>

            </form>
        </div>

    </div>
</x-app-layout>
