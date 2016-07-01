<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;

final class TermsController extends Controller
{
    public function termsAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        return new Response($this->get('templating')->render('::terms.html.twig', $arguments));
    }
}
