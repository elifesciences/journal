<?php

namespace eLife\Journal\ViewModel;

use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\Patterns\ViewModel\ContextLabel;
use eLife\Patterns\ViewModel\Link;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use UnexpectedValueException;
use function GuzzleHttp\Promise\all;

trait CreatesTeasers
{
    final private function createTeaser(array $item): PromiseInterface
    {
        switch ($type = $item['type'] ?? 'unknown') {
            case 'correction':
            case 'editorial':
            case 'feature':
            case 'insight':
            case 'research-advance':
            case 'research-article':
            case 'research-exchange':
            case 'retraction':
            case 'registered-report':
            case 'replication-study':
            case 'short-report':
            case 'tools-resources':
                return $this->teaserForArticle($item);
            case 'blog-article':
                return $this->teaserForBlogArticle($item);
            case 'collection':
                return $this->teaserForCollection($item);
            case 'event':
                return $this->teaserForEvent($item);
            case 'interview':
                return $this->teaserForInterview($item);
            case 'labs-experiment':
                return $this->teaserForLabsExperiment($item);
            case 'medium-article':
                return $this->teaserForMediumArticle($item);
            case 'podcast-episode':
                return $this->teaserForPodcastEpisode($item);
        }

        throw new UnexpectedValueException('Unknown type '.$type);
    }

    final private function createContextLabel(array $item) : PromiseInterface
    {
        if (empty($item['subjects'])) {
            return new FulfilledPromise(null);
        }

        return all(array_map(function (string $id) {
            return $this->subjects->getSubject(['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT, 1)], $id);
        }, $item['subjects']))
            ->then(function (array $subjects) {
                return new ContextLabel(...array_map(function (Result $subject) {
                    return new Link(
                        $subject['name'],
                        $this->urlGenerator->generate('subject', ['id' => $subject['id']])
                    );
                }, $subjects));
            });
    }
}
