@extends('saas.layouts.marketing', ['title' => 'Features — ClassM8'])
@section('content')
<main class="wrap">
    <header class="page-head">
        <div class="kicker">Client-facing features</div>
        <h1>Everything your academy needs to sell, schedule and deliver classes.</h1>
        <p class="section-copy">ClassM8 keeps the customer journey connected from discovery and payment through scheduling, attendance, renewals and student self-service.</p>
    </header>

    <section class="section">
        <div class="feature-showcase">
            <div>
                <div class="kicker">Three ways to sell</div>
                <h2>Choose the right product for every programme.</h2>
                <p class="section-copy">Offer a one-off class, a structured plan or a flexible class card. Each model has its own purpose, while all three remain connected to the same students, schedules, payments and attendance records.</p>
                <div class="grid-3" style="grid-template-columns:1fr;margin-top:22px">
                    <article class="card"><h3>Individual classes</h3><p>Best for trials, workshops and drop-ins where the student buys access to a specific class.</p></article>
                    <article class="card"><h3>Plans</h3><p>Best for ongoing or recurring programmes where students follow a set schedule and billing cycle.</p></article>
                    <article class="card"><h3>Class cards</h3><p>Best for flexible programmes where a student prepays for a pool of class credits.</p></article>
                </div>
            </div>
            <div class="classcard-demo">
                <small style="opacity:.7">HOW CLASS CARDS WORK</small>
                <h3 style="font-size:30px;margin:7px 0 4px">10-Class Studio Pass</h3>
                <p style="opacity:.75;margin:0">A prepaid balance that is reduced as eligible sessions are attended.</p>
                <div class="credit-ring"><span><b>7</b><small>credits left</small></span></div>
                <ul><li>Purchase recorded to the student</li><li>Balance visible to staff and student</li><li>Attendance can consume a credit</li><li>Usage remains traceable afterwards</li></ul>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-head"><div class="kicker">Student experience</div><h2>A clear self-service portal from purchase to attendance.</h2></div>
        <div class="grid-3">
            <article class="card"><div class="card-icon">01</div><h3>Personal schedule</h3><p>Students can see upcoming classes with date, time, teacher and the relevant plan, class card or direct purchase.</p></article>
            <article class="card"><div class="card-icon">02</div><h3>Subscriptions</h3><p>Keep recurring memberships visible so students know what they are enrolled in and when upcoming sessions happen.</p></article>
            <article class="card"><div class="card-icon">03</div><h3>Class-card balance</h3><p>Show the student how many credits remain instead of making them ask the front desk after every class.</p></article>
            <article class="card"><div class="card-icon">04</div><h3>Attendance history</h3><p>Maintain a clear record of class attendance across individual classes, plans and class cards.</p></article>
            <article class="card"><div class="card-icon">05</div><h3>Payments & receipts</h3><p>Students can review payment history and access receipts without staff manually sending records.</p></article>
            <article class="card"><div class="card-icon">06</div><h3>Notifications</h3><p>Use in-app communication to surface operational updates and important information to users.</p></article>
        </div>
    </section>

    <section class="section">
        <div class="proof">
            <div class="proof-photo"><img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=1400&q=82" alt="Teacher working with students in a classroom"></div>
            <div class="proof-copy">
                <div class="kicker">Teacher portal</div>
                <h3>Give instructors the information they need to run the class.</h3>
                <p>Teachers do not need the full administrative system. Their focused portal surfaces assigned classes, plans, class cards, schedules and attendance actions.</p>
                <ul class="check-list"><li>Assigned class visibility</li><li>Teacher schedule</li><li>Student participation details</li><li>Fast attendance marking</li></ul>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-head"><div class="kicker">Admin & owner control</div><h2>Manage the business without losing sight of the customer experience.</h2></div>
        <div class="grid-3">
            <article class="card"><div class="card-icon">A</div><h3>Class management</h3><p>Create classes, assign teachers, set schedules, manage venues and control capacity.</p></article>
            <article class="card"><div class="card-icon">P</div><h3>Plan management</h3><p>Build recurring or structured programmes with sessions that students can follow ahead of time.</p></article>
            <article class="card"><div class="card-icon">C</div><h3>Class-card products</h3><p>Create session-credit products and review purchases, balances and usage.</p></article>
            <article class="card"><div class="card-icon">$</div><h3>Online checkout</h3><p>Sell through the ClassM8 shop and collect supported online payments through Stripe or HitPay.</p></article>
            <article class="card"><div class="card-icon">✓</div><h3>Attendance</h3><p>Record attendance across direct classes, plan sessions and class-card usage.</p></article>
            <article class="card"><div class="card-icon">U</div><h3>People management</h3><p>Manage admins, teachers and students from one organisation workspace.</p></article>
        </div>
    </section>

    <section class="section">
        <div class="band">
            <div class="kicker" style="color:#bfdbfe">Connected by design</div>
            <h2 style="font-size:42px;margin:10px 0 12px">A purchase should change what the student can see and do.</h2>
            <p>ClassM8 is designed so payments are not isolated accounting records. Purchases connect to access, schedules, subscription state, class-card balances and attendance—creating a cleaner experience for both the student and your team.</p>
        </div>
    </section>
</main>
@endsection