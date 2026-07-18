<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <title>{{ $currentPage['title'] }} · {{ config('app.name', 'Mueble LMS') }} Docs</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased lg:h-screen lg:overflow-hidden">
    @php
        $allPages = collect($sections)->flatMap(fn ($section) => $section['pages'] ?? []);
        $currentIndex = $allPages->keys()->search($currentSlug);
        $previousSlug = $currentIndex > 0 ? $allPages->keys()->get($currentIndex - 1) : null;
        $nextSlug = $currentIndex !== false && $currentIndex < $allPages->count() - 1 ? $allPages->keys()->get($currentIndex + 1) : null;
        $dashboardUrl = url('/');
        $docsSearchIndex = $allPages->map(function ($page, $slug) {
            return [
                'slug' => $slug,
                'title' => $page['title'],
                'summary' => $page['summary'],
                'role' => $page['role'],
                'url' => route('docs.show', $slug),
                'text' => strtolower(
                    $page['title'].' '.
                    $page['summary'].' '.
                    $page['role'].' '.
                    implode(' ', $page['steps'] ?? [])
                ),
            ];
        })->values()->all();
    @endphp

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-[1600px] items-center gap-4 px-4 sm:px-6">
            <a href="{{ route('docs.show', 'getting-started') }}" class="flex shrink-0 items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500 font-black text-white">M</span>
                <span class="hidden sm:block">
                    <span class="block text-sm font-black">Mueble LMS Docs</span>
                    <span class="block text-xs font-semibold text-slate-500">Complete user guide</span>
                </span>
            </a>

            <div class="relative mx-auto w-full max-w-xl">
                <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                <input id="docsSearch" type="search" placeholder="Search guides, roles, payments, classes..." class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm focus:border-orange-400 focus:ring-orange-400">
                <div id="searchResults" class="absolute left-0 right-0 top-12 hidden max-h-96 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl"></div>
            </div>

            <a href="{{ $dashboardUrl }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-black text-slate-600 hover:bg-slate-100 hover:text-slate-950">
                <i class="bx bx-left-arrow-alt text-lg"></i>
                <span class="hidden sm:inline">Back to App</span>
            </a>
        </div>
    </header>

    <div class="mx-auto max-w-[1600px] lg:grid lg:h-[calc(100vh-4rem)] lg:grid-cols-[300px_minmax(0,1fr)_220px] lg:overflow-hidden">
        <aside class="border-r border-slate-200 bg-white lg:h-full lg:overflow-y-auto">
            <div class="p-4 lg:p-5">
                @if($viewerRole)
                    <div class="mb-5 rounded-2xl bg-orange-50 p-4 text-sm text-orange-900 ring-1 ring-orange-100">
                        <p class="font-black">Signed in as {{ str($viewerRole)->headline() }}</p>
                        <p class="mt-1 text-xs font-semibold">All guides are visible. Start with your role section.</p>
                    </div>
                @endif

                <nav class="space-y-6" aria-label="Documentation">
                    @foreach($sections as $section)
                        <section>
                            <h2 class="mb-2 px-2 text-xs font-black uppercase tracking-[0.18em] text-slate-400">{{ $section['title'] }}</h2>
                            <div class="space-y-1">
                                @foreach($section['pages'] as $slug => $page)
                                    <a href="{{ route('docs.show', $slug) }}" data-doc-link data-title="{{ strtolower($page['title'].' '.$page['summary'].' '.$page['role']) }}" class="block rounded-xl px-3 py-2 text-sm font-bold transition {{ $currentSlug === $slug ? 'bg-slate-950 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">
                                        {{ $page['title'] }}
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </nav>
            </div>
        </aside>

        <main id="top" class="min-w-0 px-5 py-8 sm:px-8 lg:h-full lg:overflow-y-auto lg:scroll-smooth lg:px-12 lg:py-12">
            <article class="mx-auto max-w-4xl">
                <div class="mb-8">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-black uppercase tracking-wide text-orange-800">{{ $currentPage['role'] }}</span>
                        <span class="text-xs font-bold text-slate-400">Mueble LMS Guide</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-5xl">{{ $currentPage['title'] }}</h1>
                    <p class="mt-4 max-w-3xl text-base font-medium leading-7 text-slate-600 sm:text-lg">{{ $currentPage['summary'] }}</p>
                </div>

                <div id="steps" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="flex items-center gap-3 text-xl font-black text-slate-950">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-500 text-white"><i class="bx bx-list-check text-xl"></i></span>
                        Step-by-step
                    </h2>
                    <ol class="mt-6 space-y-5">
                        @foreach($currentPage['steps'] as $step)
                            <li class="flex gap-4">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-950 text-xs font-black text-white">{{ $loop->iteration }}</span>
                                <p class="pt-1 text-sm font-medium leading-7 text-slate-700 sm:text-base">{{ $step }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>

                @if(!empty($currentPage['tips']))
                    <div id="notes" class="mt-6 rounded-3xl border border-amber-200 bg-amber-50 p-6 sm:p-8">
                        <h2 class="flex items-center gap-2 text-lg font-black text-amber-950"><i class="bx bx-bulb text-2xl"></i> Important notes</h2>
                        <ul class="mt-4 space-y-3">
                            @foreach($currentPage['tips'] as $tip)
                                <li class="flex gap-3 text-sm font-semibold leading-6 text-amber-900"><span>•</span><span>{{ $tip }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-10 grid gap-4 pb-12 sm:grid-cols-2">
                    @if($previousSlug)
                        <a href="{{ route('docs.show', $previousSlug) }}" class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-orange-300 hover:shadow-md">
                            <span class="text-xs font-black uppercase tracking-wide text-slate-400">Previous</span>
                            <span class="mt-1 block font-black text-slate-900">← {{ $allPages->get($previousSlug)['title'] }}</span>
                        </a>
                    @else
                        <div></div>
                    @endif
                    @if($nextSlug)
                        <a href="{{ route('docs.show', $nextSlug) }}" class="rounded-2xl border border-slate-200 bg-white p-5 text-right transition hover:border-orange-300 hover:shadow-md">
                            <span class="text-xs font-black uppercase tracking-wide text-slate-400">Next</span>
                            <span class="mt-1 block font-black text-slate-900">{{ $allPages->get($nextSlug)['title'] }} →</span>
                        </a>
                    @endif
                </div>
            </article>
        </main>

        <aside class="hidden border-l border-slate-200 bg-white px-5 py-8 lg:block lg:h-full lg:overflow-y-auto">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">On this page</p>
            <nav class="mt-4 space-y-2 text-sm font-bold text-slate-600">
                <a href="#top" class="block hover:text-orange-600">Overview</a>
                <a href="#steps" class="block hover:text-orange-600">Step-by-step</a>
                @if(!empty($currentPage['tips']))<a href="#notes" class="block hover:text-orange-600">Important notes</a>@endif
            </nav>
            <div class="mt-8 rounded-2xl bg-slate-100 p-4 text-xs font-semibold leading-5 text-slate-600">
                Documentation is available to every user role and can be opened without signing in.
            </div>
        </aside>
    </div>

    <script>
        const docs = @json($docsSearchIndex);

        const input = document.getElementById('docsSearch');
        const results = document.getElementById('searchResults');

        input.addEventListener('input', () => {
            const query = input.value.trim().toLowerCase();
            if (query.length < 2) {
                results.classList.add('hidden');
                results.innerHTML = '';
                return;
            }

            const matches = docs.filter(page => page.text.includes(query)).slice(0, 10);
            results.innerHTML = matches.length
                ? matches.map(page => `<a href="${page.url}" class="block rounded-xl px-4 py-3 hover:bg-slate-100"><span class="block text-sm font-black text-slate-950">${page.title}</span><span class="mt-1 block text-xs font-semibold text-slate-500">${page.role} · ${page.summary}</span></a>`).join('')
                : '<p class="px-4 py-6 text-center text-sm font-semibold text-slate-500">No matching guide found.</p>';
            results.classList.remove('hidden');
        });

        document.addEventListener('click', event => {
            if (!results.contains(event.target) && event.target !== input) results.classList.add('hidden');
        });
    </script>
</body>
</html>
