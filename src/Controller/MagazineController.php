<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class MagazineController extends Controller
{
    public function listAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Magazine', false,
            'Highlighting the latest research and giving a voice to life and biomedical scientists.');

        return new Response($this->get('templating')->render('::magazine.html.twig', $arguments));
    }
}
