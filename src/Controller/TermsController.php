<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class TermsController extends Controller
{
    public function termsAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['title'] = 'Terms and policy';

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['title']);

        return new Response($this->get('templating')->render('::terms.html.twig', $arguments));
    }
}
