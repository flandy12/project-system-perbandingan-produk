<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- HEADER -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">
                    Specification Score Management
                </h2>

                <a href="{{ route('specification-scores.create') }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded">
                    + Tambah Data
                </a>
            </div>

            <!-- FLASH MESSAGE -->
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- TABLE -->
            <div class="bg-white shadow rounded overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border text-left">Specification</th>
                            <th class="p-2 border text-center">Status</th>
                            <th class="p-2 border text-center w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scores as $score)
                            <tr class="hover:bg-gray-50">
                                <td class="p-2 border">
                                    {{ $score->specification->name ?? '-' }}
                                </td>

                                <td class="p-2 border text-center">
                                    @if ($score->is_used)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>

                                <td class="p-2 border text-center space-x-1">
                                    <a href="{{ route('specification-scores.edit', $score) }}"
                                       class="px-3 py-1 bg-yellow-500 text-white rounded text-sm">
                                        Edit
                                    </a>

                                    <form action="{{ route('specification-scores.destroy', $score) }}"
                                          method="POST"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            onclick="return confirm('Hapus data ini?')"
                                            class="px-3 py-1 bg-red-600 text-white rounded text-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3"
                                    class="p-4 text-center text-gray-500">
                                    Data specification score belum tersedia
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- PAGINATION -->
                <div class="p-4">
                    {{ $scores->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>