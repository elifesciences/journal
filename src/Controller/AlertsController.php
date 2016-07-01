<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;

final class AlertsController extends Controller
{
    public function alertsAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        return new Response($this->get('templating')->render('::alerts.html.twig', $arguments));
    }
}
