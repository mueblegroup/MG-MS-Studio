@php
    $timezoneFieldHtml = view('admin.settings.partials.timezone-field', [
        'data' => $data,
        'timezoneOptions' => $timezoneOptions,
    ])->render();
@endphp

@include('admin.settings.studio')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('studio-settings-form');
    if (form && !form.querySelector('[name="timezone"]')) {
        const currencyInput = form.querySelector('[name="currency"]');
        if (currencyInput) {
            const currencyWrapper = currencyInput.closest('div');
            if (currencyWrapper && currencyWrapper.parentElement) {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = @js($timezoneFieldHtml);
                if (wrapper.firstElementChild) {
                    currencyWrapper.parentElement.insertBefore(wrapper.firstElementChild, currencyWrapper.nextSibling);
                }
            }
        }
    }

    const initializeStudioClock = () => {
        const select = document.getElementById('studio-timezone-select');
        const clock = document.getElementById('studio-current-time');
        const zoneLabel = document.getElementById('studio-current-timezone');

        if (!select || !clock || !zoneLabel) {
            return;
        }

        const render = () => {
            const timezone = select.value || 'Asia/Kuala_Lumpur';

            try {
                clock.textContent = new Intl.DateTimeFormat(undefined, {
                    timeZone: timezone,
                    weekday: 'short',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                }).format(new Date());
                zoneLabel.textContent = timezone;
            } catch (error) {
                zoneLabel.textContent = timezone;
            }
        };

        select.addEventListener('change', render);
        render();
        window.setInterval(render, 1000);
    };

    initializeStudioClock();

    const headingRow = document.querySelector('.mb-6.flex.items-center.justify-between');
    if (headingRow && !document.getElementById('payment-gateway-settings-link')) {
        const link = document.createElement('a');
        link.id = 'payment-gateway-settings-link';
        link.href = @js(route('settings.payment-gateways.index'));
        link.className = 'inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow transition hover:bg-indigo-700';
        link.innerHTML = '<i class="bx bx-credit-card"></i><span>Payment Gateways</span>';
        headingRow.appendChild(link);
    }
});
</script>
