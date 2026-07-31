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

const explainSubscriptionEndDate = () => {
    const classType = document.getElementById('class_type');
    const untilDate = document.getElementById('until_date');
    const summary = document.getElementById('setupSummary');
    const subscriptionPanel = document.getElementById('subscriptionPanel');

    if (!classType || !untilDate || !summary || !subscriptionPanel) return;

    const notice = subscriptionPanel.querySelector('.rounded-xl.border.border-amber-200');

    const refresh = () => {
        if (notice) {
            notice.innerHTML = '<strong>Automatic cancellation:</strong> For subscription classes, Stripe billing will stop automatically at the end of the selected session end date.';
        }

        if (classType.value !== 'subscription') return;

        const endDate = untilDate.value || 'the selected end date';
        window.setTimeout(() => {
            const current = summary.textContent || '';
            summary.textContent = current.replace(
                /Stripe billing continues until the subscription is cancelled\.?/,
                `Stripe billing will cancel automatically at the end of ${endDate}.`
            );
        }, 0);
    };

    [classType, untilDate, document.getElementById('billing_interval'), document.getElementById('price'), document.getElementById('recurrence_frequency')]
        .filter(Boolean)
        .forEach((element) => {
            element.addEventListener('change', refresh);
            element.addEventListener('input', refresh);
        });

    window.setTimeout(refresh, 0);
};

const renderDocumentationLink = () => {
    if (window.location.pathname.startsWith('/docs') || document.getElementById('mueble-docs-link')) return;

    const link = document.createElement('a');
    link.id = 'mueble-docs-link';
    link.href = '/docs';
    link.setAttribute('aria-label', 'Open Mueble LMS documentation');
    link.title = 'Help & Documentation';
    link.className = 'fixed bottom-5 right-5 z-40 inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-extrabold text-white shadow-2xl transition hover:-translate-y-0.5 hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-200';
    link.innerHTML = '<i class="bx bx-book-open text-xl"></i><span class="hidden sm:inline">Docs</span>';
    document.body.appendChild(link);
};

const renderMobileLogout = () => {
    if (document.getElementById('mueble-mobile-logout')) return;

    const mobileHeader = document.querySelector('header.md\\:hidden');
    const controls = mobileHeader?.querySelector('.flex.items-center.gap-1\\.5');
    if (!controls) return;

    const logout = document.createElement('a');
    logout.id = 'mueble-mobile-logout';
    logout.href = '/logout';
    logout.setAttribute('aria-label', 'Log out');
    logout.title = 'Log out';
    logout.className = 'rounded-xl p-2 text-red-600 transition hover:bg-red-50 hover:text-red-700 dark:text-red-300 dark:hover:bg-red-950/30';
    logout.innerHTML = '<i class="bx bx-log-out text-xl"></i>';
    controls.appendChild(logout);
};

const renderMarketingLogo = () => {
    if (!document.body.classList.contains('marketing-page')) return;

    document.querySelectorAll('.brand-mark').forEach((mark) => {
        const logo = document.createElement('img');
        logo.src = '/images/mueble-logo.svg';
        logo.alt = 'Mueble';
        logo.width = 300;
        logo.height = 300;
        logo.style.width = '44px';
        logo.style.height = '44px';
        logo.style.display = 'block';
        logo.style.objectFit = 'contain';
        logo.style.borderRadius = '0';
        logo.style.background = 'transparent';
        logo.style.boxShadow = 'none';
        mark.replaceWith(logo);
    });
};

const renderClientSso = async () => {
    if (!['/login', '/register'].includes(window.location.pathname) || document.getElementById('client-sso-options')) return;

    const form = document.querySelector('form[action$="/login"], form[action$="/register"]');
    if (!form) return;

    try {
        const response = await fetch('/auth/sso/providers', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!response.ok) return;

        const data = await response.json();
        if (!Array.isArray(data.providers) || !data.providers.length) return;

        const wrapper = document.createElement('div');
        wrapper.id = 'client-sso-options';
        wrapper.className = 'mb-6 space-y-3';

        const icons = {
            google: '<span class="text-lg font-black text-blue-600">G</span>',
            microsoft: '<span class="grid grid-cols-2 gap-0.5"><i class="h-2 w-2 bg-red-500"></i><i class="h-2 w-2 bg-green-500"></i><i class="h-2 w-2 bg-blue-500"></i><i class="h-2 w-2 bg-yellow-500"></i></span>',
            apple: '<span class="text-xl leading-none">●</span>',
        };

        wrapper.innerHTML = data.providers.map((provider) => `
            <a href="${provider.url}" class="flex w-full items-center justify-center gap-3 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-800 shadow-sm transition hover:border-blue-400 hover:bg-slate-50 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:hover:border-blue-500">
                ${icons[provider.id] || ''}<span>${provider.label}</span>
            </a>`).join('') + `
            <div class="flex items-center gap-4 py-1">
                <hr class="flex-grow border-slate-200 dark:border-gray-700"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">or use email</span><hr class="flex-grow border-slate-200 dark:border-gray-700">
            </div>`;

        form.parentNode.insertBefore(wrapper, form);
    } catch (_) {
        // Password authentication remains available when provider discovery fails.
    }
};

document.addEventListener('DOMContentLoaded', renderSeatPromotion);
document.addEventListener('DOMContentLoaded', explainSubscriptionEndDate);
document.addEventListener('DOMContentLoaded', renderDocumentationLink);
document.addEventListener('DOMContentLoaded', renderMobileLogout);
document.addEventListener('DOMContentLoaded', renderMarketingLogo);
document.addEventListener('DOMContentLoaded', renderClientSso);
