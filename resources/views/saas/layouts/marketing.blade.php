<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="{{ $metaDescription ?? 'Mueble Studio is a complete institute and studio management platform for students, teachers, classes, attendance, subscriptions and payments.' }}">
    <title>{{ $title ?? 'Mueble Studio' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{--ink:#101828;--muted:#667085;--line:rgba(16,24,40,.11);--blue:#2563eb;--cyan:#06b6d4;--purple:#7c3aed;--pink:#ec4899;--orange:#f97316;--green:#10b981;--soft:#f8fbff;--shadow:0 24px 70px rgba(15,23,42,.10)}
        *,*::before,*::after{box-sizing:border-box}html{height:auto;min-height:100%;scroll-behavior:smooth;overflow-x:hidden;overflow-y:auto}body{min-height:100%;height:auto!important;margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:var(--ink);background:radial-gradient(circle at 8% 3%,rgba(37,99,235,.12),transparent 28%),radial-gradient(circle at 92% 8%,rgba(236,72,153,.10),transparent 27%),linear-gradient(180deg,#fff 0%,#f8fbff 48%,#fff 100%);overflow-x:hidden!important;overflow-y:auto!important;overscroll-behavior-y:auto;-webkit-overflow-scrolling:touch}a{color:inherit}.page{width:100%;min-height:100vh;height:auto;overflow:visible}.wrap{width:min(1180px,calc(100% - 40px));margin-inline:auto}.nav{position:sticky;top:14px;z-index:60;margin-top:14px;padding:13px 16px;display:flex;align-items:center;justify-content:space-between;gap:20px;border:1px solid rgba(255,255,255,.84);border-radius:999px;background:rgba(255,255,255,.88);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);box-shadow:0 12px 35px rgba(15,23,42,.08)}.brand{display:flex;align-items:center;gap:10px;min-width:0;text-decoration:none;font-weight:950;letter-spacing:-.035em}.brand-mark{flex:0 0 auto;width:38px;height:38px;border-radius:14px;background:linear-gradient(135deg,var(--blue),var(--cyan),var(--pink));box-shadow:0 12px 30px rgba(37,99,235,.28)}.brand span:last-child{white-space:nowrap}.nav-links{display:flex;align-items:center;gap:20px;font-size:14px;font-weight:800;color:#344054}.nav-links a{text-decoration:none;white-space:nowrap}.nav-links a:hover{color:var(--blue)}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:46px;padding:12px 20px;border:0;border-radius:999px;text-decoration:none;font-weight:900;transition:.22s ease;cursor:pointer}.btn:hover{transform:translateY(-2px)}.btn-primary{color:#fff;background:linear-gradient(135deg,var(--blue),var(--purple),var(--pink));box-shadow:0 18px 35px rgba(124,58,237,.24)}.btn-soft{color:#1d4ed8;background:#eff6ff;border:1px solid rgba(37,99,235,.14)}.mobile-menu{display:none;position:relative}.mobile-menu summary{list-style:none;width:44px;height:44px;display:grid;place-items:center;border:1px solid var(--line);border-radius:14px;background:#fff;color:var(--ink);font-size:22px;font-weight:900;cursor:pointer}.mobile-menu summary::-webkit-details-marker{display:none}.mobile-menu-panel{position:absolute;top:54px;right:0;width:min(310px,calc(100vw - 28px));padding:12px;border:1px solid var(--line);border-radius:22px;background:rgba(255,255,255,.98);box-shadow:0 20px 50px rgba(15,23,42,.16)}.mobile-menu-panel a{display:flex;align-items:center;min-height:46px;padding:11px 14px;border-radius:14px;text-decoration:none;font-weight:850}.mobile-menu-panel a:hover{background:#eff6ff;color:var(--blue)}.mobile-menu-panel .btn{margin-top:8px;width:100%;color:#fff}.hero{padding:92px 0 74px}.hero-grid{display:grid;grid-template-columns:minmax(0,1.04fr) minmax(0,.96fr);gap:46px;align-items:center}.hero-grid>*{min-width:0}.eyebrow{display:inline-flex;align-items:center;gap:9px;max-width:100%;padding:9px 14px;border-radius:999px;border:1px solid rgba(37,99,235,.15);background:rgba(255,255,255,.82);box-shadow:0 12px 28px rgba(15,23,42,.07);color:#1d4ed8;font-size:13px;font-weight:900}.dot{flex:0 0 auto;width:9px;height:9px;border-radius:999px;background:var(--green);box-shadow:0 0 0 7px rgba(16,185,129,.12)}h1,h2,h3{overflow-wrap:anywhere;letter-spacing:-.04em}.hero h1{margin:22px 0 18px;font-size:clamp(42px,6vw,78px);line-height:.98;letter-spacing:-.065em}.gradient{background:linear-gradient(135deg,var(--blue),var(--purple),var(--pink),var(--orange));background-clip:text;-webkit-background-clip:text;color:transparent}.lead{margin:0;color:var(--muted);font-size:19px;line-height:1.75}.actions{display:flex;flex-wrap:wrap;gap:13px;margin-top:28px}.hero-panel,.card,.panel{border:1px solid var(--line);background:rgba(255,255,255,.86);box-shadow:var(--shadow)}.hero-panel{min-width:0;padding:24px;border-radius:34px}.hero-panel h3{margin:0 0 8px;font-size:24px}.hero-panel p{margin:0;color:var(--muted);line-height:1.7}.metric-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:18px}.metric{min-width:0;padding:17px;border-radius:20px;background:linear-gradient(135deg,#eff6ff,#faf5ff);border:1px solid rgba(37,99,235,.09)}.metric strong{display:block;font-size:25px}.metric span{font-size:12px;color:var(--muted);font-weight:800}.section{padding:76px 0}.section-head{max-width:790px;margin-bottom:34px}.kicker{color:var(--blue);font-size:12px;font-weight:950;letter-spacing:.13em;text-transform:uppercase}.section h2,.page-head h1{margin:10px 0 14px;font-size:clamp(34px,4.7vw,58px);line-height:1.02}.section-copy{margin:0;color:var(--muted);font-size:18px;line-height:1.75}.grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.card{min-width:0;padding:25px;border-radius:27px}.card-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:16px;background:linear-gradient(135deg,var(--blue),var(--cyan));color:#fff;font-weight:950}.card h3{margin:18px 0 9px;font-size:22px}.card p{margin:0;color:var(--muted);line-height:1.7}.card ul,.check-list{margin:16px 0 0;padding:0;list-style:none;display:grid;gap:10px;color:#475467}.card li,.check-list li{position:relative;padding-left:25px;line-height:1.55}.card li:before,.check-list li:before{content:'✓';position:absolute;left:0;color:var(--green);font-weight:950}.page-head{padding:82px 0 44px;max-width:850px}.subnav{display:flex;flex-wrap:wrap;gap:10px;margin-top:25px}.pill{max-width:100%;padding:9px 13px;border-radius:999px;background:#fff;border:1px solid var(--line);font-size:13px;font-weight:850;color:#475467}.band{min-width:0;padding:34px;border-radius:34px;background:linear-gradient(135deg,#111827,#1d4ed8,#7c3aed);color:#fff}.band p{color:rgba(255,255,255,.78);line-height:1.75}.steps{display:grid;gap:14px}.step{display:grid;grid-template-columns:58px minmax(0,1fr);gap:16px;padding:21px;border:1px solid var(--line);border-radius:23px;background:#fff}.step-no{width:48px;height:48px;display:grid;place-items:center;border-radius:16px;background:linear-gradient(135deg,var(--blue),var(--purple));color:#fff;font-weight:950}.step h3{margin:2px 0 6px}.step p{margin:0;color:var(--muted);line-height:1.65}.pricing{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.price-card{position:relative;min-width:0;padding:28px;border-radius:28px;border:1px solid var(--line);background:#fff;box-shadow:0 18px 48px rgba(15,23,42,.08)}.price-card.featured{border:2px solid #7c3aed;transform:translateY(-8px)}.price-card h3{font-size:24px;margin:0}.price{margin:16px 0;font-size:40px;font-weight:950;letter-spacing:-.06em}.price small{font-size:14px;color:var(--muted);letter-spacing:0}.notice{padding:18px 20px;border-radius:20px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;line-height:1.65}.faq{display:grid;gap:13px}.faq details{padding:20px 22px;border:1px solid var(--line);border-radius:20px;background:#fff}.faq summary{cursor:pointer;font-weight:900}.faq p{color:var(--muted);line-height:1.7}.cta{padding:72px 0}.cta-box{padding:48px;border-radius:36px;background:linear-gradient(135deg,#111827,#1d4ed8,#7c3aed,#ec4899);color:#fff}.cta-box h2{margin:0 0 12px;font-size:clamp(34px,4.5vw,58px);line-height:1}.cta-box p{max-width:760px;color:rgba(255,255,255,.78);font-size:18px;line-height:1.7}.footer{padding:22px 0 34px}.footer-inner{display:flex;justify-content:space-between;gap:20px;padding-top:24px;border-top:1px solid var(--line);color:var(--muted);font-size:14px}.footer-links{display:flex;flex-wrap:wrap;gap:16px}.footer-links a{text-decoration:none}
        @media(max-width:1000px){.nav-links{gap:12px}.nav-links>a:not(.btn){display:none}.mobile-menu{display:block}.nav-links .btn{display:none}.hero-grid{grid-template-columns:minmax(0,1fr) minmax(300px,.8fr);gap:28px}.grid-3,.pricing{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:760px){.wrap{width:min(100% - 28px,1180px)}.nav{top:8px;margin-top:8px;padding:10px 12px;border-radius:22px}.brand-mark{width:34px;height:34px;border-radius:12px}.hero{padding:52px 0 48px}.hero-grid,.grid-3,.grid-2,.pricing,.metric-grid{grid-template-columns:1fr}.hero h1{font-size:clamp(38px,12vw,56px)}.lead,.section-copy,.cta-box p{font-size:16px;line-height:1.7}.actions{display:grid;grid-template-columns:1fr}.btn{width:100%}.hero-panel,.card,.price-card{padding:21px;border-radius:24px}.section{padding:50px 0}.section-head{margin-bottom:24px}.page-head{padding:54px 0 28px}.page-head h1,.section h2{font-size:clamp(32px,10vw,46px)}.band,.cta-box{padding:26px;border-radius:27px}.step{grid-template-columns:48px minmax(0,1fr);gap:12px;padding:17px}.step-no{width:42px;height:42px;border-radius:13px}.price-card.featured{transform:none}.cta{padding:48px 0}.footer-inner{display:grid}.footer-links{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px 16px}}
        @media(max-width:420px){.wrap{width:min(100% - 22px,1180px)}.brand span:last-child{font-size:15px}.eyebrow{align-items:flex-start;border-radius:18px}.hero h1{font-size:38px}.hero-panel,.card,.price-card{padding:18px}.metric{padding:15px}.footer-links{grid-template-columns:1fr}.subnav{display:grid}.pill{text-align:center}.mobile-menu-panel{right:-2px}}
    </style>
</head>
<body>
<div class="page">
    <header class="wrap nav">
        <a href="{{ url('/') }}" class="brand"><span class="brand-mark"></span><span>Mueble Studio</span></a>

        <nav class="nav-links" aria-label="Main navigation">
            <a href="{{ route('marketing.features') }}">Features</a>
            <a href="{{ route('marketing.solutions') }}">Who it’s for</a>
            <a href="{{ route('marketing.platform') }}">How it works</a>
            <a href="{{ route('marketing.security') }}">Security</a>
            <a href="{{ route('marketing.pricing') }}">Pricing</a>
            <a class="btn btn-primary" href="{{ route('login') }}">Login</a>
        </nav>

        <details class="mobile-menu">
            <summary aria-label="Open navigation menu">☰</summary>
            <nav class="mobile-menu-panel" aria-label="Mobile navigation">
                <a href="{{ route('marketing.features') }}">Features</a>
                <a href="{{ route('marketing.solutions') }}">Who it’s for</a>
                <a href="{{ route('marketing.platform') }}">How it works</a>
                <a href="{{ route('marketing.security') }}">Security</a>
                <a href="{{ route('marketing.pricing') }}">Pricing</a>
                <a class="btn btn-primary" href="{{ route('login') }}">Login</a>
            </nav>
        </details>
    </header>

    @yield('content')

    <section class="wrap cta">
        <div class="cta-box">
            <h2>Run your institute from one connected platform.</h2>
            <p>Replace scattered spreadsheets, chats and manual payment tracking with a structured system for students, staff, schedules, attendance, subscriptions and growth.</p>
            <div class="actions">
                <a class="btn" style="background:#fff;color:#1d4ed8" href="{{ route('register') }}">Create your studio</a>
                <a class="btn" style="background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.28)" href="{{ route('marketing.features') }}">Explore all features</a>
            </div>
        </div>
    </section>

    <footer class="wrap footer">
        <div class="footer-inner">
            <div><strong style="color:#101828">Mueble Studio</strong><br>Institute, academy and studio management in one platform.</div>
            <div class="footer-links">
                <a href="{{ route('marketing.features') }}">Features</a>
                <a href="{{ route('marketing.solutions') }}">Solutions</a>
                <a href="{{ route('marketing.security') }}">Security</a>
                <a href="{{ route('marketing.pricing') }}">Pricing</a>
                <a href="{{ route('login') }}">Login</a>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
