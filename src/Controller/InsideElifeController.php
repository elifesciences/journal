<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class InsideElifeController extends Controller
{
    public function listAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Inside eLife');

        return new Response($this->get('templating')->render('::inside-elife.html.twig', $arguments));
    }
}
