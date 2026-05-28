<button {{ $attributes->merge(['type' => 'submit', 'class' => 'mg-btn-danger uppercase tracking-widest']) }}>
    {{ $slot }}
</button>
