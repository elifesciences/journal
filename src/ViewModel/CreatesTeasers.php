<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ViewModel\ContextLabel;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Teaser;
use UnexpectedValueException;

trait CreatesTeasers
{
    final private function createTeaser(array $item): Teaser
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

    /**
     * @return ContextLabel|null
     */
    final private function createContextLabel(array $item)
    {
        if (empty($item['subjects'])) {
            return null;
        }

        return new ContextLabel(...array_map(function (array $subject) {
            return new Link(
                $subject['name'],
                $this->urlGenerator->generate('subject', ['id' => $subject['id']])
            );
        }, $item['subjects'] ?? []));
    }
}
