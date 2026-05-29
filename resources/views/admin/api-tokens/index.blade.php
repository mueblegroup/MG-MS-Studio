<x-app-layout>
    <div class="min-h-screen bg-gray-50/50 p-4 dark:bg-gray-900 sm:p-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">API Management</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Create secure API tokens, control permissions, and monitor API activity.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.api-tokens.docs') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    API Docs
                </a>
                <a href="{{ route('admin.api-tokens.create') }}" class="rounded-xl bg-[#d97706] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#b45309]">
                    Create Token
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if($plainTextToken)
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-800 dark:bg-amber-950">
                <div class="mb-2 text-sm font-extrabold text-amber-900 dark:text-amber-100">Copy this token now</div>
                <p class="mb-3 text-xs text-amber-800 dark:text-amber-200">For security, this token will not be shown again.</p>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <input id="plainApiToken" readonly value="{{ $plainTextToken }}" class="w-full rounded-xl border border-amber-200 bg-white px-4 py-3 font-mono text-xs text-gray-800 dark:border-amber-800 dark:bg-gray-900 dark:text-gray-100">
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('plainApiToken').value)" class="rounded-xl bg-amber-600 px-4 py-3 text-sm font-bold text-white hover:bg-amber-700">
                        Copy
                    </button>
                </div>
            </div>
        @endif

        <div class="mb-8 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 p-5 dark:border-gray-700">
                <h2 class="font-bold text-gray-800 dark:text-white">API Tokens</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Abilities</th>
                            <th class="px-5 py-3">Last Used</th>
                            <th class="px-5 py-3">Expires</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($tokens as $token)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-gray-800 dark:text-white">{{ $token->name }}</td>
                                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">
                                    @if(in_array('*', $token->abilities ?? [], true))
                                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700 dark:bg-red-950 dark:text-red-300">Full Access</span>
                                    @else
                                        <div class="flex max-w-xl flex-wrap gap-1.5">
                                            @foreach(($token->abilities ?? []) as $ability)
                                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $ability }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ optional($token->last_used_at)->format('Y-m-d H:i') ?? 'Never' }}</td>
                                <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ optional($token->expires_at)->format('Y-m-d H:i') ?? 'Never' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <form method="POST" action="{{ route('admin.api-tokens.destroy', $token) }}" onsubmit="return confirm('Revoke this API token?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900">Revoke</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">No API tokens yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">
                {{ $tokens->links() }}
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 p-5 dark:border-gray-700">
                <h2 class="font-bold text-gray-800 dark:text-white">Recent API Logs</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">Time</th>
                            <th class="px-5 py-3">Token</th>
                            <th class="px-5 py-3">Method</th>
                            <th class="px-5 py-3">Endpoint</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-5 py-4 text-gray-700 dark:text-gray-200">{{ $log->token_name ?? 'Unknown' }}</td>
                                <td class="px-5 py-4 font-mono text-xs font-bold text-gray-700 dark:text-gray-200">{{ $log->method }}</td>
                                <td class="px-5 py-4 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $log->endpoint }}</td>
                                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $log->status_code }}</td>
                                <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">No API logs yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
