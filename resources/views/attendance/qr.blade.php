<x-app-layout>
    <div class="mx-auto max-w-5xl p-4 sm:p-8" data-attendance-qr
         data-status-url="{{ route('attendance.qr.show', [$kind, $session->id]) }}">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Student self check-in</p>
                <h1 class="mt-1 text-2xl font-bold">{{ $kind === 'class' ? $session->classModel->name : $session->plan->name }}</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    {{ $session->start_time?->format('d M Y, H:i') }} – {{ $session->end_time?->format('H:i') }}
                    · {{ config('app.studio_timezone', config('app.timezone')) }}
                </p>
            </div>
            <a href="{{ $backUrl }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold dark:border-gray-600">Back to attendance</a>
        </div>
        <div class="grid gap-6 md:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-bold">Scan to mark your attendance</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Use your phone camera, sign in to your student account, then confirm.</p>
                <div class="my-5 flex justify-center" data-qr-container hidden>
                    <canvas data-qr-canvas aria-label="Attendance check-in QR code" style="max-width:100%;height:auto"></canvas>
                </div>
                <p class="my-5 text-sm font-semibold" data-qr-message role="status">{{ $data['message'] }}</p>
                <form method="POST" action="{{ route('attendance.qr.open', [$kind, $session->id]) }}" class="mt-4">
                    @csrf
                    <button data-qr-control @disabled(! $data['can_open']) class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50">Open QR check-in</button>
                </form>
                <form method="POST" action="{{ route('attendance.qr.open', [$kind, $session->id]) }}" class="mt-3"
                      onsubmit="return confirm('Replace this QR code? Previously shared codes and open check-in pages will stop working.')">
                    @csrf
                    <input type="hidden" name="replace" value="1">
                    <button data-qr-control @disabled(! $data['can_open']) class="text-sm font-semibold text-gray-600 underline disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-300">Replace QR code</button>
                </form>
                <p class="mt-5 text-xs text-gray-500 dark:text-gray-400">Display this code in class. Only this studio’s admins and the assigned teacher can open it.</p>
                <noscript><p class="mt-4 text-red-700">Enable JavaScript to display the QR code. Manual attendance remains available.</p></noscript>
            </section>
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-bold">Checked in <span data-attendee-count>{{ count($data['attendees']) }}</span></h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Updates every 10 seconds while this page is open.</p>
                <ul data-attendees class="mt-5 space-y-3" aria-live="polite">
                    @foreach($data['attendees'] as $attendee)
                        <li class="flex justify-between gap-3 text-sm"><span>{{ $attendee['name'] }}</span><span>{{ $attendee['time'] }}</span></li>
                    @endforeach
                </ul>
                <p class="mt-6 text-xs text-gray-500 dark:text-gray-400">For corrections or students without a phone, use the attendance page.</p>
            </section>
        </div>
    </div>
</x-app-layout>
