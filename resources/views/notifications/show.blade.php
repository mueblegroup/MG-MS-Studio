<x-app-layout>
    <div class="mg-page">
        <div class="mg-page-inner max-w-4xl">
            <div>
                <a href="{{ route('notifications.index') }}" class="mg-btn-secondary">
                    Back to notifications
                </a>
            </div>

            <div class="mg-card p-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="mg-badge">{{ str_replace('_', ' ', ucfirst($notification->type)) }}</span>
                    @if($notification->read_at)
                        <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-bold text-green-700">Read</span>
                    @endif
                </div>

                <h1 class="mt-4 text-2xl font-extrabold text-[#171717] dark:text-white">{{ $notification->title }}</h1>
                <p class="mt-2 text-xs font-semibold text-[#9a8c7d] dark:text-gray-500">
                    {{ $notification->created_at->format('d M Y, h:i A') }}
                </p>

                <div class="mt-6 whitespace-pre-line rounded-2xl bg-[#fffaf3] p-5 text-sm leading-7 text-[#31261d] dark:bg-gray-800 dark:text-gray-200">{{ $notification->message }}</div>

                @if($notification->action_url)
                    <div class="mt-6 rounded-2xl border border-[#eadfce] bg-white p-4 text-sm text-[#6b5f52] dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                        Related page: {{ $notification->action_url }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
