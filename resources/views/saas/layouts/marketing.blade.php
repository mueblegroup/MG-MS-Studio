<!DOCTYPE html>
<html lang="en" class="marketing-document">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $metaDescription ?? 'Mueble Studio is a complete institute and studio management platform for students, teachers, classes, attendance, subscriptions and payments.' }}">
    <title>{{ $title ?? 'Mueble Studio' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{--ink:#101828;--muted:#667085;--line:rgba(16,24,40,.11);--blue:#2563eb;--cyan:#06b6d4;--purple:#7c3aed;--pink:#ec4899;--orange:#f97316;--green:#10b981;--soft:#f8fbff;--shadow:0 24px 70px rgba(15,23,42,.10)}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:var(--ink);background:radial-gradient(circle at 8% 3%,rgba(37,99,235,.12),transparent 28%),radial-gradient(circle at 92% 8%,rgba(236,72,153,.10),transparent 27%),linear-gradient(180deg,#fff 0%,#f8fbff 48%,#fff 100%);overflow-x:hidden}a{color:inherit}.page{min-height:100vh}.wrap{width:min(1180px,calc(100% - 40px));margin:0 auto}.nav{position:sticky;top:14px;z-index:60;margin-top:14px;padding:13px 16px;display:flex;align-items:center;justify-content:space-between;gap:20px;border:1px solid rgba(255,255,255,.84);border-radius:999px;background:rgba(255,255,255,.82);backdrop-filter:blur(18px);box-shadow:0 12px 35px rgba(15,23,42,.08)}.brand{display:flex;align-items:center;gap:10px;text-decoration:none;font-weight:950;letter-spacing:-.035em}.brand-mark{width:38px;height:38px;border-radius:14px;background:linear-gradient(135deg,var(--blue),var(--cyan),var(--pink));box-shadow:0 12px 30px rgba(37,99,235,.28)}.nav-links{display:flex;align-items:center;gap:20px;font-size:14px;font-weight:800;color:#344054}.nav-links a{text-decoration:none}.nav-links a:hover{color:var(--blue)}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:46px;padding:12px 20px;border:0;border-radius:999px;text-decoration:none;font-weight:900;transition:.22s ease;cursor:pointer}.btn:hover{transform:translateY(-2px)}.btn-primary{color:#fff;background:linear-gradient(135deg,var(--blue),var(--purple),var(--pink));box-shadow:0 18px 35px rgba(124,58,237,.24)}.btn-soft{color:#1d4ed8;background:#eff6ff;border:1px solid rgba(37,99,235,.14)}.hero{padding:92px 0 74px}.hero-grid{display:grid;grid-template-columns:1.04fr .96fr;gap:46px;align-items:center}.eyebrow{display:inline-flex;align-items:center;gap:9px;padding:9px 14px;border-radius:999px;border:1px solid rgba(37,99,235,.15);background:rgba(255,255,255,.82);box-shadow:0 12px 28px rgba(15,23,42,.07);color:#1d4ed8;font-size:13px;font-weight:900}.dot{width:9px;height:9px;border-radius:999px;background:var(--green);box-shadow:0 0 0 7px rgba(16,185,129,.12)}h1,h2,h3{letter-spacing:-.04em}.hero h1{margin:22px 0 18px;font-size:clamp(44px,6vw,78px);line-height:.98;letter-spacing:-.065em}.gradient{background:linear-gradient(135deg,var(--blue),var(--purple),var(--pink),var(--orange));background-clip:text;-webkit-background-clip:text;color:transparent}.lead{margin:0;color:var(--muted);font-size:19px;line-height:1.75}.actions{display:flex;flex-wrap:wrap;gap:13px;margin-top:28px}.hero-panel,.card,.panel{border:1px solid var(--line);background:rgba(255,255,255,.86);box-shadow:var(--shadow)}.hero-panel{padding:24px;border-radius:34px}.hero-panel h3{margin:0 0 8px;font-size:24px}.hero-panel p{margin:0;color:var(--muted);line-height:1.7}.metric-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:18px}.metric{padding:17px;border-radius:20px;background:linear-gradient(135deg,#eff6ff,#faf5ff);border:1px solid rgba(37,99,235,.09)}.metric strong{display:block;font-size:25px}.metric span{font-size:12px;color:var(--muted);font-weight:800}.section{padding:76px 0}.section-head{max-width:790px;margin-bottom:34px}.kicker{color:var(--blue);font-size:12px;font-weight:950;letter-spacing:.13em;text-transform:uppercase}.section h2,.page-head h1{margin:10px 0 14px;font-size:clamp(34px,4.7vw,58px);line-height:1.02}.section-copy{margin:0;color:var(--muted);font-size:18px;line-height:1.75}.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}.card{padding:25px;border-radius:27px}.card-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:16px;background:linear-gradient(135deg,var(--blue),var(--cyan));color:#fff;font-weight:950}.card h3{margin:18px 0 9px;font-size:22px}.card p{margin:0;color:var(--muted);line-height:1.7}.card ul,.check-list{margin:16px 0 0;padding:0;list-style:none;display:grid;gap:10px;color:#475467}.card li,.check-list li{position:relative;padding-left:25px;line-height:1.55}.card li:before,.check-list li:before{content:'✓';position:absolute;left:0;color:var(--green);font-weight:950}.page-head{padding:82px 0 44px;max-width:850px}.subnav{display:flex;flex-wrap:wrap;gap:10px;margin-top:25px}.pill{padding:9px 13px;border-radius:999px;background:#fff;border:1px solid var(--line);font-size:13px;font-weight:850;color:#475467}.band{padding:34px;border-radius:34px;background:linear-gradient(135deg,#111827,#1d4ed8,#7c3aed);color:#fff}.band p{color:rgba(255,255,255,.78);line-height:1.75}.steps{display:grid;gap:14px}.step{display:grid;grid-template-columns:58px 1fr;gap:16px;padding:21px;border:1px solid var(--line);border-radius:23px;background:#fff}.step-no{width:48px;height:48px;display:grid;place-items:center;border-radius:16px;background:linear-gradient(135deg,var(--blue),var(--purple));color:#fff;font-weight:950}.step h3{margin:2px 0 6px}.step p{margin:0;color:var(--muted);line-height:1.65}.pricing{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.price-card{position:relative;padding:28px;border-radius:28px;border:1px solid var(--line);background:#fff;box-shadow:0 18px 48px rgba(15,23,42,.08)}.price-card.featured{border:2px solid #7c3aed;transform:translateY(-8px)}.price-card h3{font-size:24px;margin:0}.price{margin:16px 0;font-size:40px;font-weight:950;letter-spacing:-.06em}.price small{font-size:14px;color:var(--muted);letter-spacing:0}.notice{padding:18px 20px;border-radius:20px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;line-height:1.65}.faq{display:grid;gap:13px}.faq details{padding:20px 22px;border:1px solid var(--line);border-radius:20px;background:#fff}.faq summary{cursor:pointer;font-weight:900}.faq p{color:var(--muted);line-height:1.7}.cta{padding:72px 0}.cta-box{padding:48px;border-radius:36px;background:linear-gradient(135deg,#111827,#1d4ed8,#7c3aed,#ec4899);color:#fff}.cta-box h2{margin:0 0 12px;font-size:clamp(34px,4.5vw,58px);line-height:1}.cta-box p{max-width:760px;color:rgba(255,255,255,.78);font-size:18px;line-height:1.7}.footer{padding:22px 0 34px}.footer-inner{display:flex;justify-content:space-between;gap:20px;padding-top:24px;border-top:1px solid var(--line);color:var(--muted);font-size:14px}.footer-links{display:flex;flex-wrap:wrap;gap:16px}.footer-links a{text-decoration:none}.mobile-menu{display:none}
        @media(max-width:920px){.hero-grid,.grid-3,.pricing{grid-template-columns:1fr 1fr}.nav-links{gap:12px}.nav-links>a:not(.btn){display:none}}
        @media(max-width:700px){.wrap{width:min(100% - 28px,1180px)}.nav{border-radius:25px}.hero{padding-top:58px}.hero-grid,.grid-3,.grid-2,.pricing,.metric-grid{grid-template-columns:1fr}.actions{display:grid}.btn{width:100%}.section{padding:56px 0}.price-card.featured{transform:none}.footer-inner{display:grid}.page-head{padding-top:58px}}
    </style>
</head>
<body class="marketing-page">
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
                <a href="{{ route('marketing.features') }}">Features</a><a href="{{ route('marketing.solutions') }}">Solutions</a><a href="{{ route('marketing.security') }}">Security</a><a href="{{ route('marketing.pricing') }}">Pricing</a><a href="{{ route('login') }}">Login</a>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
