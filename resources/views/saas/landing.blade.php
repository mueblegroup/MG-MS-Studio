@extends('saas.layouts.marketing', [
    'title' => 'Mueble Studio — Complete Institute Management Platform',
    'metaDescription' => 'Manage students, teachers, classes, attendance, subscriptions, payments and studio operations from one connected platform.'
])

@section('content')
<main>
    <section class="wrap hero">
        <div class="hero-grid">
            <div>
                <div class="eyebrow"><span class="dot"></span>Built for academies, institutes and studios</div>
                <h1>A complete <span class="gradient">operating system</span> for modern learning businesses.</h1>
                <p class="lead">Mueble Studio connects student management, teachers, scheduling, attendance, memberships, class cards, recurring billing, notifications and reporting in one professional platform.</p>
                <div class="actions">
                    <a class="btn btn-primary" href="{{ route('register') }}">Start your studio</a>
                    <a class="btn btn-soft" href="{{ route('marketing.platform') }}">See how it works</a>
                </div>
                <div class="subnav">
                    <span class="pill">Multi-role portals</span><span class="pill">Stripe & HitPay</span><span class="pill">Recurring subscriptions</span><span class="pill">Attendance tracking</span>
                </div>
            </div>
            <div class="hero-panel">
                <div class="kicker">Live operational view</div>
                <h3>Know what is happening across your institute.</h3>
                <p>See active students, today’s classes, upcoming renewals, attendance activity and operational tasks without jumping between disconnected tools.</p>
                <div class="metric-grid">
                    <div class="metric"><strong>360°</strong><span>student lifecycle</span></div>
                    <div class="metric"><strong>3</strong><span>role-based portals</span></div>
                    <div class="metric"><strong>1</strong><span>connected checkout flow</span></div>
                    <div class="metric"><strong>24/7</strong><span>self-service access</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="wrap section">
        <div class="section-head">
            <div class="kicker">What the system manages</div>
            <h2>One platform instead of five separate tools.</h2>
            <p class="section-copy">The system is designed around the full operating cycle of a learning business—from the moment a student joins until every class, payment, attendance record and renewal is completed.</p>
        </div>
        <div class="grid-3">
            <article class="card"><div class="card-icon">S</div><h3>Students and enrolment</h3><p>Maintain student profiles, class access, plans, class cards, payment records and attendance history in one place.</p></article>
            <article class="card"><div class="card-icon">C</div><h3>Classes and schedules</h3><p>Build classes, assign teachers, create sessions, manage venues and keep daily schedules organised.</p></article>
            <article class="card"><div class="card-icon">P</div><h3>Payments and subscriptions</h3><p>Sell plans and classes, collect payments, manage recurring billing and maintain a clear transaction history.</p></article>
            <article class="card"><div class="card-icon">A</div><h3>Attendance and usage</h3><p>Record attendance for classes, plans and class cards while keeping eligibility linked to valid access.</p></article>
            <article class="card"><div class="card-icon">T</div><h3>Teachers and staff</h3><p>Give instructors a focused portal for schedules, assigned sessions, attendance and student participation.</p></article>
            <article class="card"><div class="card-icon">N</div><h3>Communication</h3><p>Use in-app notifications and operational messages to keep students and teams informed.</p></article>
        </div>
    </section>

    <section class="wrap section">
        <div class="grid-2">
            <div class="band">
                <div class="kicker" style="color:#bfdbfe">Built for real operations</div>
                <h2 style="margin:10px 0 12px;font-size:42px">Not only an LMS. A complete institute management system.</h2>
                <p>Traditional LMS products focus mainly on course content. Mueble Studio focuses on the daily business around learning: enrolment, capacity, staff, sessions, attendance, packages, checkout, renewals and administration.</p>
            </div>
            <div class="steps">
                <div class="step"><div class="step-no">01</div><div><h3>Create your studio</h3><p>Choose a plan, reserve your subdomain and launch a dedicated workspace.</p></div></div>
                <div class="step"><div class="step-no">02</div><div><h3>Configure your operations</h3><p>Add staff, classes, packages, class cards, schedules, payment settings and studio preferences.</p></div></div>
                <div class="step"><div class="step-no">03</div><div><h3>Serve students at scale</h3><p>Let students register, purchase, view schedules, track attendance and manage subscriptions.</p></div></div>
            </div>
        </div>
    </section>

    <section class="wrap section">
        <div class="section-head">
            <div class="kicker">Designed for different organisations</div>
            <h2>Flexible enough for education, training and membership-based studios.</h2>
        </div>
        <div class="grid-3">
            <article class="card"><h3>Tuition and learning centres</h3><p>Manage recurring classes, student plans, teachers, session schedules and payments.</p></article>
            <article class="card"><h3>Dance, music and art studios</h3><p>Coordinate instructors, class capacity, attendance, packages and performance-focused schedules.</p></article>
            <article class="card"><h3>Fitness and wellness academies</h3><p>Sell subscriptions or class cards, track usage and support repeat bookings.</p></article>
            <article class="card"><h3>Professional training providers</h3><p>Run structured cohorts, workshops, attendance and student administration.</p></article>
            <article class="card"><h3>Language centres</h3><p>Organise levels, batches, teachers, recurring lessons and student progress records.</p></article>
            <article class="card"><h3>Multi-branch institutes</h3><p>Use a structured foundation designed to grow into larger operational requirements.</p></article>
        </div>
        <div class="actions"><a class="btn btn-soft" href="{{ route('marketing.solutions') }}">See who it is for</a></div>
    </section>
</main>
@endsection
