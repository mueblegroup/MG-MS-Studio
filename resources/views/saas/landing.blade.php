<style>
    :root {
        --ink: #101828;
        --muted: #667085;
        --line: rgba(16, 24, 40, .10);
        --soft: #f8fbff;
        --blue: #2563eb;
        --cyan: #06b6d4;
        --purple: #7c3aed;
        --pink: #ec4899;
        --orange: #f97316;
        --green: #10b981;
        --shadow: 0 24px 70px rgba(15, 23, 42, .10);
        --shadow-soft: 0 14px 35px rgba(15, 23, 42, .08);
    }

    * {
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        margin: 0;
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: var(--ink);
        background:
            radial-gradient(circle at 10% 5%, rgba(37, 99, 235, .12), transparent 32%),
            radial-gradient(circle at 90% 12%, rgba(236, 72, 153, .12), transparent 30%),
            radial-gradient(circle at 45% 45%, rgba(6, 182, 212, .08), transparent 34%),
            linear-gradient(180deg, #ffffff 0%, #f8fbff 45%, #ffffff 100%);
        overflow-x: hidden;
    }

    a {
        color: inherit;
    }

    .lp {
        min-height: 100vh;
        position: relative;
        overflow: hidden;
    }

    .wrap {
        width: min(1180px, calc(100% - 40px));
        margin: 0 auto;
    }

    .nav {
        position: sticky;
        top: 14px;
        z-index: 50;
        margin-top: 14px;
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, .80);
        border-radius: 999px;
        background: rgba(255, 255, 255, .78);
        backdrop-filter: blur(18px);
        box-shadow: 0 12px 35px rgba(15, 23, 42, .08);
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 900;
        letter-spacing: -.03em;
    }

    .brand-mark {
        width: 38px;
        height: 38px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--blue), var(--cyan), var(--pink));
        box-shadow: 0 12px 30px rgba(37, 99, 235, .28);
    }

    .nav-links {
        display: flex;
        align-items: center;
        gap: 22px;
        font-size: 14px;
        font-weight: 700;
        color: #344054;
    }

    .nav-links a {
        text-decoration: none;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 13px 20px;
        border-radius: 999px;
        border: 0;
        text-decoration: none;
        font-weight: 900;
        letter-spacing: -.01em;
        transition: .25s ease;
        cursor: pointer;
    }

    .btn-primary {
        color: white;
        background: linear-gradient(135deg, var(--blue), var(--purple), var(--pink));
        box-shadow: 0 18px 35px rgba(124, 58, 237, .25);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 45px rgba(124, 58, 237, .32);
    }

    .btn-soft {
        color: #1d4ed8;
        background: #eff6ff;
        border: 1px solid rgba(37, 99, 235, .13);
    }

    .btn-soft:hover {
        transform: translateY(-2px);
        background: #dbeafe;
    }

    .hero {
        position: relative;
        padding: 88px 0 70px;
        display: grid;
        grid-template-columns: 1.02fr .98fr;
        gap: 44px;
        align-items: center;
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 9px 14px;
        border-radius: 999px;
        border: 1px solid rgba(37, 99, 235, .14);
        background: rgba(255, 255, 255, .78);
        box-shadow: var(--shadow-soft);
        color: #1d4ed8;
        font-weight: 900;
        font-size: 13px;
    }

    .pulse-dot {
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: var(--green);
        box-shadow: 0 0 0 7px rgba(16, 185, 129, .12);
    }

    .hero h1 {
        margin: 22px 0 18px;
        font-size: clamp(44px, 6.5vw, 82px);
        line-height: .94;
        letter-spacing: -.065em;
    }

    .gradient-text {
        background: linear-gradient(135deg, #2563eb, #7c3aed, #ec4899, #f97316);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .hero-copy {
        margin: 0;
        max-width: 650px;
        color: var(--muted);
        font-size: 19px;
        line-height: 1.7;
    }

    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 28px;
    }

    .trust-strip {
        margin-top: 30px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        max-width: 640px;
    }

    .trust-item {
        padding: 15px;
        border-radius: 22px;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, .74);
        box-shadow: 0 8px 25px rgba(15, 23, 42, .05);
    }

    .trust-item strong {
        display: block;
        font-size: 22px;
        letter-spacing: -.04em;
    }

    .trust-item span {
        color: var(--muted);
        font-size: 13px;
        font-weight: 700;
    }

    .dashboard-card {
        position: relative;
        padding: 18px;
        border-radius: 34px;
        background:
            linear-gradient(#ffffff, #ffffff) padding-box,
            linear-gradient(135deg, rgba(37, 99, 235, .45), rgba(236, 72, 153, .45), rgba(6, 182, 212, .45)) border-box;
        border: 1px solid transparent;
        box-shadow: var(--shadow);
        transform: rotate(1deg);
    }

    .dashboard-card::before,
    .dashboard-card::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        filter: blur(20px);
        opacity: .45;
        z-index: -1;
    }

    .dashboard-card::before {
        width: 180px;
        height: 180px;
        background: var(--cyan);
        left: -35px;
        top: 40px;
    }

    .dashboard-card::after {
        width: 210px;
        height: 210px;
        background: var(--pink);
        right: -45px;
        bottom: 20px;
    }

    .mock-window {
        overflow: hidden;
        border-radius: 25px;
        background: #fff;
        border: 1px solid var(--line);
    }

    .mock-top {
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #f8fbff, #ffffff);
        border-bottom: 1px solid var(--line);
    }

    .mock-dots {
        display: flex;
        gap: 7px;
    }

    .mock-dots span {
        width: 11px;
        height: 11px;
        border-radius: 999px;
    }

    .mock-dots span:nth-child(1) { background: #fb7185; }
    .mock-dots span:nth-child(2) { background: #fbbf24; }
    .mock-dots span:nth-child(3) { background: #34d399; }

    .mock-status {
        padding: 7px 11px;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        font-size: 12px;
        font-weight: 900;
    }

    .mock-body {
        padding: 18px;
        display: grid;
        gap: 14px;
    }

    .metric-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 13px;
    }

    .metric {
        padding: 18px;
        border-radius: 22px;
        background: #f8fbff;
        border: 1px solid var(--line);
    }

    .metric small {
        color: var(--muted);
        font-weight: 800;
    }

    .metric b {
        display: block;
        margin-top: 8px;
        font-size: 28px;
        letter-spacing: -.04em;
    }

    .progress-card {
        padding: 18px;
        border-radius: 22px;
        background: linear-gradient(135deg, #eff6ff, #faf5ff);
        border: 1px solid rgba(37, 99, 235, .10);
    }

    .progress-card h3 {
        margin: 0 0 12px;
    }

    .bar {
        height: 12px;
        border-radius: 999px;
        background: rgba(37, 99, 235, .12);
        overflow: hidden;
        margin-bottom: 10px;
    }

    .bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--blue), var(--cyan), var(--pink));
    }

    .mini-list {
        display: grid;
        gap: 10px;
    }

    .mini-list div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid var(--line);
        font-size: 13px;
        font-weight: 800;
    }

    .section {
        padding: 78px 0;
    }

    .section-head {
        max-width: 760px;
        margin-bottom: 34px;
    }

    .section-kicker {
        color: #2563eb;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .12em;
        font-size: 12px;
    }

    .section h2 {
        margin: 10px 0 12px;
        font-size: clamp(32px, 4.8vw, 56px);
        line-height: 1;
        letter-spacing: -.055em;
    }

    .section-lead {
        margin: 0;
        color: var(--muted);
        font-size: 18px;
        line-height: 1.7;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }

    .feature-card {
        position: relative;
        overflow: hidden;
        padding: 26px;
        min-height: 260px;
        border-radius: 30px;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, .82);
        box-shadow: 0 12px 35px rgba(15, 23, 42, .06);
        transition: .25s ease;
    }

    .feature-card:hover {
        transform: translateY(-7px);
        box-shadow: var(--shadow);
    }

    .feature-card::after {
        content: "";
        position: absolute;
        width: 150px;
        height: 150px;
        border-radius: 999px;
        right: -55px;
        top: -55px;
        opacity: .13;
        background: var(--accent);
    }

    .icon {
        width: 54px;
        height: 54px;
        border-radius: 20px;
        display: grid;
        place-items: center;
        color: white;
        font-size: 24px;
        font-weight: 900;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        box-shadow: 0 16px 28px rgba(15, 23, 42, .12);
    }

    .feature-card h3 {
        margin: 22px 0 10px;
        font-size: 22px;
        letter-spacing: -.03em;
    }

    .feature-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
    }

    .split {
        display: grid;
        grid-template-columns: .9fr 1.1fr;
        gap: 28px;
        align-items: stretch;
    }

    .panel {
        border-radius: 34px;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, .82);
        box-shadow: var(--shadow-soft);
        padding: 30px;
    }

    .color-panel {
        color: white;
        background:
            radial-gradient(circle at 20% 20%, rgba(255,255,255,.25), transparent 30%),
            linear-gradient(135deg, #2563eb, #7c3aed, #ec4899);
        border: 0;
    }

    .color-panel p {
        color: rgba(255,255,255,.82);
    }

    .workflow {
        display: grid;
        gap: 14px;
    }

    .step {
        display: grid;
        grid-template-columns: 54px 1fr;
        gap: 15px;
        padding: 18px;
        border: 1px solid var(--line);
        border-radius: 24px;
        background: #fff;
    }

    .step-no {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 950;
    }

    .step h3 {
        margin: 0 0 6px;
        letter-spacing: -.02em;
    }

    .step p {
        margin: 0;
        color: var(--muted);
        line-height: 1.6;
    }

    .roles {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }

    .role-card {
        padding: 28px;
        border-radius: 30px;
        background: #fff;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-soft);
    }

    .role-card h3 {
        margin: 0 0 10px;
        font-size: 24px;
        letter-spacing: -.04em;
    }

    .role-card ul {
        margin: 18px 0 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 12px;
    }

    .role-card li {
        color: var(--muted);
        display: flex;
        gap: 10px;
        line-height: 1.5;
    }

    .role-card li::before {
        content: "✓";
        color: var(--green);
        font-weight: 950;
    }

    .analytics-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .analytics-card {
        padding: 24px;
        border-radius: 28px;
        border: 1px solid var(--line);
        background: #fff;
        box-shadow: var(--shadow-soft);
    }

    .analytics-card h3 {
        margin: 0 0 10px;
    }

    .analytics-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
    }

    .security-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }

    .security-item {
        padding: 20px;
        border-radius: 24px;
        background: #fff;
        border: 1px solid var(--line);
        font-weight: 900;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
    }

    .cta {
        padding: 80px 0 95px;
    }

    .cta-box {
        position: relative;
        overflow: hidden;
        padding: 48px;
        border-radius: 40px;
        color: white;
        background:
            radial-gradient(circle at 15% 15%, rgba(255,255,255,.24), transparent 28%),
            radial-gradient(circle at 85% 35%, rgba(255,255,255,.20), transparent 30%),
            linear-gradient(135deg, #2563eb, #7c3aed, #ec4899, #f97316);
        box-shadow: 0 30px 80px rgba(124, 58, 237, .22);
    }

    .cta-box h2 {
        max-width: 780px;
        margin: 0;
        font-size: clamp(34px, 5vw, 64px);
        line-height: 1;
        letter-spacing: -.055em;
    }

    .cta-box p {
        max-width: 720px;
        margin: 18px 0 28px;
        color: rgba(255,255,255,.84);
        font-size: 18px;
        line-height: 1.7;
    }

    .cta-box .btn {
        background: #fff;
        color: #1d4ed8;
    }

    .footer {
        padding: 30px 0 45px;
        color: var(--muted);
        border-top: 1px solid var(--line);
    }

    .footer-inner {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 14px;
    }

    .reveal {
        animation: rise .75s ease both;
    }

    .delay-1 { animation-delay: .08s; }
    .delay-2 { animation-delay: .16s; }
    .delay-3 { animation-delay: .24s; }

    @keyframes rise {
        from {
            opacity: 0;
            transform: translateY(22px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .float {
        animation: float 5s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(1deg); }
        50% { transform: translateY(-12px) rotate(-1deg); }
    }

    @media (max-width: 980px) {
        .hero,
        .split {
            grid-template-columns: 1fr;
        }

        .feature-grid,
        .roles,
        .security-strip {
            grid-template-columns: repeat(2, 1fr);
        }

        .dashboard-card {
            transform: none;
        }
    }

    @media (max-width: 720px) {
        .wrap {
            width: min(100% - 28px, 1180px);
        }

        .nav {
            border-radius: 28px;
            align-items: flex-start;
        }

        .nav-links {
            display: none;
        }

        .hero {
            padding-top: 58px;
        }

        .hero-actions,
        .trust-strip,
        .metric-row,
        .feature-grid,
        .roles,
        .analytics-grid,
        .security-strip {
            grid-template-columns: 1fr;
        }

        .hero-actions {
            display: grid;
        }

        .btn {
            width: 100%;
        }

        .panel,
        .cta-box {
            padding: 28px;
            border-radius: 30px;
        }

        .section {
            padding: 58px 0;
        }
    }
</style>

<main class="lp">
    <header class="wrap nav reveal">
        <a href="/" class="brand" style="text-decoration:none;">
            <span class="brand-mark"></span>
            <span>Mueble Studio</span>
        </a>

        <nav class="nav-links">
            <a href="#platform">Platform</a>
            <a href="#workflow">Workflow</a>
            <a href="#roles">Roles</a>
            <a href="#analytics">Analytics</a>
            <a href="#security">Security</a>
            <a class="btn btn-primary" href="/login">Login</a>
        </nav>
    </header>

    <section class="wrap hero">
        <div class="reveal">
            <div class="eyebrow">
                <span class="pulse-dot"></span>
                Built for modern institutes, academies and studios
            </div>

            <h1>
                A colorful, futuristic
                <span class="gradient-text">LMS operating system</span>
                for growing education teams.
            </h1>

            <p class="hero-copy">
                Mueble Studio brings students, teachers, classes, plans, attendance, payments,
                schedules and admin operations into one clean platform — designed for institutes
                that want to look professional and operate reliably.
            </p>

            <div class="hero-actions">
                <a class="btn btn-primary" href="/register">Start Your Studio</a>
                <a class="btn btn-soft" href="#platform">Explore Features</a>
            </div>

            <div class="trust-strip">
                <div class="trust-item">
                    <strong>360°</strong>
                    <span>student lifecycle</span>
                </div>
                <div class="trust-item">
                    <strong>Smart</strong>
                    <span>class operations</span>
                </div>
                <div class="trust-item">
                    <strong>Ready</strong>
                    <span>for scaling teams</span>
                </div>
            </div>
        </div>

        <div class="dashboard-card float reveal delay-1">
            <div class="mock-window">
                <div class="mock-top">
                    <div class="mock-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="mock-status">Live Dashboard</div>
                </div>

                <div class="mock-body">
                    <div class="metric-row">
                        <div class="metric">
                            <small>Active Students</small>
                            <b>1,248</b>
                        </div>
                        <div class="metric">
                            <small>Today Classes</small>
                            <b>36</b>
                        </div>
                    </div>

                    <div class="progress-card">
                        <h3>Institute Growth Pulse</h3>
                        <div class="bar"><span style="width:82%"></span></div>
                        <p style="margin:0;color:#667085;line-height:1.6;">
                            Enrolment, attendance and payments are connected in one operational view.
                        </p>
                    </div>

                    <div class="mini-list">
                        <div>
                            <span>Yoga Beginner Batch</span>
                            <span style="color:#2563eb;">24 learners</span>
                        </div>
                        <div>
                            <span>Teacher Schedule</span>
                            <span style="color:#10b981;">Optimised</span>
                        </div>
                        <div>
                            <span>Payment Tracking</span>
                            <span style="color:#ec4899;">Updated</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="platform" class="wrap section">
        <div class="section-head reveal">
            <div class="section-kicker">Platform Features</div>
            <h2>Everything your institute needs, presented beautifully.</h2>
            <p class="section-lead">
                The landing page should immediately show that this is not a basic student portal.
                It is a complete business platform for managing education operations from first enquiry
                to recurring attendance and payments.
            </p>
        </div>

        <div class="feature-grid">
            <div class="feature-card reveal" style="--accent:#2563eb;--accent2:#06b6d4;">
                <div class="icon">S</div>
                <h3>Student Management</h3>
                <p>
                    Manage student profiles, enrolments, active plans, class cards, payment history
                    and attendance records in a clear admin experience.
                </p>
            </div>

            <div class="feature-card reveal delay-1" style="--accent:#7c3aed;--accent2:#ec4899;">
                <div class="icon">C</div>
                <h3>Class Scheduling</h3>
                <p>
                    Organise daily classes, sessions, instructors, capacity and student bookings
                    without scattered spreadsheets or manual tracking.
                </p>
            </div>

            <div class="feature-card reveal delay-2" style="--accent:#f97316;--accent2:#facc15;">
                <div class="icon">P</div>
                <h3>Plans & Packages</h3>
                <p>
                    Create memberships, session packages and class cards so institutes can sell
                    flexible learning access to different student groups.
                </p>
            </div>

            <div class="feature-card reveal" style="--accent:#10b981;--accent2:#06b6d4;">
                <div class="icon">A</div>
                <h3>Attendance Tracking</h3>
                <p>
                    Track class attendance, plan attendance and class card usage with a workflow
                    that helps teachers and admins stay aligned.
                </p>
            </div>

            <div class="feature-card reveal delay-1" style="--accent:#ec4899;--accent2:#7c3aed;">
                <div class="icon">₹</div>
                <h3>Payments & Checkout</h3>
                <p>
                    Support smoother checkout, payment records and purchase history so finance
                    teams have better visibility over student transactions.
                </p>
            </div>

            <div class="feature-card reveal delay-2" style="--accent:#0ea5e9;--accent2:#2563eb;">
                <div class="icon">N</div>
                <h3>Notifications</h3>
                <p>
                    Keep users informed with app notifications for updates, reminders, classes
                    and important operational announcements.
                </p>
            </div>
        </div>
    </section>

    <section id="workflow" class="wrap section">
        <div class="split">
            <div class="panel color-panel reveal">
                <div class="section-kicker" style="color:rgba(255,255,255,.78);">Daily Workflow</div>
                <h2 style="margin:10px 0 14px;">From enquiry to loyal student.</h2>
                <p style="line-height:1.75;">
                    A strong LMS should not only store data. It should guide your team through the full
                    operating flow — student onboarding, package assignment, class scheduling,
                    attendance, renewals and reporting.
                </p>
            </div>

            <div class="workflow reveal delay-1">
                <div class="step">
                    <div class="step-no">01</div>
                    <div>
                        <h3>Register or create the student</h3>
                        <p>Admins can create student records and prepare the right plan or class card access.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-no">02</div>
                    <div>
                        <h3>Assign plans, packages or class cards</h3>
                        <p>Students can be connected to the right product based on how the institute sells access.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-no">03</div>
                    <div>
                        <h3>Schedule and manage sessions</h3>
                        <p>Teachers, classes and sessions stay organised so the daily timetable is easier to run.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-no">04</div>
                    <div>
                        <h3>Track attendance and progress</h3>
                        <p>Attendance records help admins understand student activity and package usage.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-no">05</div>
                    <div>
                        <h3>Review payments and operations</h3>
                        <p>The back office gets a clearer view of purchase history, active users and daily movement.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="roles" class="wrap section">
        <div class="section-head reveal">
            <div class="section-kicker">Role-Based Experience</div>
            <h2>Built for admins, teachers and students.</h2>
            <p class="section-lead">
                A serious institute platform should feel different for every user type.
                Each role gets the tools they need without unnecessary clutter.
            </p>
        </div>

        <div class="roles">
            <div class="role-card reveal">
                <h3>Admin Portal</h3>
                <p style="color:#667085;line-height:1.65;margin:0;">
                    Full operational control for the institute.
                </p>
                <ul>
                    <li>Manage students, teachers and admins</li>
                    <li>Create classes, plans and sessions</li>
                    <li>Track purchases and payment history</li>
                    <li>Review attendance and daily activity</li>
                </ul>
            </div>

            <div class="role-card reveal delay-1">
                <h3>Teacher Portal</h3>
                <p style="color:#667085;line-height:1.65;margin:0;">
                    Clean tools for instructors and class delivery.
                </p>
                <ul>
                    <li>View assigned classes and schedules</li>
                    <li>Manage class attendance</li>
                    <li>Check student participation</li>
                    <li>Stay aligned with admin planning</li>
                </ul>
            </div>

            <div class="role-card reveal delay-2">
                <h3>Student Portal</h3>
                <p style="color:#667085;line-height:1.65;margin:0;">
                    A simple experience for learners.
                </p>
                <ul>
                    <li>View dashboard and schedule</li>
                    <li>Track attendance history</li>
                    <li>Access payment information</li>
                    <li>Stay updated with notifications</li>
                </ul>
            </div>
        </div>
    </section>

    <section id="analytics" class="wrap section">
        <div class="section-head reveal">
            <div class="section-kicker">Operational Intelligence</div>
            <h2>Make better decisions with cleaner data.</h2>
            <p class="section-lead">
                The platform should give institutes confidence by showing useful information clearly:
                what is active, what is upcoming, who attended, who paid and where operations need attention.
            </p>
        </div>

        <div class="analytics-grid">
            <div class="analytics-card reveal">
                <h3>Student activity visibility</h3>
                <p>
                    Understand which students are active, what they are enrolled in and how they are engaging
                    with classes or packages.
                </p>
            </div>

            <div class="analytics-card reveal delay-1">
                <h3>Class and capacity overview</h3>
                <p>
                    See class movement more clearly so admins can manage schedules, instructors and availability
                    with better planning.
                </p>
            </div>

            <div class="analytics-card reveal">
                <h3>Payment and purchase clarity</h3>
                <p>
                    Keep purchase history and payment tracking accessible so the finance side of the institute
                    does not become disconnected from operations.
                </p>
            </div>

            <div class="analytics-card reveal delay-1">
                <h3>Attendance-driven operations</h3>
                <p>
                    Attendance records help the institute understand usage, student commitment and class performance.
                </p>
            </div>
        </div>
    </section>

    <section id="security" class="wrap section">
        <div class="section-head reveal">
            <div class="section-kicker">Production Mindset</div>
            <h2>Designed to feel reliable for bigger institutes.</h2>
            <p class="section-lead">
                The landing page should communicate confidence. Bigger institutes care about access control,
                organised workflows, clean admin design and a platform that can continue growing.
            </p>
        </div>

        <div class="security-strip">
            <div class="security-item reveal">Role-based dashboards</div>
            <div class="security-item reveal delay-1">Cleaner admin operations</div>
            <div class="security-item reveal delay-2">Structured data flow</div>
            <div class="security-item reveal delay-3">Responsive experience</div>
        </div>
    </section>

    <section class="wrap cta">
        <div class="cta-box reveal">
            <h2>Turn your institute into a modern digital learning operation.</h2>
            <p>
                Mueble Studio helps education teams move beyond manual admin work and into a more connected,
                professional and scalable system for students, teachers, classes and growth.
            </p>

            <div class="hero-actions" style="margin-top:0;">
                <a class="btn" href="/register">Create Account</a>
                <a class="btn" href="/login" style="background:rgba(255,255,255,.16);color:#fff;border:1px solid rgba(255,255,255,.32);">
                    Login to Dashboard
                </a>
            </div>
        </div>
    </section>

    <footer class="wrap footer">
        <div class="footer-inner">
            <strong>Mueble Studio</strong>
            <span>Modern LMS and studio management platform for growing institutes.</span>
        </div>
    </footer>
</main>