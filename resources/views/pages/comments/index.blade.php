<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Comment Management
                </h1>
                <p class="text-sm text-slate-500">
                    Manage all user comments.
                </p>
            </div>

            @can('create.comment')
                <a href="{{ route('comments.create') }}"
                    class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 transition">
                    + Add Comment
                </a>
            @endcan
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-100 border border-green-300 text-green-700 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        <!-- Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">
                            #
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">
                            User
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">
                            Product
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">
                            Comment
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase">
                            Status
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase">
                            Date
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase">
                            Action
                        </th>

                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse($comments as $comment)
                        <tr class="hover:bg-slate-50">

                            <td class="px-6 py-4">
                                {{ $loop->iteration + ($comments->firstItem() - 1) }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $comment->user->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                @if ($comment->commentable instanceof \App\Models\Product)
                                    {{ $comment->commentable->title }}
                                @endif
                            </td>

                            <td class="px-6 py-4 max-w-md">
                                <p class="line-clamp-2">
                                    {{ $comment->comment }}
                                </p>
                            </td>

                            <td class="px-6 py-4 text-center">

                                @if ($comment->status == 'approved')
                                    <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                        Approved
                                    </span>
                                @elseif($comment->status == 'pending')
                                    <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                                        Pending
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-700">
                                        Rejected
                                    </span>
                                @endif

                            </td>

                            <td class="px-6 py-4 text-center">
                                {{ $comment->created_at->format('d M Y') }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">

                                    @can('comment.edit')
                                        @if ($comment->status != 'approved')
                                            <form action="{{ route('comments.approve', $comment) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    class="px-3 py-1 rounded bg-green-500 text-white hover:bg-green-600">
                                                    Approve
                                                </button>
                                            </form>
                                        @endif

                                        @if ($comment->status != 'rejected')
                                            <form action="{{ route('comments.reject', $comment) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    class="px-3 py-1 rounded bg-yellow-500 text-white hover:bg-yellow-600">
                                                    Reject
                                                </button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('comment.delete')
                                        <form action="{{ route('comments.destroy', $comment) }}" method="POST"
                                            onsubmit="return confirm('Delete this comment?')">

                                            @csrf
                                            @method('DELETE')

                                            <button class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600">
                                                Delete
                                            </button>

                                        </form>
                                    @endcan

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">

                                No comments found.

                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $comments->links() }}
        </div>

    </div>
</x-app-layout>
