@extends('saas.layouts.marketing', ['title' => 'How It Works — Mueble Studio'])
@section('content')
<main class="wrap">
    <header class="page-head">
        <div class="kicker">How the platform works</div>
        <h1>From studio creation to daily operations.</h1>
        <p class="section-copy">Mueble Studio is organised around a clear flow so an institute can launch, configure services, enrol users, collect payments and run attendance without rebuilding its process every day.</p>
    </header>

    <section class="section">
        <div class="steps">
            <div class="step"><div class="step-no">01</div><div><h3>Create a dedicated studio</h3><p>Select a platform plan, choose a studio name and subdomain, configure timezone and currency, and activate a separate tenant workspace.</p></div></div>
            <div class="step"><div class="step-no">02</div><div><h3>Configure the organisation</h3><p>Add administrators, teachers and students. Set studio details, payment gateway defaults, mail delivery and registration preferences.</p></div></div>
            <div class="step"><div class="step-no">03</div><div><h3>Create what you sell</h3><p>Set up classes, plans, sessions, subscriptions and class cards according to the way the institute charges for access.</p></div></div>
            <div class="step"><div class="step-no">04</div><div><h3>Publish and collect payment</h3><p>Students can browse eligible offerings, add them to cart, check out through supported gateways and receive a payment record.</p></div></div>
            <div class="step"><div class="step-no">05</div><div><h3>Generate access and schedules</h3><p>Successful purchases connect students to the correct class, plan, subscription or class-card entitlement.</p></div></div>
            <div class="step"><div class="step-no">06</div><div><h3>Run classes and attendance</h3><p>Teachers use focused schedules and attendance tools while the system keeps usage and eligibility connected.</p></div></div>
            <div class="step"><div class="step-no">07</div><div><h3>Handle renewals and exceptions</h3><p>Recurring Stripe subscriptions, upcoming billing dates, failed payments, cancellation and access changes are reflected in the platform.</p></div></div>
            <div class="step"><div class="step-no">08</div><div><h3>Review operations</h3><p>Admins can review users, schedules, payments, attendance, subscriptions and notifications from role-based dashboards.</p></div></div>
        </div>
    </section>

    <section class="section">
        <div class="grid-2">
            <div class="card"><div class="kicker">Tenant architecture</div><h3>Each studio has its own workspace.</h3><p>Studio-specific settings, users and operational data are resolved through the active tenant instead of being treated as one global organisation.</p></div>
            <div class="card"><div class="kicker">Connected billing</div><h3>Payments are part of access control.</h3><p>The system does more than store a transaction. Checkout, subscription state, session access and cancellation behaviour are designed to remain connected.</p></div>
        </div>
    </section>
</main>
@endsection
