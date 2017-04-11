<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Listing;
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

        $arguments['body'] = [
            ArticleSection::basic('New Research', 2, $this->render(
                Listing::unordered([
                    'Use our <a href="https://crm.elifesciences.org/crm/node/3">email preference management center</a> to sign up for weekly notifications of new published research plus papers available in PDF shortly after acceptance',
                    'To stay on top of new research available every day, subscribe to our RSS feeds for <a href="https://elifesciences.org/rss/ahead.xml">author PDFs</a> and <a href="https://elifesciences.org/rss/recent.xml">published articles</a>',
                    'See the highlights of recently published research and more on <a href="https://www.twitter.com/elife">Twitter</a> or <a href="https://www.facebook.com/elifesciences">Facebook</a>',
                ], 'bullet')
            )),
            ArticleSection::basic('Science in plain language', 2, $this->render(
                Listing::unordered([
                    '<a href="https://medium.com/feed/@eLIfe">Subscribe</a> to the RSS feed for eLife Digests on Medium.com',
                ], 'bullet')
            )),
        ];

        return new Response($this->get('templating')->render('::alerts.html.twig', $arguments));
    }
}
