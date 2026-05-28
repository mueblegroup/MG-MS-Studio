<x-app-layout>
    <div class="mg-page">
        <div class="mg-page-inner">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="mg-title">Notifications</h1>
                    <p class="mg-subtitle mt-1">Send personalised in-app notifications to one user, multiple users, or everyone.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700">
                    <ul class="list-inside list-disc">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
                <div class="xl:col-span-5">
                    <div class="mg-card p-5">
                        <h2 class="text-lg font-bold text-[#171717] dark:text-white">Send notification</h2>
                        <p class="mg-subtitle mt-1">The notification will appear in the user's notification center with read/unread status.</p>

                        <form method="POST" action="{{ route('admin.notifications.store') }}" class="mt-5 space-y-4">
                            @csrf

                            <div>
                                <label class="block text-sm font-bold text-[#31261d] dark:text-gray-200">Recipients</label>
                                <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-[#eadfce] bg-[#fffaf3] p-3 text-sm font-semibold text-[#31261d] dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                        <input type="radio" name="recipient_type" value="all" class="text-[#d97706] focus:ring-[#d97706]" {{ old('recipient_type', 'all') === 'all' ? 'checked' : '' }}>
                                        Send to all users
                                    </label>

                                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-[#eadfce] bg-[#fffaf3] p-3 text-sm font-semibold text-[#31261d] dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                        <input type="radio" name="recipient_type" value="specific" class="text-[#d97706] focus:ring-[#d97706]" {{ old('recipient_type') === 'specific' ? 'checked' : '' }}>
                                        Specific users
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label for="user_ids" class="block text-sm font-bold text-[#31261d] dark:text-gray-200">Choose users</label>
                                <select id="user_ids" name="user_ids[]" multiple class="mg-input mt-2 min-h-44">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(in_array($user->id, old('user_ids', [])))>
                                            {{ $user->name }} — {{ $user->email }} ({{ ucfirst($user->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-[#6b5f52] dark:text-gray-400">Hold CTRL/CMD to select multiple users. Only used when “Specific users” is selected.</p>
                            </div>

                            <div>
                                <label for="title" class="block text-sm font-bold text-[#31261d] dark:text-gray-200">Title</label>
                                <input id="title" name="title" value="{{ old('title') }}" class="mg-input mt-2" placeholder="Example: Payment reminder">
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-bold text-[#31261d] dark:text-gray-200">Type</label>
                                <select id="type" name="type" class="mg-select mt-2 w-full">
                                    @foreach(['general' => 'General', 'payment_due' => 'Payment Due', 'payment_success' => 'Payment Success', 'class' => 'Class', 'plan' => 'Plan', 'class_card' => 'Class Card', 'system' => 'System'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('type', 'general') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-bold text-[#31261d] dark:text-gray-200">Message</label>
                                <textarea id="message" name="message" rows="6" class="mg-input mt-2" placeholder="Write the notification message...">{{ old('message') }}</textarea>
                            </div>

                            <div>
                                <label for="action_url" class="block text-sm font-bold text-[#31261d] dark:text-gray-200">Action URL <span class="text-xs font-medium text-[#6b5f52]">optional</span></label>
                                <input id="action_url" name="action_url" value="{{ old('action_url') }}" class="mg-input mt-2" placeholder="/student/payments or full URL">
                            </div>

                            <button type="submit" class="mg-btn-primary w-full">
                                <i class="bx bx-send"></i>
                                Send Notification
                            </button>
                        </form>
                    </div>
                </div>

                <div class="xl:col-span-7">
                    <div class="mg-card overflow-hidden">
                        <div class="border-b border-[#eadfce] px-5 py-4 dark:border-gray-800">
                            <h2 class="text-lg font-bold text-[#171717] dark:text-white">Recent sent notifications</h2>
                        </div>

                        <div class="divide-y divide-[#f0e5d4] dark:divide-gray-800">
                            @forelse($notifications as $notification)
                                <div class="p-5">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="mg-badge">{{ str_replace('_', ' ', ucfirst($notification->type)) }}</span>
                                                @if(!$notification->read_at)
                                                    <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-bold text-red-700">Unread</span>
                                                @else
                                                    <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-bold text-green-700">Read</span>
                                                @endif
                                            </div>
                                            <h3 class="mt-2 font-bold text-[#171717] dark:text-white">{{ $notification->title }}</h3>
                                            <p class="mt-1 line-clamp-2 text-sm text-[#6b5f52] dark:text-gray-400">{{ $notification->message }}</p>
                                            <p class="mt-2 text-xs text-[#9a8c7d] dark:text-gray-500">
                                                To {{ $notification->user?->name ?? 'Deleted user' }} • {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-10 text-center text-sm text-[#6b5f52] dark:text-gray-400">No notifications sent yet.</div>
                            @endforelse
                        </div>

                        <div class="border-t border-[#eadfce] p-4 dark:border-gray-800">
                            {{ $notifications->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
