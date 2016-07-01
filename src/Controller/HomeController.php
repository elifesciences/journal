<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;

final class HomeController extends Controller
{
    public function homeAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        return new Response($this->get('templating')->render('::home.html.twig', $arguments));
    }
}
