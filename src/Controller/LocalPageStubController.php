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

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'Local page stub.');

        $arguments['body'] = new Paragraph('eLife is also on <a href="https://www.linkedin.com/company/elife-sciences-publications-ltd">LinkedIn</a> and <a href="https://www.youtube.com/channel/UCNEHLtAc_JPI84xW8V4XWyw">YouTube</a>.');

        return new Response($this->get('templating')->render('::alerts.html.twig', $arguments));
    }
}
