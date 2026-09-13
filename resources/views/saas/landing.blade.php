@extends('saas.layouts.marketing', [
    'title' => 'ClassM8 — Sell, Schedule & Manage Every Class',
    'metaDescription' => 'ClassM8 helps academies and studios sell classes, plans and class cards, manage teachers and students, collect payments and run attendance from one connected platform.'
])

@section('content')
<main>
    <section class="wrap hero">
        <div class="hero-grid">
            <div>
                <div class="eyebrow"><span class="dot"></span>Built for tuition centres, academies and studios</div>
                <h1>Run every class. Sell every seat. <span class="gradient">Keep everyone in sync.</span></h1>
                <p class="lead">ClassM8 gives your business one place to sell individual classes, recurring plans and flexible class cards — then connects every purchase to schedules, teachers, attendance, payments and the student portal.</p>
                <div class="actions">
                    <a class="btn btn-primary" href="{{ route('register') }}">Start your studio</a>
                    <a class="btn btn-soft" href="#products">See what you can sell</a>
                </div>
                <div class="subnav">
                    <span class="pill">Individual classes</span>
                    <span class="pill">Recurring plans</span>
                    <span class="pill">Class cards</span>
                    <span class="pill">Student self-service</span>
                </div>
            </div>

            <div class="photo-stage">
                <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=82" alt="Teacher leading students in a real classroom environment">
                <div class="floating-card"><b>One system</b><small>Admin · Teacher · Student · Client</small></div>
                <div class="photo-caption">
                    <strong>Built around real class operations</strong>
                    <span>From the first online purchase to the final attendance mark, ClassM8 keeps the same student journey connected.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="wrap section" id="products">
        <div class="section-head">
            <div class="kicker">Sell the way your business actually works</div>
            <h2>Classes, plans and class cards — clearly different, all connected.</h2>
            <p class="section-copy">Instead of forcing every programme into one billing model, ClassM8 lets you offer the right product for each type of student and class.</p>
        </div>

        <div class="grid-3">
            <article class="card">
                <div class="card-icon">1x</div>
                <h3>Individual classes</h3>
                <p>Perfect for workshops, trial sessions, drop-ins and one-off lessons. Students buy the class they want and receive access to the correct session.</p>
                <ul><li>One-time purchase</li><li>Specific date, teacher and venue</li><li>Capacity-aware class access</li><li>Attendance linked to the purchase</li></ul>
            </article>
            <article class="card">
                <div class="card-icon">∞</div>
                <h3>Plans & subscriptions</h3>
                <p>Ideal for monthly programmes, term-based classes and recurring memberships. Students stay connected to a structured schedule while billing continues automatically.</p>
                <ul><li>Recurring payment support</li><li>Scheduled plan sessions</li><li>Renewal and cancellation handling</li><li>Upcoming classes visible to students</li></ul>
            </article>
            <article class="card">
                <div class="card-icon">8</div>
                <h3>Class cards</h3>
                <p>Sell a bundle of class credits that students can use across eligible sessions. It is flexible for the student while keeping usage controlled for the studio.</p>
                <ul><li>Prepaid session credits</li><li>Remaining balance tracking</li><li>Usage deducted through attendance</li><li>Great for flexible attendance</li></ul>
            </article>
        </div>
    </section>

    <section class="wrap section">
        <div class="feature-showcase">
            <div>
                <div class="kicker">Class cards explained</div>
                <h2>Sell flexibility without losing control of attendance.</h2>
                <p class="section-copy">A ClassM8 class card works like a digital session pass. The student purchases a set number of credits, sees the card in their portal and uses those credits as they attend eligible classes. Your team can always see what was purchased, what was used and what remains.</p>
                <ul class="check-list">
                    <li>Create different card sizes for different customer needs</li>
                    <li>Track purchases and assignments per student</li>
                    <li>Let teachers or admins mark usage from attendance workflows</li>
                    <li>Keep the remaining credit balance visible and auditable</li>
                </ul>
                <div class="actions"><a class="btn btn-soft" href="{{ route('marketing.features') }}">Explore class cards & more</a></div>
            </div>
            <div class="classcard-demo">
                <div style="display:flex;justify-content:space-between;gap:20px;align-items:flex-start">
                    <div><small style="opacity:.7">CLASSM8 CLASS CARD</small><h3 style="font-size:28px;margin:6px 0">8-Class Flex Pass</h3><span style="opacity:.75">Valid for eligible studio classes</span></div>
                    <div style="padding:8px 10px;border-radius:12px;background:rgba(255,255,255,.12);font-size:11px;font-weight:900">ACTIVE</div>
                </div>
                <div class="credit-ring"><span><b>6</b><small>credits left</small></span></div>
                <ul>
                    <li>✓ 8 credits purchased</li>
                    <li>✓ 2 sessions attended</li>
                    <li>✓ Usage recorded with attendance</li>
                    <li>✓ Balance visible in the student portal</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="wrap section">
        <div class="feature-showcase reverse">
            <div class="browser">
                <div class="browser-bar"><i></i><i></i><i></i><span style="margin-left:8px;font-size:11px;color:#667085">student.classm8.app</span></div>
                <div class="screen">
                    <div class="screen-head"><div><div class="screen-title">Good afternoon, Maya</div><small style="color:#667085">Here is what is coming up</small></div><span class="screen-chip">Student portal</span></div>
                    <div class="stat-row">
                        <div class="mini-stat"><strong>3</strong><span>upcoming classes</span></div>
                        <div class="mini-stat"><strong>92%</strong><span>attendance</span></div>
                        <div class="mini-stat"><strong>6</strong><span>class-card credits</span></div>
                    </div>
                    <div class="class-list">
                        <div class="class-item"><div class="class-date">14<br><small>SEP</small></div><div><b>Contemporary Level 2</b><small>5:30 PM · Studio A · Ms. Alina</small></div><span class="status">Plan</span></div>
                        <div class="class-item"><div class="class-date">16<br><small>SEP</small></div><div><b>Technique Workshop</b><small>7:00 PM · Main Hall · Mr. Daniel</small></div><span class="status">Class card</span></div>
                        <div class="class-item"><div class="class-date">20<br><small>SEP</small></div><div><b>Performance Lab</b><small>4:00 PM · Studio B · Ms. Sara</small></div><span class="status">Single class</span></div>
                    </div>
                </div>
            </div>
            <div>
                <div class="kicker">A portal students will actually use</div>
                <h2>Students can see what they bought — and exactly when to show up.</h2>
                <p class="section-copy">The student experience brings schedule, teacher, date, time, attendance, subscriptions, class cards, payments and receipts into one place. No more asking the front desk which class they are booked into.</p>
                <ul class="check-list"><li>Upcoming class details in one view</li><li>Subscription and plan visibility</li><li>Class-card balances</li><li>Payment history and downloadable receipts</li></ul>
            </div>
        </div>
    </section>

    <section class="wrap section">
        <div class="section-head">
            <div class="kicker">One platform, four clear experiences</div>
            <h2>Everyone sees what they need — without the clutter they do not.</h2>
        </div>
        <div class="role-strip">
            <div class="role-card"><span>OWNER / ADMIN</span><h3>Control the business</h3><p>Manage classes, students, staff, plans, class cards, payments, settings and daily operations.</p></div>
            <div class="role-card"><span>TEACHER</span><h3>Focus on delivery</h3><p>See assigned classes and schedules, open session details and mark attendance quickly.</p></div>
            <div class="role-card"><span>STUDENT</span><h3>Know what’s next</h3><p>View upcoming classes, subscriptions, attendance, class-card balance, payments and receipts.</p></div>
            <div class="role-card"><span>CLIENT / BUYER</span><h3>Buy with confidence</h3><p>Browse available offerings, choose the right class or plan and complete checkout online.</p></div>
        </div>
    </section>

    <section class="wrap section">
        <div class="proof">
            <div class="proof-photo"><img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1400&q=82" alt="Students learning together in a modern real-life education environment"></div>
            <div class="proof-copy">
                <div class="kicker">Built to help you grow</div>
                <h3>Less admin friction. More room to teach, sell and retain.</h3>
                <p>ClassM8 turns the operational parts of your business into a connected customer journey: publish an offering, accept payment, create access, show the schedule, mark attendance and keep the student informed.</p>
                <ul class="check-list"><li>Online shop and checkout</li><li>Stripe and HitPay payment support</li><li>Teacher and student self-service</li><li>Role-based dashboards and notifications</li></ul>
                <div class="actions"><a class="btn btn-primary" href="{{ route('marketing.platform') }}">See how ClassM8 works</a></div>
            </div>
        </div>
    </section>

    <section class="wrap section">
        <div class="band">
            <div class="kicker" style="color:#bfdbfe">Designed for real learning businesses</div>
            <h2 style="margin:10px 0 12px;font-size:clamp(34px,4vw,50px)">From tuition centres to dance studios, the workflow stays simple.</h2>
            <p>ClassM8 is suitable for organisations that run repeat classes, multiple teachers, memberships, packages or session-based products — including tuition centres, language academies, music schools, dance studios, fitness and wellness businesses, and professional training providers.</p>
            <div class="actions"><a class="btn" style="background:#fff;color:#1d4ed8" href="{{ route('marketing.solutions') }}">See who it is for</a><a class="btn" style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2)" href="{{ route('marketing.pricing') }}">View pricing</a></div>
        </div>
    </section>
</main>
@endsection