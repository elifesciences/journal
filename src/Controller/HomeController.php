<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\SeeMoreLink;
use eLife\Patterns\ViewModel\SiteLinks;
use eLife\Patterns\ViewModel\SiteLinksList;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Response;

final class HomeController extends Controller
{
    public function homeAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife');

        $arguments['leadParas'] = new LeadParas([new LeadPara('eLife is an open-access journal that publishes research in the life and biomedical sciences')]);

        $arguments['subjects'] = $this->get('elife.api_sdk.subjects')
            ->reverse()
            ->slice(1, 100)
            ->map(function (Subject $subject) {
                return new Link($subject->getName(), $this->get('router')->generate('subject', ['id' => $subject->getId()]));
            })
            ->then(function (Sequence $links) {
                return array_chunk($links->toArray(), ceil(count($links) / 3));
            })
            ->then(function (array $columns) {
                return new SiteLinksList(array_map(function (array $items) {
                    return new SiteLinks($items);
                }, $columns));
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['latestResearchHeading'] = new ListHeading('Latest research');
        $arguments['latestResearch'] = $this->get('elife.api_sdk.search')
            ->forType('research-advance', 'research-article', 'research-exchange', 'short-report', 'tools-resources', 'replication-study')
            ->sortBy('date')
            ->slice(($page * $perPage) - $perPage, $perPage)
            ->then(function (Sequence $result) use ($arguments) {
                if ($result->isEmpty()) {
                    return null;
                }

                return ListingTeasers::basic(
                    $result->map(function (Model $model) {
                        return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class);
                    })->toArray(),
                    $arguments['latestResearchHeading']['heading']
                );
            });

        $arguments['magazine'] = $this->get('elife.api_sdk.search')
            ->forType('editorial', 'insight', 'feature', 'collection', 'interview', 'podcast-episode')
            ->sortBy('date')
            ->slice(1, 7)
            ->then(function (Sequence $result) use ($arguments) {
                if ($result->isEmpty()) {
                    return null;
                }

                return ListingTeasers::withSeeMore(
                    $result->map(function (Model $model) {
                        return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class, ['variant' => 'secondary']);
                    })->toArray(),
                    new SeeMoreLink(new Link('See more Magazine articles', $this->get('router')->generate('magazine'))),
                    'Magazine'
                );
            })->otherwise(function () {
                return null;
            });

        return new Response($this->get('templating')->render('::home.html.twig', $arguments));
    }
}
