<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchEngineVisibility
{
    /**
     * Public pages that search engines are allowed to index on the central ClassM8 domain.
     */
    private const INDEXABLE_PATHS = [
        '',
        'features',
        'solutions',
        'platform',
        'security',
        'pricing',
    ];

    /**
     * Search-focused titles and descriptions for the public marketing pages.
     */
    private const PAGE_META = [
        '' => [
            'title' => 'ClassM8 | Studio & Class Management Software by Mueble Group',
            'description' => 'ClassM8 is an all-in-one studio and class management platform for class cards, schedules, students, teachers, attendance, subscriptions and payments.',
        ],
        'features' => [
            'title' => 'ClassM8 Features | Class Cards, Scheduling, Attendance & Payments',
            'description' => 'Explore ClassM8 features for studios and academies, including class cards, recurring plans, individual classes, student portals, schedules, attendance and payments.',
        ],
        'solutions' => [
            'title' => 'ClassM8 Solutions | Management Software for Studios & Academies',
            'description' => 'See how ClassM8 supports dance studios, tuition centres, music schools, fitness businesses, academies and other class-based organisations.',
        ],
        'platform' => [
            'title' => 'How ClassM8 Works | Studio & Class Management Platform',
            'description' => 'See how ClassM8 connects online class sales, subscriptions, class cards, scheduling, teachers, students, attendance and payments in one platform.',
        ],
        'security' => [
            'title' => 'ClassM8 Security | Secure Studio Management Software',
            'description' => 'Learn how ClassM8 protects studio and student operations with role-based access, secure authentication and platform security controls.',
        ],
        'pricing' => [
            'title' => 'ClassM8 Pricing | Studio & Class Management Software Plans',
            'description' => 'View ClassM8 pricing for studios, academies and class-based businesses that need scheduling, class cards, student management, attendance and payments.',
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $path = trim($request->path(), '/');
        $isCentralHost = $this->isCentralHost($request);
        $isIndexable = $isCentralHost
            && in_array($request->method(), ['GET', 'HEAD'], true)
            && in_array($path, self::INDEXABLE_PATHS, true);

        if (! $isIndexable) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

            return $response;
        }

        $response->headers->set(
            'X-Robots-Tag',
            'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'
        );

        $canonical = $this->canonicalUrl($path);
        $response->headers->set('Link', '<' . $canonical . '>; rel="canonical"', false);

        $this->enhanceHtmlResponse($response, $path, $canonical);

        return $response;
    }

    private function isCentralHost(Request $request): bool
    {
        $host = strtolower($request->getHost());
        $centralDomains = array_values(array_unique(array_filter(array_map(
            static fn ($domain) => strtolower(trim((string) $domain)),
            config('saas.central_domains', [])
        ))));

        return in_array($host, $centralDomains, true);
    }

    private function canonicalUrl(string $path): string
    {
        $base = rtrim((string) config('app.url'), '/');

        if ($base === '') {
            $centralDomain = collect(config('saas.central_domains', []))->first();
            $base = $centralDomain ? 'https://' . $centralDomain : url('/');
        }

        return $base . ($path === '' ? '/' : '/' . $path);
    }

    private function enhanceHtmlResponse(Response $response, string $path, string $canonical): void
    {
        $contentType = (string) $response->headers->get('Content-Type');

        if (! str_contains(strtolower($contentType), 'text/html') || ! method_exists($response, 'getContent') || ! method_exists($response, 'setContent')) {
            return;
        }

        $content = $response->getContent();

        if (! is_string($content) || ! str_contains(strtolower($content), '</head>')) {
            return;
        }

        $meta = self::PAGE_META[$path] ?? self::PAGE_META[''];
        $title = $meta['title'];
        $description = $meta['description'];
        $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $escapedDescription = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        $escapedCanonical = htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8');

        $content = preg_replace(
            '/<title>.*?<\/title>/is',
            '<title>' . $escapedTitle . '</title>',
            $content,
            1
        ) ?? $content;

        $content = preg_replace(
            '/<meta\s+name=["\']description["\']\s+content=["\'].*?["\']\s*\/?\s*>/is',
            '<meta name="description" content="' . $escapedDescription . '">',
            $content,
            1
        ) ?? $content;

        $tags = [
            '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">',
            '<link rel="canonical" href="' . $escapedCanonical . '">',
            '<meta property="og:type" content="website">',
            '<meta property="og:site_name" content="ClassM8">',
            '<meta property="og:title" content="' . $escapedTitle . '">',
            '<meta property="og:description" content="' . $escapedDescription . '">',
            '<meta property="og:url" content="' . $escapedCanonical . '">',
            '<meta name="twitter:card" content="summary_large_image">',
            '<meta name="twitter:title" content="' . $escapedTitle . '">',
            '<meta name="twitter:description" content="' . $escapedDescription . '">',
        ];

        if ($path === '') {
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareApplication',
                'name' => 'ClassM8',
                'url' => $canonical,
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'Web',
                'description' => $description,
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'Mueble Group',
                    'url' => 'https://mueblegroup.com/',
                ],
            ];

            $tags[] = '<script type="application/ld+json">'
                . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                . '</script>';
        }

        $content = str_ireplace('</head>', implode("\n    ", $tags) . "\n</head>", $content);
        $response->setContent($content);
    }
}
