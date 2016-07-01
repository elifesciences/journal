<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;

final class ResourcesController extends Controller
{
    public function resourcesAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        return new Response($this->get('templating')->render('::resources.html.twig', $arguments));
    }
}
