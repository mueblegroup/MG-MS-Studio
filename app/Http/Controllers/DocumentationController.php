<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    public function index(Request $request, ?string $page = null): View
    {
        $sections = config('docs.sections', []);
        $pages = collect($sections)->flatMap(fn (array $section) => $section['pages'] ?? []);
        $page = $page ?: 'getting-started';

        abort_unless($pages->has($page), 404);

        return view('docs.index', [
            'sections' => $sections,
            'currentSlug' => $page,
            'currentPage' => $pages->get($page),
            'viewerRole' => auth()->user()?->role,
        ]);
    }
}
