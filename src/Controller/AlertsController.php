<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class AlertsController extends Controller
{
    public function alertsAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Alerts');

        return new Response($this->get('templating')->render('::alerts.html.twig', $arguments));
    }
}
