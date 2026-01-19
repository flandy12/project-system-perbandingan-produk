<x-app-layout>
    <div x-data="scoreWeightCrud()" class="py-6">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <!-- HEADER -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Score Weight Management</h2>

                    <button @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Tambah Weight
                    </button>
                </div>

                <!-- ALERT -->
                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- TABLE -->
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2 text-left">No</th>
                            <th class="border px-4 py-2 text-left">Key</th>
                            <th class="border px-4 py-2 text-center">Weight</th>
                            <th class="border px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($weights as $item)
                            <tr>
                                <td class="border px-4 py-2 font-medium">
                                    {{ $loop->iteration }}
                            </td>
                                <td class="border px-4 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                        {{ number_format($item->weight, 3) }}
                                    </span>
                                </td>

                                <td class="border px-4 py-2 text-center space-x-2">
                                    <button
                                        @click="openEdit({
                                            id: {{ $item->id }},
                                            key: @js($item->key),
                                            weight: @js($item->weight)
                                        })"
                                        class="text-green-600">
                                        Edit
                                    </button>

                                    <form action="{{ route('score-weights.destroy', $item) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Hapus weight ini?')" class="text-red-600">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-6 text-gray-500">
                                    Data score weight belum tersedia
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <!-- TOTAL -->
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            <td class="border px-4 py-2 text-right">
                                Total
                            </td>
                            <td class="border px-4 py-2 text-center">
                                {{ number_format($weights->sum('weight'), 3) }}
                            </td>
                            <td class="border"></td>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>

        <!-- MODAL -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">

            <div @click.away="closeModal()" class="bg-white w-full max-w-md p-6 rounded">

                <h3 class="text-lg font-semibold mb-4" x-text="isEdit ? 'Edit Weight' : 'Tambah Weight'">
                </h3>

                <form
                    :action="isEdit
                        ?
                        `/score-weights/${form.id}` :
                        `{{ route('score-weights.store') }}`"
                    method="POST" class="space-y-4">

                    @csrf

                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- KEY -->
                    <input type="text" name="key" x-model="form.key" class="w-full border rounded px-3 py-2"
                        placeholder="Key (ex: specification)" required :disabled="isEdit">

                    <!-- WEIGHT -->
                    <input type="number" step="0.001" min="0" max="1" name="weight"
                        x-model="form.weight" class="w-full border rounded px-3 py-2"
                        placeholder="Weight (0.000 - 1.000)" required>

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
        function scoreWeightCrud() {
            return {
                open: false,
                isEdit: false,
                form: {
                    id: null,
                    key: '',
                    weight: ''
                },

                openCreate() {
                    this.isEdit = false
                    this.form = {
                        id: null,
                        key: '',
                        weight: ''
                    }
                    this.open = true
                },

                openEdit(data) {
                    this.isEdit = true
                    this.form = data
                    this.open = true
                },

                closeModal() {
                    this.open = false
                }
            }
        }
    </script>
</x-app-layout>
