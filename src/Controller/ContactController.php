<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;

final class ContactController extends Controller
{
    public function contactAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        return new Response($this->get('templating')->render('::contact.html.twig', $arguments));
    }
}
