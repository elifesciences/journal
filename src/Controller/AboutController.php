<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;

final class AboutController extends Controller
{
    public function aboutAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }
}
