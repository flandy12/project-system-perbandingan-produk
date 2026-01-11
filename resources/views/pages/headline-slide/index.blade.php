<x-app-layout>
    <div
        x-data="headlineCrud()"
        class="py-6"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <!-- HEADER -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Headline Management</h2>

                    <button
                        @click="openCreate()"
                        class="px-4 py-2 bg-blue-600 text-white rounded"
                    >
                        Tambah Headline
                    </button>
                </div>

                <!-- TABLE -->
                <table class="min-w-full border text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-3 py-2">Preview</th>
                            <th class="border px-3 py-2">Judul</th>
                            <th class="border px-3 py-2">Urutan</th>
                            <th class="border px-3 py-2">Status</th>
                            <th class="border px-3 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @foreach ($headlines as $headline)
                            <tr>
                                <td class="border px-3 py-2 text-center">
                                    <img
                                        src="{{ asset('storage/'.$headline->image) }}"
                                        class="w-20 h-12 object-cover rounded mx-auto"
                                    >
                                </td>

                                <td class="border px-3 py-2">
                                    <p class="font-semibold">{{ $headline->title }}</p>
                                    <p class="text-gray-500 text-xs line-clamp-2">
                                        {{ $headline->subtitle }}
                                    </p>
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    {{ $headline->position }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <span
                                        class="px-2 py-1 rounded text-xs
                                        {{ $headline->is_active
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700' }}"
                                    >
                                        {{ $headline->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                <td class="border px-3 py-2 text-center space-x-2">
                                    <button
                                        @click="openEdit({{ Js::from($headline) }})"
                                        class="text-green-600"
                                    >
                                        Edit
                                    </button>

                                    <form
                                        action="{{ route('headlines.destroy', $headline) }}"
                                        method="POST"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            onclick="return confirm('Hapus headline?')"
                                            class="text-red-600"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach --}}
                    </tbody>
                </table>

            </div>
        </div>

        <!-- MODAL -->
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center"
        >
            <div
                @click.away="closeModal()"
                class="bg-white w-full max-w-lg p-6 rounded"
            >
                <h3
                    class="text-lg font-semibold mb-4"
                    x-text="isEdit ? 'Edit Headline' : 'Tambah Headline'"
                ></h3>

                <form
                    :action="isEdit ? `/headline-slide/${form.id}` : `{{ route('headline-slide.store') }}`"
                    method="POST"
                    enctype="multipart/form-data"
                    class="space-y-4"
                >
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- TITLE -->
                    <input
                        type="text"
                        name="title"
                        x-model="form.title"
                        class="w-full border rounded px-3 py-2"
                        placeholder="Judul Headline"
                        required
                    >

                    <!-- SUBTITLE -->
                    <textarea
                        name="subtitle"
                        x-model="form.subtitle"
                        rows="3"
                        class="w-full border rounded px-3 py-2"
                        placeholder="Deskripsi singkat"
                    ></textarea>

                    <!-- LINK -->
                    <input
                        type="url"
                        name="link"
                        x-model="form.link"
                        class="w-full border rounded px-3 py-2"
                        placeholder="Link (opsional)"
                    >

                    <!-- IMAGE -->
                    <div>
                        <input
                            type="file"
                            name="image"
                            class="w-full border rounded px-3 py-2"
                            :required="!isEdit"
                        >

                        <template x-if="form.image">
                            <img
                                :src="form.image"
                                class="mt-2 w-full h-40 object-cover rounded"
                            >
                        </template>
                    </div>

                    <!-- POSITION -->
                    <label>Position</label>
                    <input
                        type="number"
                        name="position"
                        x-model="form.position"
                        class="w-full border rounded px-3 py-2"
                        placeholder="Urutan slider"
                        required
                    >

                    <!-- STATUS -->
                    <select
                        name="is_active"
                        x-model="form.is_active"
                        class="w-full border rounded px-3 py-2"
                    >
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>

                    <!-- ACTION -->
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="closeModal()"
                            class="px-4 py-2 border rounded"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ALPINE -->
    <script>
        function headlineCrud() {
            return {
                open: false,
                isEdit: false,
                form: {
                    id: null,
                    title: '',
                    subtitle: '',
                    link: '',
                    image: '',
                    position: 1,
                    is_active: 1,
                },

                openCreate() {
                    this.isEdit = false
                    this.form = {
                        id: null,
                        title: '',
                        subtitle: '',
                        link: '',
                        image: '',
                        position: 1,
                        is_active: 1,
                    }
                    this.open = true
                },

                openEdit(headline) {
                    this.isEdit = true
                    this.form = {
                        id: headline.id,
                        title: headline.title,
                        subtitle: headline.subtitle,
                        link: headline.link,
                        image: '/storage/' + headline.image,
                        position: headline.position,
                        is_active: headline.is_active ? 1 : 0,
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
