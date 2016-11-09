<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\MediaChapterListingItem;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Response;

final class PodcastController extends Controller
{
    public function listAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife podcast');

        $arguments['episodes'] = $this->get('elife.api_sdk.podcast_episodes')
            ->slice(($page * $perPage) - $perPage, $perPage)
            ->then(function (Sequence $episodes) {
                if ($episodes->isEmpty()) {
                    return null;
                }

                return GridListing::forTeasers($episodes->map(function (PodcastEpisode $episode) {
                    return $this->get('elife.journal.view_model.converter')->convert($episode, Teaser::class, ['variant' => 'grid']);
                })->toArray(), 'Latest episodes');
            });

        return new Response($this->get('templating')->render('::podcast.html.twig', $arguments));
    }

    public function episodeAction(int $number) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['episode'] = $this->get('elife.api_sdk.podcast_episodes')->get($number);

        $arguments['contentHeader'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                return $this->get('elife.journal.view_model.converter')->convert($episode, ContentHeaderNonArticle::class);
            });

        $arguments['audioPlayer'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                return $this->get('elife.journal.view_model.converter')->convert($episode, AudioPlayer::class);
            });

        $arguments['leadParas'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                return new LeadParas([new LeadPara($episode->getImpactStatement())]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['chapters'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                return $episode->getChapters()->map(function (PodcastEpisodeChapter $chapter) {
                    return $this->get('elife.journal.view_model.converter')->convert($chapter, MediaChapterListingItem::class);
                });
            });

        $arguments['related'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                $articles = [];

                foreach ($episode->getChapters() as $chapter) {
                    $articles = array_merge($articles, $chapter->getContent()->slice(0, 1)->toArray());
                }

                return ListingTeasers::basic(
                    array_map(function (Model $model) {
                        return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class, ['variant' => 'secondary']);
                    }, $articles),
                    'Related'
                );
            });

        return new Response($this->get('templating')->render('::podcast-episode.html.twig', $arguments));
    }
}
