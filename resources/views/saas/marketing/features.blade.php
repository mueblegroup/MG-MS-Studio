@extends('saas.layouts.marketing', ['title' => 'Features — Mueble Studio'])
@section('content')
<main class="wrap">
    <header class="page-head">
        <div class="kicker">Complete feature set</div>
        <h1>Everything needed to operate a modern institute.</h1>
        <p class="section-copy">Mueble Studio combines academic operations, student self-service, staff workflows and payment management so your team can work from one source of truth.</p>
    </header>

    <section class="section">
        <div class="grid-3">
            <article class="card"><div class="card-icon">01</div><h3>Student records</h3><ul><li>Profiles and contact information</li><li>Active classes and packages</li><li>Attendance and payment history</li><li>Role-aware account access</li></ul></article>
            <article class="card"><div class="card-icon">02</div><h3>Class management</h3><ul><li>One-time and recurring classes</li><li>Teacher and venue assignment</li><li>Capacity and session planning</li><li>Search and class-type filtering</li></ul></article>
            <article class="card"><div class="card-icon">03</div><h3>Plans and packages</h3><ul><li>Structured membership plans</li><li>Scheduled plan sessions</li><li>Validity and access tracking</li><li>Student-plan assignments</li></ul></article>
            <article class="card"><div class="card-icon">04</div><h3>Class cards</h3><ul><li>Session-credit products</li><li>Usage and balance tracking</li><li>Purchase and assignment records</li><li>Attendance-linked deductions</li></ul></article>
            <article class="card"><div class="card-icon">05</div><h3>Online shop</h3><ul><li>Classes grouped clearly for purchase</li><li>Cart and checkout workflows</li><li>Student purchase restrictions</li><li>Payment status reconciliation</li></ul></article>
            <article class="card"><div class="card-icon">06</div><h3>Recurring subscriptions</h3><ul><li>Stripe subscription checkout</li><li>Renewal-date synchronisation</li><li>Payment failure tracking</li><li>Cancellation and access handling</li></ul></article>
            <article class="card"><div class="card-icon">07</div><h3>Attendance</h3><ul><li>Class attendance</li><li>Plan-session attendance</li><li>Class-card usage</li><li>Teacher-friendly marking workflow</li></ul></article>
            <article class="card"><div class="card-icon">08</div><h3>Teacher portal</h3><ul><li>Assigned classes and plans</li><li>Schedule visibility</li><li>Attendance tools</li><li>Focused role-based navigation</li></ul></article>
            <article class="card"><div class="card-icon">09</div><h3>Student portal</h3><ul><li>Personal dashboard</li><li>Schedule and attendance history</li><li>Subscription management</li><li>Payments and downloadable receipts</li></ul></article>
            <article class="card"><div class="card-icon">10</div><h3>Payments</h3><ul><li>Stripe and HitPay support</li><li>Pending and failed payment states</li><li>Payment history and receipts</li><li>Subscription-cycle records</li></ul></article>
            <article class="card"><div class="card-icon">11</div><h3>Studio configuration</h3><ul><li>Timezone and currency settings</li><li>Payment gateway defaults</li><li>Mail server configuration</li><li>Studio-specific settings isolation</li></ul></article>
            <article class="card"><div class="card-icon">12</div><h3>Notifications and auditability</h3><ul><li>Admin and user notifications</li><li>Platform messages</li><li>Authenticated action auditing</li><li>Clear operational status indicators</li></ul></article>
        </div>
    </section>

    <section class="section">
        <div class="band"><div class="kicker" style="color:#bfdbfe">Connected by design</div><h2 style="font-size:42px;margin:10px 0 12px">Features work together, not as isolated modules.</h2><p>A successful payment updates access. A valid subscription controls future sessions. Attendance reflects the correct class or package. Studio settings apply only to that tenant. This connected behaviour is what turns a collection of features into an operational system.</p></div>
    </section>
</main>
@endsection
