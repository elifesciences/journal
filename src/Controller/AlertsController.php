<?php

namespace eLife\Journal\Controller;

use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
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
            ArticleSection::basic('Community-building', 2, $this->render(
                Listing::unordered([
                    '<a href="https://crm.elifesciences.org/crm/civicrm/profile/create?reset=1&gid=26">Sign up to our bi-monthly community newsletter</a> for details on upcoming webinars, travel grant deadlines, interviews, and other efforts to connect and support especially early-career researchers in life sciences and biomedicine',
                ], 'bullet')
            )),
            ArticleSection::basic('eLife&apos;s Innovation Initiative and technology news', 2, $this->render(
                Listing::unordered([
                    'For the latest in eLife Labs, innovation, and new tools, <a href="https://crm.elifesciences.org/crm/node/8">sign up for our technology and innovation newsletter</a>',
                ], 'bullet')
            )),
            new Paragraph('eLife is also on <a href="http://www.linkedin.com/company/elife-sciences-publications-ltd">LinkedIn</a> and <a href="https://plus.google.com/102129675554093758550/posts">Google Plus</a>.'),
        ];

        return new Response($this->get('templating')->render('::alerts.html.twig', $arguments));
    }
}
