<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    public function index(Request $request, ?string $page = null): View
    {
        $sections = config('docs.sections', []);
        $sections = $this->addUpcomingPaymentGuidance($sections);

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

    protected function addUpcomingPaymentGuidance(array $sections): array
    {
        $adminPage =& $sections['admin']['pages']['payments-admin'];
        if (is_array($adminPage)) {
            $adminPage['steps'][] = 'The top of the Payments page shows active subscriptions with a next billing date within three days as Due today, Due tomorrow, or Due in 2–3 days.';
            $adminPage['tips'][] = 'A due-soon subscription is a billing forecast. It becomes a payment record only when the provider creates or confirms the charge.';
        }

        $studentPage =& $sections['student']['pages']['student-payments'];
        if (is_array($studentPage)) {
            $studentPage['steps'][] = 'The Payments page shows upcoming subscription charges within the next three days above the transaction history.';
            $studentPage['tips'][] = 'Upcoming dues are not receipts and do not mean the payment has failed. The transaction appears below after the provider processes it.';
        }

        return $sections;
    }
}
