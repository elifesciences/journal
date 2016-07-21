<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class ResourcesController extends Controller
{
    public function resourcesAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Resources');

        return new Response($this->get('templating')->render('::resources.html.twig', $arguments));
    }
}
