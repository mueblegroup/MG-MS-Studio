@extends('saas.layouts.marketing', ['title' => 'How It Works — ClassM8'])
@section('content')
<main class="wrap">
    <header class="page-head">
        <div class="kicker">How ClassM8 works</div>
        <h1>From “I want this class” to “I’m here” — one connected flow.</h1>
        <p class="section-copy">ClassM8 is built around the real journey of a class business: publish what you offer, collect payment, give access, show the schedule, deliver the class and keep the record.</p>
    </header>

    <section class="section">
        <div class="browser">
            <div class="browser-bar"><i></i><i></i><i></i><span style="margin-left:8px;font-size:11px;color:#667085">Your ClassM8 workspace</span></div>
            <div class="screen">
                <div class="screen-head"><div><div class="screen-title">Today at the studio</div><small style="color:#667085">A simple operational snapshot</small></div><span class="screen-chip">Live workflow</span></div>
                <div class="stat-row">
                    <div class="mini-stat"><strong>12</strong><span>classes scheduled</span></div>
                    <div class="mini-stat"><strong>86</strong><span>active students</span></div>
                    <div class="mini-stat"><strong>94%</strong><span>attendance marked</span></div>
                </div>
                <div class="class-list">
                    <div class="class-item"><div class="class-date">09<br><small>AM</small></div><div><b>Junior Ballet</b><small>Studio A · Ms. Aisha · 14 students</small></div><span class="status">Plan</span></div>
                    <div class="class-item"><div class="class-date">02<br><small>PM</small></div><div><b>Maths Intensive</b><small>Room 3 · Mr. Lee · 18 students</small></div><span class="status">Subscription</span></div>
                    <div class="class-item"><div class="class-date">06<br><small>PM</small></div><div><b>Open Technique</b><small>Main Hall · Ms. Sara · 11 bookings</small></div><span class="status">Class card</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="steps">
            <div class="step"><div class="step-no">01</div><div><h3>Create your studio workspace</h3><p>Launch a dedicated ClassM8 workspace with your organisation settings, timezone, currency and payment configuration.</p></div></div>
            <div class="step"><div class="step-no">02</div><div><h3>Add the people who deliver the experience</h3><p>Create admins, teachers and students so everyone receives the right portal and permissions.</p></div></div>
            <div class="step"><div class="step-no">03</div><div><h3>Create what you want to sell</h3><p>Publish individual classes, recurring plans or flexible class cards depending on how your programme is structured.</p></div></div>
            <div class="step"><div class="step-no">04</div><div><h3>Let students buy online</h3><p>Students browse eligible offerings, add them to cart and complete checkout through the configured payment gateway.</p></div></div>
            <div class="step"><div class="step-no">05</div><div><h3>Turn payment into access</h3><p>The student’s purchase becomes the correct class, plan, subscription or class-card entitlement in their account.</p></div></div>
            <div class="step"><div class="step-no">06</div><div><h3>Show everyone the right schedule</h3><p>Students see upcoming class details while teachers see the sessions assigned to them.</p></div></div>
            <div class="step"><div class="step-no">07</div><div><h3>Run the class and mark attendance</h3><p>Attendance is recorded against the correct access model, including class-card usage where applicable.</p></div></div>
            <div class="step"><div class="step-no">08</div><div><h3>Keep renewals and records organised</h3><p>Recurring billing, payment history, receipts, cancellations and future access remain visible in the same system.</p></div></div>
        </div>
    </section>

    <section class="section">
        <div class="role-strip">
            <div class="role-card"><span>ADMIN</span><h3>Build & control</h3><p>Create offerings, people, payments and schedules.</p></div>
            <div class="role-card"><span>TEACHER</span><h3>Deliver</h3><p>See assignments and record attendance.</p></div>
            <div class="role-card"><span>STUDENT</span><h3>Follow</h3><p>Know what is booked, paid and coming next.</p></div>
            <div class="role-card"><span>CLIENT</span><h3>Buy</h3><p>Choose the right class, plan or class card online.</p></div>
        </div>
    </section>
</main>
@endsection