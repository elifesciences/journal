<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResourcesController extends Controller
{
    public function resourcesAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Resources';

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['title']);

        $arguments['leadParas'] = new LeadParas([
            new LeadPara('A collection of posters, handouts, slide presentations, videos, and more, about all of the work behind the eLife initiative.'),
        ]);

        return new Response($this->get('templating')->render('::resources.html.twig', $arguments));
    }
}
