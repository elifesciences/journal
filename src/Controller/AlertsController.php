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
            ArticleSection::basic($this->render(
                Listing::unordered([
                    'Receive <a href="'.$this->get('router')->generate('content-alerts').'">weekly updates of the latest published research</a>',
                    'To stay on top of new research available every day, subscribe to our RSS feeds for <a href="'.$this->get('router')->generate('rss-ahead').'">author PDFs</a> and <a href="'.$this->get('router')->generate('rss-recent').'">published articles</a>',
                    'See the highlights of recently published research and more on <a href="https://www.twitter.com/elife">Twitter</a> or <a href="https://www.facebook.com/elifesciences">Facebook</a>',
                ], 'bullet')
            ), 'New Research', 2),
            ArticleSection::basic($this->render(
                Listing::unordered([
                    'Sign up to receive the eLife Magazine Highlights, a fortnightly newsletter featuring some of the <a href="https://connect.elifesciences.org/magazine-highlights">latest content published in our magazine</a>."',
                    'Subscribe to the RSS feed for <a href="'.$this->get('router')->generate('rss-digests').'">eLife Digests</a>',
                    'Subscribe to the RSS feed for <a href="'.$this->get('router')->generate('rss-magazine').'">all the latest content from the eLife magazine</a>',
                ], 'bullet')
            ), 'eLife Magazine', 2),
            ArticleSection::basic($this->render(
                Listing::unordered([
                    '<a href="'.$this->get('router')->generate('content-alerts-variant', ['variant' => 'early-career']).'">Sign up to our monthly eLife community newsletter</a> for details on upcoming webinars, new programmes, interviews, and other efforts to support positive research culture in life sciences and biomedicine',
                ], 'bullet')
            ), 'Community-building', 2),
            ArticleSection::basic($this->render(
                Listing::unordered([
                    'Sign up to receive our <a href="'.$this->get('router')->generate('content-alerts-variant', ['variant' => 'elife-newsletter']).'">bi-monthly newsletter</a> for recent developments at eLife, new products and collaborations and changes to editorial policy.</a>',
                ], 'bullet')
            ), 'The latest from eLife', 2),
        ];

        $this->get('elife.api_sdk.subjects')
            ->reverse()
            ->slice(1, 100)
            ->map(function (Subject $subject) {
                return 'Subscribe to the RSS feed for <a href="'.$this->get('router')->generate('rss-recent-by-subject', [$subject]).'">'.$subject->getName().'</a>';
            })
            ->then(function (Sequence $links) use (&$arguments) {
                $arguments['body'][] = ArticleSection::basic($this->render(
                    Listing::unordered($links->toArray(), 'bullet')
                ), 'eLife&apos;s Subject specific RSS feeds', 2);
            })
            ->otherwise($this->softFailure('Failed to load subjects list'))
            ->wait();

        $arguments['body'][] = new Paragraph('eLife is also on <a href="https://www.youtube.com/channel/UCNEHLtAc_JPI84xW8V4XWyw">YouTube</a>.');

        return new Response($this->get('templating')->render('::alerts.html.twig', $arguments));
    }
}
