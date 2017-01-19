<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class SearchController extends Controller
{
    public function queryAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['title'] = 'Search';

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Search');

        return new Response($this->get('templating')->render('::search.html.twig', $arguments));
    }
}
