<x-app-layout>
    <div class="min-h-screen bg-gray-50/50 p-4 dark:bg-gray-900 sm:p-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">API Documentation</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Use Bearer tokens created from the API Management page.</p>
            </div>
            <a href="{{ route('admin.api-tokens.index') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Back to Tokens</a>
        </div>

        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-3 font-bold text-gray-800 dark:text-white">Base URL</h2>
            <code class="block rounded-xl bg-gray-900 p-4 text-sm text-gray-100">{{ $baseUrl }}</code>
        </div>

        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-3 font-bold text-gray-800 dark:text-white">Authentication</h2>
            <pre class="overflow-x-auto rounded-xl bg-gray-900 p-4 text-sm text-gray-100"><code>Authorization: Bearer YOUR_API_TOKEN
Accept: application/json
Content-Type: application/json</code></pre>
        </div>

        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-3 font-bold text-gray-800 dark:text-white">Example cURL</h2>
            <pre class="overflow-x-auto rounded-xl bg-gray-900 p-4 text-sm text-gray-100"><code>curl -X GET "{{ $baseUrl }}/students" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 font-bold text-gray-800 dark:text-white">Core Endpoints</h2>
                <div class="space-y-3 text-sm">
                    @php
                        $endpoints = [
                            'GET /me',
                            'GET /reports/dashboard',
                            'GET|POST /users',
                            'GET|PUT|DELETE /users/{id}',
                            'GET|POST /students',
                            'GET|POST /teachers',
                            'GET|POST /classes',
                            'GET|PUT|DELETE /classes/{id}',
                            'GET|POST /classes/{id}/sessions',
                            'PUT|DELETE /class-sessions/{id}',
                            'GET|POST /plans',
                            'GET|PUT|DELETE /plans/{id}',
                            'GET|POST /plans/{id}/sessions',
                            'PUT|DELETE /plan-sessions/{id}',
                            'GET|POST /classcards',
                            'GET|PUT|DELETE /classcards/{id}',
                            'GET|POST /classcard-purchases',
                            'GET|PUT|DELETE /classcard-purchases/{id}',
                            'GET /attendance/class-assignments',
                            'GET /attendance/classcards',
                            'POST /attendance/classcards/{userClassCardId}/mark',
                            'GET /payments',
                            'GET /orders',
                            'GET /shop',
                            'GET|POST /notifications',
                            'GET|PUT|DELETE /notifications/{id}',
                            'GET|PUT /settings/studio',
                            'GET /api-logs',
                        ];
                    @endphp

                    @foreach($endpoints as $endpoint)
                        <div class="rounded-xl bg-gray-50 px-3 py-2 font-mono text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ $endpoint }}</div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 font-bold text-gray-800 dark:text-white">Permission Abilities</h2>
                <div class="space-y-4">
                    @foreach($abilityGroups as $group => $abilities)
                        <div>
                            <h3 class="mb-2 text-sm font-extrabold text-gray-800 dark:text-white">{{ $group }}</h3>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($abilities as $ability => $label)
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200" title="{{ $label }}">{{ $ability }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
