<x-app-layout>
    <div class="space-y-6">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Platform Configuration</p>
            <h1 class="mt-2 text-3xl font-black text-[#171717] dark:text-white">Settings</h1>
            <p class="mt-2 max-w-3xl text-sm font-medium leading-6 text-[#6b5f52] dark:text-gray-400">Manage platform-wide authentication, verification, integrations and security settings from one place.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <a href="{{ route('superadmin.sso.index') }}" class="group rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#d97706] dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff3df] text-[#d97706] dark:bg-amber-950/30"><i class="bx bx-log-in-circle text-2xl"></i></div>
                    <span class="rounded-full bg-[#f7f2ea] px-3 py-1 text-xs font-black text-[#6b5f52] dark:bg-gray-950 dark:text-gray-300">{{ $enabledSsoCount }}/3 enabled</span>
                </div>
                <h2 class="mt-5 text-xl font-black text-[#171717] dark:text-white">Client Portal SSO</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-[#6b5f52] dark:text-gray-400">Configure Google, Microsoft and Apple login for client owners on the central portal.</p>
                <span class="mt-5 inline-flex items-center gap-2 text-sm font-black text-[#d97706]">Manage SSO <i class="bx bx-right-arrow-alt"></i></span>
            </a>

            <div class="rounded-3xl border border-dashed border-[#d8c8b2] bg-[#fffaf3] p-6 dark:border-gray-700 dark:bg-gray-900/60">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#9a8c7d] shadow-sm dark:bg-gray-800"><i class="bx bx-mobile-alt text-2xl"></i></div>
                <h2 class="mt-5 text-xl font-black text-[#171717] dark:text-white">Phone Verification</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-[#6b5f52] dark:text-gray-400">WhatsApp and SMS verification provider settings will appear here in the next phase.</p>
                <span class="mt-5 inline-flex rounded-full bg-white px-3 py-1 text-xs font-black text-[#9a8c7d] dark:bg-gray-800">Planned</span>
            </div>

            <div class="rounded-3xl border border-dashed border-[#d8c8b2] bg-[#fffaf3] p-6 dark:border-gray-700 dark:bg-gray-900/60">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#9a8c7d] shadow-sm dark:bg-gray-800"><i class="bx bx-shield-quarter text-2xl"></i></div>
                <h2 class="mt-5 text-xl font-black text-[#171717] dark:text-white">Security Policies</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-[#6b5f52] dark:text-gray-400">Future controls for mandatory 2FA, session policies and client verification requirements.</p>
                <span class="mt-5 inline-flex rounded-full bg-white px-3 py-1 text-xs font-black text-[#9a8c7d] dark:bg-gray-800">Planned</span>
            </div>
        </div>
    </div>
</x-app-layout>
