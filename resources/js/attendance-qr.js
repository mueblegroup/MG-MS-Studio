import QRCode from 'qrcode';

// QR data stays inside the studio portal; no third-party image/QR service receives it.
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-attendance-qr]');
    if (!root) return;
    const canvas = root.querySelector('[data-qr-canvas]');
    const container = root.querySelector('[data-qr-container]');
    const message = root.querySelector('[data-qr-message]');
    let expiryTimer;
    let currentUrl;

    const hide = (text) => {
        container.hidden = true;
        container.style.display = 'none';
        currentUrl = null;
        message.textContent = text;
        root.querySelectorAll('[data-qr-control]').forEach((button) => { button.disabled = true; });
    };

    const refresh = async () => {
        const started = performance.now();
        try {
            const response = await fetch(root.dataset.statusUrl, {
                headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store',
                signal: AbortSignal.timeout(8000),
            });
            if (!response.ok) throw new Error('Unable to refresh. Reload this page or sign in again.');
            const data = await response.json();
            clearTimeout(expiryTimer);
            if (!data.url) {
                hide(data.message);
            } else {
                if (currentUrl !== data.url) {
                    await QRCode.toCanvas(canvas, data.url, { width: 400, margin: 4, errorCorrectionLevel: 'M' });
                    currentUrl = data.url;
                }
                container.hidden = false;
                container.style.display = 'flex';
                message.textContent = data.message;
                // Use server-relative time, not the teacher's phone/computer clock.
                expiryTimer = setTimeout(() => hide('Check-in is closed. The class has ended.'),
                    Math.max(0, data.remaining * 1000 - (performance.now() - started)));
            }
            root.querySelectorAll('[data-qr-control]').forEach((button) => { button.disabled = !data.can_open; });
            root.querySelector('[data-attendee-count]').textContent = data.attendees.length;
            const list = root.querySelector('[data-attendees]');
            list.replaceChildren(...data.attendees.map((attendee) => {
                const row = document.createElement('li');
                row.className = 'flex justify-between gap-3 text-sm';
                const name = document.createElement('span');
                name.textContent = attendee.name;
                const time = document.createElement('span');
                time.textContent = attendee.time || '';
                row.append(name, time);
                return row;
            }));
        } catch (error) {
            hide('Connection lost or sign-in expired. Reload this page to display the current QR code.');
        }
    };
    refresh();
    setInterval(refresh, 10000);
});
