import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const renderSeatPromotion = async () => {
    if (!window.location.pathname.startsWith('/admin')) return;

    try {
        const response = await fetch('/admin/subscription/seat-usage', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });

        if (!response.ok) return;

        const data = await response.json();
        const limited = Object.entries(data.usage || {}).filter(([, seat]) => !seat.unlimited);
        if (!limited.length) return;

        const pressure = limited.some(([, seat]) => seat.full || seat.percentage >= 80);
        const summary = limited
            .map(([role, seat]) => `${role.charAt(0).toUpperCase() + role.slice(1)}s ${seat.used}/${seat.limit}`)
            .join(' · ');

        const banner = document.createElement('div');
        banner.className = `mx-4 mt-4 rounded-2xl border p-4 shadow-sm sm:mx-6 lg:mx-8 ${pressure ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50'}`;
        banner.innerHTML = `
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-extrabold text-gray-900">${pressure ? 'Your studio is running out of seats' : `Current plan: ${data.plan}`}</div>
                    <div class="mt-1 text-xs font-semibold text-gray-600">${summary}. Upgrade now to add more students, teachers and staff.</div>
                </div>
                <a href="${data.upgrade_url}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-amber-600 px-4 py-2 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-700">View upgrade options</a>
            </div>`;

        const main = document.querySelector('main');
        if (main) main.prepend(banner);
    } catch (_) {
        // Seat promotion must never interrupt normal studio operation.
    }
};

document.addEventListener('DOMContentLoaded', renderSeatPromotion);
