<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AlertsController extends Controller
{
    public function alertsAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Alerts';

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['title']);

        $arguments['leadParas'] = new LeadParas([
            new LeadPara('Stay in touch with eLife efforts to support the community and open science as well as new research. Choose your feeds and preferred ways to connect below.'),
        ]);

        return new Response($this->get('templating')->render('::alerts.html.twig', $arguments));
    }
}
