<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;

final class LabsController extends Controller
{
    public function listAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        return new Response($this->get('templating')->render('::labs.html.twig', $arguments));
    }
}
