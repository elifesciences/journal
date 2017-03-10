<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Helper\HasPages;
use eLife\Journal\Helper\Paginator;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ListingTeasers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AnnualReportsController extends Controller
{
    use HasPages;

    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $annualReports = $this->pagerfantaPromise(
            $this->get('elife.api_sdk.annual_reports'),
            $page,
            $perPage
        );

        $arguments['title'] = 'Annual reports';

        $arguments['paginator'] = $this->paginator(
            $annualReports,
            $request,
            'Browse our annual reports',
            'annual-reports'
        );

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['type' => 'annual reports']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['title']);

        return new Response($this->get('templating')->render('::annual-reports.html.twig', $arguments));
    }
}
