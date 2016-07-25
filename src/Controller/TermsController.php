<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class TermsController extends Controller
{
    public function termsAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Terms and policy');

        return new Response($this->get('templating')->render('::terms.html.twig', $arguments));
    }
}
