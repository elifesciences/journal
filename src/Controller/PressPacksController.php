<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class PressPacksController extends Controller
{
    public function listAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('For the press');

        return new Response($this->get('templating')->render('::press-packs.html.twig', $arguments));
    }
}
