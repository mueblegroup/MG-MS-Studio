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
    if (!form || form.querySelector('[name="timezone"]')) return;

    const currencyInput = form.querySelector('[name="currency"]');
    if (!currencyInput) return;

    const currencyWrapper = currencyInput.closest('div');
    if (!currencyWrapper || !currencyWrapper.parentElement) return;

    const wrapper = document.createElement('div');
    wrapper.innerHTML = @js($timezoneFieldHtml);

    if (wrapper.firstElementChild) {
        currencyWrapper.parentElement.insertBefore(wrapper.firstElementChild, currencyWrapper.nextSibling);
    }
});
</script>
