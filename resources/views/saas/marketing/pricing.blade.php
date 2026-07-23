@extends('saas.layouts.marketing', ['title' => 'Pricing — Mueble Studio'])
@section('content')
<main class="wrap">
    <header class="page-head">
        <div class="kicker">Platform plans</div>
        <h1>Choose a plan that matches your institute’s stage.</h1>
        <p class="section-copy">Every plan gives you a dedicated studio workspace. Available plans and limits are loaded from the current platform configuration.</p>
    </header>

    <section class="section">
        @if($plans->isEmpty())
            <div class="notice">No public subscription plans are currently available. Please contact the platform administrator.</div>
        @else
            <div class="pricing">
                @foreach($plans as $index => $plan)
                    <article class="price-card {{ $index === 1 ? 'featured' : '' }}">
                        @if($index === 1)<div class="pill" style="display:inline-flex;margin-bottom:14px;color:#6d28d9">Popular choice</div>@endif
                        <h3>{{ $plan->name }}</h3>
                        <p style="color:#667085;line-height:1.65">{{ $plan->description ?: 'A structured platform plan for managing your studio operations.' }}</p>
                        <div class="price">{{ strtoupper($plan->currency ?: 'MYR') }} {{ number_format((float) $plan->price, 2) }} <small>/ {{ strtolower($plan->billing_interval ?: 'month') }}</small></div>
                        @if((int) $plan->trial_days > 0)
                            <div class="pill" style="display:inline-flex;margin-bottom:16px">{{ (int) $plan->trial_days }}-day trial</div>
                        @endif
                        <ul class="check-list">
                            <li>Dedicated studio subdomain</li>
                            <li>Admin, teacher and student portals</li>
                            <li>Classes, plans and class cards</li>
                            <li>Attendance and payment tracking</li>
                            <li>Studio-specific timezone and currency</li>
                        </ul>
                        <div class="actions"><a class="btn btn-primary" href="{{ route('register') }}">Choose {{ $plan->name }}</a></div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="section">
        <div class="section-head"><div class="kicker">Frequently asked questions</div><h2>Before you get started.</h2></div>
        <div class="faq">
            <details><summary>Does each institute get a separate workspace?</summary><p>Yes. Each studio is created with its own tenant identity and subdomain, with studio-specific users and settings.</p></details>
            <details><summary>Can the studio collect student payments?</summary><p>The system supports checkout and payment records through configured gateways such as Stripe and HitPay. Gateway availability depends on account and regional support.</p></details>
            <details><summary>Can plans include a trial?</summary><p>Yes. Trial duration is configured per platform subscription plan. Plans with zero trial days charge and activate according to the normal Stripe subscription flow.</p></details>
            <details><summary>Can a studio upgrade later?</summary><p>Higher-priced plan upgrades can be processed through the customer billing portal with Stripe proration and payment confirmation.</p></details>
            <details><summary>Is this only for schools?</summary><p>No. It is suitable for tuition centres, academies, dance and music studios, fitness businesses, training providers and other scheduled membership organisations.</p></details>
        </div>
    </section>
</main>
@endsection
