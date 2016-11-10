<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\SeeMoreLink;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MagazineController extends Controller
{
    public function listAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Magazine', false,
            'Highlighting the latest research and giving a voice to life and biomedical scientists.');

        $arguments['audio_player'] = $this->get('elife.api_sdk.podcast_episodes')
            ->slice(0, 1)
            ->then(function (Sequence $result) {
                if ($result->isEmpty()) {
                    return null;
                }

                return $this->get('elife.journal.view_model.converter')->convert($result[0], AudioPlayer::class);
            })
            ->otherwise(function (Throwable $e) {
                return null;
            });

        $arguments['latestHeading'] = new ListHeading('Latest');
        $arguments['latest'] = $this->get('elife.api_sdk.search')
            ->forType('editorial', 'insight', 'feature', 'collection', 'interview', 'podcast-episode')
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
                    $arguments['latestHeading']['heading']
                );
            });

        $events = $this->get('elife.api_sdk.events')
            ->forType('open')
            ->reverse();

        $arguments['events'] = $events
            ->slice(0, 3)
            ->then(function (Sequence $result) use ($events) {
                if ($result->isEmpty()) {
                    return null;
                }

                $items = $result->map(function (Model $model) {
                    return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class, ['variant' => 'secondary']);
                })->toArray();
                $heading = 'Events';

                if (count($events) > 3) {
                    return ListingTeasers::withSeeMore(
                        $items,
                        new SeeMoreLink(new Link('See more events', $this->get('router')->generate('events'))),
                        $heading
                    );
                }

                return ListingTeasers::basic($items, $heading);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['elifeDigests'] = $this->get('elife.api_sdk.medium_articles')
            ->slice(0, 3)
            ->then(function (Sequence $result) {
                if ($result->isEmpty()) {
                    return null;
                }

                return ListingTeasers::withSeeMore(
                    $result->map(function (Model $model) {
                        return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class, ['variant' => 'secondary']);
                    })->toArray(),
                    new SeeMoreLink(new Link('See more eLife digests on Medium', 'https://medium.com/@elife')),
                    'eLife digests'
                );
            })->otherwise(function () {
                return null;
            });

        return new Response($this->get('templating')->render('::magazine.html.twig', $arguments));
    }
}
