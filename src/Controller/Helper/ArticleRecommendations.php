<?php

namespace eLife\Journal\Controller\Helper;

use DateTimeImmutable;
use eLife\ApiSdk\Client\Articles;
use eLife\ApiSdk\Client\Collections;
use eLife\ApiSdk\Client\PodcastEpisodes;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapterModel;
use eLife\ApiSdk\Model\ReviewedPreprint;
use GuzzleHttp\Promise\Utils;

class ArticleRecommendations
{
    private Articles $articlesClient;
    private Collections $collectionsClient;
    private PodcastEpisodes $podcastEpisodesClient;

    public function __construct(
        Articles $articlesClient,
        Collections $collectionsClient,
        PodcastEpisodes $podcastEpisodesClient
    ) {
        $this->articlesClient = $articlesClient;
        $this->collectionsClient = $collectionsClient;
        $this->podcastEpisodesClient = $podcastEpisodesClient;
    }

    public function getRecommendationsForArticle(Identifier $id) : Sequence
    {
        $relations = $this->articlesClient->getRelatedArticles($id->getId())
            ->sort($this->byTypeThenDate());

        $collections = $this->collectionsClient
            ->containing($id)
            ->slice(0, 100)
            ->otherwise(function () {
                return new EmptySequence();
            });

        // ->reduce() on a promise-backed Sequence blocks immediately (it calls
        // ->wait() internally), so do the reduction inside ->then() once the
        // episodes have actually resolved, instead of calling it directly here.
        $podcastEpisodeChapters = $this->podcastEpisodesClient
            ->containing($id)
            ->slice(0, 100)
            ->then(function (Sequence $episodes) use ($id) {
                return $episodes->reduce(function (Sequence $chapters, PodcastEpisode $episode) use ($id) {
                    foreach ($episode->getChapters() as $chapter) {
                        foreach ($chapter->getContent() as $content) {
                            if ($id->getId() === $content->getId()) {
                                return $chapters->append(new PodcastEpisodeChapterModel($episode, $chapter));
                            }
                        }
                    }

                    return $chapters;
                }, new EmptySequence());
            })
            ->otherwise(function () {
                return new EmptySequence();
            });

        // Utils::all() kicks off all three calls and waits for them together,
        // rather than resolving one at a time, and only the ->then() callback
        // below (run once everything has resolved) does the combining.
        return new PromiseSequence(
            Utils::all([
                'relations' => $relations,
                'collections' => $collections,
                'podcastEpisodeChapters' => $podcastEpisodeChapters,
            ])
                ->then(function (array $parts) {
                    return $parts['relations']
                        ->append(...$parts['collections'])
                        ->append(...$parts['podcastEpisodeChapters'])
                        ->slice(0, 100);
                })
        );
    }

    private function byTypeThenDate() : callable
    {
        return function (Model $a, Model $b) {
            $aType = $a instanceof ReviewedPreprint ? 'reviewed-preprint' : $a->getType();
            $bType = $b instanceof ReviewedPreprint ? 'reviewed-preprint' : $b->getType();

            static $order = [
                'retraction' => 1,
                'correction' => 2,
                'expression-concern' => 3,
                'external-article' => 4,
                'registered-report' => 5,
                'replication-study' => 6,
                'research-advance' => 7,
                'scientific-correspondence' => 8,
                'research-article' => 9,
                'research-communication' => 10,
                'tools-resources' => 11,
                'feature' => 12,
                'insight' => 13,
                'editorial' => 14,
                'short-report' => 15,
                'review-article' => 16,
                'reviewed-preprint' => 17,
            ];

            if ($order[$aType] === $order[$bType]) {
                $aDate = $a instanceof HasPublishedDate ? $a->getPublishedDate() : new DateTimeImmutable('0000-00-00');
                $bDate = $b instanceof HasPublishedDate ? $b->getPublishedDate() : new DateTimeImmutable('0000-00-00');

                return $bDate <=> $aDate;
            }

            return $order[$aType] <=> $order[$bType];
        };
    }
}
