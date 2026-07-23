@extends('saas.layouts.marketing', ['title' => 'Security & Reliability — Mueble Studio'])
@section('content')
<main class="wrap">
    <header class="page-head">
        <div class="kicker">Security and reliability</div>
        <h1>Designed for institutes that need dependable daily operations.</h1>
        <p class="section-copy">Larger organisations need more than attractive screens. They need controlled access, tenant separation, reliable payment reconciliation, traceable actions and operational safeguards.</p>
    </header>

    <section class="section">
        <div class="grid-3">
            <article class="card"><div class="card-icon">R</div><h3>Role-based access</h3><p>Administrators, teachers, students and superadministrators receive different routes, navigation and capabilities.</p></article>
            <article class="card"><div class="card-icon">T</div><h3>Tenant-aware data</h3><p>Each studio operates within its own tenant context so settings and operational records are not treated as global data.</p></article>
            <article class="card"><div class="card-icon">W</div><h3>Verified webhooks</h3><p>Stripe events are validated using signing secrets before subscription or payment state is updated.</p></article>
            <article class="card"><div class="card-icon">A</div><h3>Action auditing</h3><p>Authenticated actions can be logged to improve accountability and troubleshooting.</p></article>
            <article class="card"><div class="card-icon">C</div><h3>Controlled access state</h3><p>Subscription, payment and cancellation states are connected to future class access instead of relying only on manual updates.</p></article>
            <article class="card"><div class="card-icon">S</div><h3>Studio-level settings</h3><p>Timezone, currency, mail and checkout settings are stored per studio to prevent cross-tenant configuration conflicts.</p></article>
        </div>
    </section>

    <section class="section">
        <div class="grid-2">
            <div class="band"><div class="kicker" style="color:#bfdbfe">Operational reliability</div><h2 style="font-size:42px;margin:10px 0 12px">Built to recover from real payment and workflow conditions.</h2><p>The platform tracks pending, paid, failed, cancelled and recurring states rather than assuming every checkout succeeds immediately. Webhook processing and local reconciliation help keep Stripe and application state aligned.</p></div>
            <div class="card"><h3>Production-focused capabilities</h3><ul><li>Separate platform and student-payment webhook handling</li><li>Configuration validation for required integrations</li><li>Queue restart and cached configuration support</li><li>Timezone-aware date presentation</li><li>Manual suspension safeguards</li><li>Payment history and receipt records</li></ul></div>
        </div>
    </section>

    <section class="section">
        <div class="notice"><strong>Important:</strong> final production reliability also depends on correct server configuration, backups, monitoring, HTTPS, database maintenance, queue workers and keeping Laravel dependencies updated.</div>
    </section>
</main>
@endsection
