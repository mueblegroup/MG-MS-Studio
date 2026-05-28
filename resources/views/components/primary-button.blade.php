<button {{ $attributes->merge(['type' => 'submit', 'class' => 'mg-btn-primary uppercase tracking-widest']) }}>
    {{ $slot }}
</button>
