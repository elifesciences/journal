<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Subject;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\Paragraph;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LocalPageStubController extends Controller
{
    public function localPageStubAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Local Page Stub';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        $arguments['body'] = [];

        $arguments['body'][] = new Paragraph('This is a page forwarded to other projects in production, it cannot be displayed in local development.');

        return new Response($this->get('templating')->render('::local-page-stub.html.twig', $arguments));
    }
}
