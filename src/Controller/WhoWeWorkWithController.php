<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class WhoWeWorkWithController extends Controller
{
    public function whoWeWorkWithAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Who we work with');

        return new Response($this->get('templating')->render('::who-we-work-with.html.twig', $arguments));
    }
}
