<div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950">
    <div>
        <p class="font-semibold text-gray-900 dark:text-white">Let students check themselves in</p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">QR check-in opens 15 minutes before class and closes at the end time.</p>
    </div>
    <a href="{{ route('attendance.qr.show', [$kind, $session->id]) }}"
       class="inline-flex items-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
        Show attendance QR
    </a>
</div>
