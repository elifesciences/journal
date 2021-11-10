<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Subject;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\Paragraph;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AlertsController extends Controller
{
    public function alertsAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Alerts';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'Stay in touch with eLife efforts to support the community and open science as well as new research. Choose your feeds and preferred ways to connect below.');

        $arguments['body'] = [
            ArticleSection::basic('New Research', 2, $this->render(
                Listing::unordered([
                    'Receive <a href="'.$this->get('router')->generate('content-alerts').'">weekly updates of the latest published research</a>',
                    'To stay on top of new research available every day, subscribe to our RSS feeds for <a href="'.$this->get('router')->generate('rss-ahead').'">author PDFs</a> and <a href="'.$this->get('router')->generate('rss-recent').'">published articles</a>',
                    'See the highlights of recently published research and more on <a href="https://www.twitter.com/elife">Twitter</a> or <a href="https://www.facebook.com/elifesciences">Facebook</a>',
                ], 'bullet')
            )),
            ArticleSection::basic('Science in plain language', 2, $this->render(
                Listing::unordered([
                    'Subscribe to the RSS feed for <a href="'.$this->get('router')->generate('rss-digests').'">eLife Digests</a>',
                    'Subscribe to the RSS feed for <a href="'.$this->get('router')->generate('rss-magazine').'">all the latest content from the eLife magazine</a>',
                ], 'bullet')
            )),
            ArticleSection::basic('Community-building', 2, $this->render(
                Listing::unordered([
                    '<a href="https://elifesciences.org/content-alerts">Sign up to our monthly community newsletter</a> for details on upcoming webinars, travel grant deadlines, interviews, and other efforts to connect and support especially early-career researchers in life sciences and biomedicine',
                ], 'bullet')
            )),
            ArticleSection::basic('eLife&apos;s Innovation Initiative and technology news', 2, $this->render(
                Listing::unordered([
                    'For the latest in eLife Labs, innovation, and new tools, <a href="https://elifesciences.org/content-alerts">sign up for our technology and innovation newsletter</a>',
                    'Subscribe to the RSS feed for <a href="'.$this->get('router')->generate('rss-labs').'">all the open source technology innovation news</a> from eLife Sciences',
                ], 'bullet')
            )),
            ArticleSection::basic('The latest from eLife', 2, $this->render(
                Listing::unordered([
                    'Sign up to receive our <a href="https://elifesciences.org/content-alerts">bi-monthly newsletter</a> for recent developments at eLife, new products and collaborations and changes to editorial policy.</a>',
                ], 'bullet')
            )),
        ];

        $this->get('elife.api_sdk.subjects')
            ->reverse()
            ->slice(1, 100)
            ->map(function (Subject $subject) {
                return 'Subscribe to the RSS feed for <a href="'.$this->get('router')->generate('rss-recent-by-subject', [$subject]).'">'.$subject->getName().'</a>';
            })
            ->then(function (Sequence $links) use (&$arguments) {
                $arguments['body'][] = ArticleSection::basic('eLife&apos;s Subject specific RSS feeds', 2, $this->render(
                    Listing::unordered($links->toArray(), 'bullet')
                ));
            })
            ->otherwise($this->softFailure('Failed to load subjects list'))
            ->wait();

        $arguments['body'][] = new Paragraph('eLife is also on <a href="https://www.linkedin.com/company/elife-sciences-publications-ltd">LinkedIn</a> and <a href="https://www.youtube.com/channel/UCNEHLtAc_JPI84xW8V4XWyw">YouTube</a>.');

        return new Response($this->get('templating')->render('::alerts.html.twig', $arguments));
    }
}
