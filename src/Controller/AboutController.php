<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class AboutController extends Controller
{
    public function aboutAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['title'] = 'About';

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('About eLife', false,
            'Pain-free publishing for your best science.');

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }
}
