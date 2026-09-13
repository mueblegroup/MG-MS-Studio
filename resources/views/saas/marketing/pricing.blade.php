@extends('saas.layouts.marketing', ['title' => 'Pricing — ClassM8'])
@section('content')
<main class="wrap">
    <header class="page-head">
        <div class="kicker">Simple platform pricing</div>
        <h1>Choose the ClassM8 plan that fits where your academy is today.</h1>
        <p class="section-copy">Every plan gives you your own ClassM8 workspace for managing students, teachers, classes, plans, class cards, attendance and payments. Start with what you need and grow from there.</p>
    </header>

    <section class="section">
        @if($plans->isEmpty())
            <div class="notice">Public subscription plans are being prepared. Please contact us for current pricing and onboarding options.</div>
        @else
            <div class="pricing">
                @foreach($plans as $index => $plan)
                    <article class="price-card {{ $index === 1 ? 'featured' : '' }}">
                        @if($index === 1)<div class="pill" style="display:inline-flex;margin-bottom:14px;color:#6d28d9">Popular choice</div>@endif
                        <h3>{{ $plan->name }}</h3>
                        <p style="color:#667085;line-height:1.65">{{ $plan->description ?: 'A complete ClassM8 workspace for running your academy or studio.' }}</p>
                        <div class="price">{{ strtoupper($plan->currency ?: 'MYR') }} {{ number_format((float) $plan->price, 2) }} <small>/ {{ strtolower($plan->billing_interval ?: 'month') }}</small></div>
                        @if((int) $plan->trial_days > 0)
                            <div class="pill" style="display:inline-flex;margin-bottom:16px">{{ (int) $plan->trial_days }}-day trial available</div>
                        @endif
                        <ul class="check-list">
                            <li>Your own dedicated studio workspace</li>
                            <li>Admin, teacher and student portals</li>
                            <li>Individual classes, plans and class cards</li>
                            <li>Schedules and attendance tracking</li>
                            <li>Online checkout and payment records</li>
                        </ul>
                        <div class="actions"><a class="btn btn-primary" href="{{ route('register') }}">Choose {{ $plan->name }}</a></div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="section">
        <div class="grid-3">
            <article class="card"><div class="card-icon">1</div><h3>Launch your workspace</h3><p>Create your ClassM8 studio and set up the organisation details your team will use every day.</p></article>
            <article class="card"><div class="card-icon">2</div><h3>Add what you sell</h3><p>Create your individual classes, recurring plans and flexible class-card products.</p></article>
            <article class="card"><div class="card-icon">3</div><h3>Bring in your students</h3><p>Start selling online and give students a clear portal for schedules, payments and class access.</p></article>
        </div>
    </section>

    <section class="section">
        <div class="section-head"><div class="kicker">Frequently asked questions</div><h2>Questions before you get started?</h2></div>
        <div class="faq">
            <details><summary>Does my academy get its own workspace?</summary><p>Yes. Each organisation gets its own ClassM8 studio workspace with its own users, settings and day-to-day operations.</p></details>
            <details><summary>Can I sell more than one type of class product?</summary><p>Yes. You can use individual classes for one-off bookings, plans for structured or recurring programmes, and class cards for flexible session-credit packages.</p></details>
            <details><summary>Can students pay online?</summary><p>ClassM8 supports online checkout using configured payment gateways such as Stripe and HitPay, depending on your account and regional availability.</p></details>
            <details><summary>What can students see after they purchase?</summary><p>The student portal can show upcoming class details, subscriptions, class-card balances, attendance information, payment history and receipts.</p></details>
            <details><summary>Is ClassM8 only for schools?</summary><p>No. It is built for tuition centres, language academies, dance and music studios, fitness businesses, sports academies and other organisations that run scheduled classes or memberships.</p></details>
        </div>
    </section>
</main>
@endsection