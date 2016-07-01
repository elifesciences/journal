<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;

final class CareersController extends Controller
{
    public function listAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        return new Response($this->get('templating')->render('::careers.html.twig', $arguments));
    }
}
