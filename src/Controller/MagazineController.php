<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;

final class MagazineController extends Controller
{
    public function listAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        return new Response($this->get('templating')->render('::magazine.html.twig', $arguments));
    }
}
