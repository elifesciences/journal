<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;

final class WhoWeWorkWithController extends Controller
{
    public function whoWeWorkWithAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        return new Response($this->get('templating')->render('::who-we-work-with.html.twig', $arguments));
    }
}
