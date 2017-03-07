<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class AnnualReportsController extends Controller
{
    public function listAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['title'] = 'Annual reports';

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['title']);

        return new Response($this->get('templating')->render('::annual-reports.html.twig', $arguments));
    }
}
