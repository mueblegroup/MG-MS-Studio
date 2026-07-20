<x-app-layout>
    <div class="min-h-screen bg-gray-50/60 p-4 dark:bg-gray-900 sm:p-8">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Class Cards</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Create and manage class card products.</p>
            </div>
            <div class="grid grid-cols-1 gap-2 sm:flex sm:items-center">
                <a href="{{ route('admin.classcards.classcard-purchases') }}" class="mg-btn-secondary">Purchases</a>
                <a href="{{ route('admin.classcards.create') }}" class="mg-btn-primary">Add Class Card</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-green-700">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.classcards.index') }}" class="mb-4 grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_auto_auto_auto]">
            <input name="q" value="{{ $search ?? request('q','') }}" placeholder="Search class card name..." class="mg-input min-w-0" />
            <button type="submit" class="mg-btn-primary"><i class="bx bx-search"></i> Search</button>
            <a href="{{ route('admin.classcards.index') }}" class="mg-btn-secondary"><i class="bx bx-reset"></i> Reset</a>
            <select name="per_page" onchange="this.form.submit()" class="mg-select">
                @foreach([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>{{ $size }} rows</option>
                @endforeach
            </select>
        </form>

        {{-- Mobile cards --}}
        <div class="space-y-3 md:hidden">
            @forelse($cards as $card)
                <article class="mg-card min-w-0 p-4">
                    <div class="flex min-w-0 items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="break-words font-bold text-gray-900 dark:text-white">{{ $card->name }}</h2>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $card->total_classes }} class credits · {{ $card->validity_weeks }} weeks validity</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $card->is_active ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                            {{ $card->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                            <dt class="text-xs font-bold uppercase text-gray-500">Credits</dt>
                            <dd class="mt-1 font-semibold">{{ $card->total_classes }}</dd>
                        </div>
                        <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                            <dt class="text-xs font-bold uppercase text-gray-500">Validity</dt>
                            <dd class="mt-1 font-semibold">{{ $card->validity_weeks }} weeks</dd>
                        </div>
                        <div class="col-span-2 rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                            <dt class="text-xs font-bold uppercase text-gray-500">Price</dt>
                            <dd class="mt-1 font-semibold">RM {{ number_format($card->price, 2) }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.classcards.show', $card) }}" class="mg-btn-secondary"><i class="bx bx-show"></i> View</a>
                        <a href="{{ route('admin.classcards.edit', $card) }}" class="mg-btn-secondary"><i class="bx bx-edit"></i> Edit</a>
                        <form method="POST" action="{{ route('admin.classcards.destroy', $card) }}" onsubmit="return confirm('Delete this class card?')" class="col-span-2">
                            @csrf @method('DELETE')
                            <button type="submit" class="mg-btn-danger w-full"><i class="bx bx-trash"></i> Remove</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="mg-card p-8 text-center text-sm text-gray-500 dark:text-gray-400">No class cards found.</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 md:block">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Credits</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Validity</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($cards as $card)
                            <tr>
                                <td class="px-4 py-4 font-semibold text-gray-900 dark:text-white">{{ $card->name }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $card->total_classes }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $card->validity_weeks }} weeks</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">RM {{ number_format($card->price, 2) }}</td>
                                <td class="px-4 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $card->is_active ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">{{ $card->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4 text-right">
                                    <a href="{{ route('admin.classcards.show', $card) }}" class="mg-btn-secondary min-h-9 px-3 py-1.5"><i class="bx bx-show"></i> View</a>
                                    <a href="{{ route('admin.classcards.edit', $card) }}" class="mg-btn-secondary min-h-9 px-3 py-1.5"><i class="bx bx-edit"></i> Edit</a>
                                    <form method="POST" action="{{ route('admin.classcards.destroy', $card) }}" onsubmit="return confirm('Delete this class card?')" class="inline">@csrf @method('DELETE')<button type="submit" class="mg-btn-danger"><i class="bx bx-trash"></i> Remove</button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No class cards found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 min-w-0 overflow-x-auto">{{ $cards->links() }}</div>
    </div>
</x-app-layout>
