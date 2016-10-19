<?php

namespace eLife\Journal\ViewModel;

use DateTimeImmutable;
use eLife\ApiClient\Result;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use eLife\Patterns\ViewModel\TeaserImage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ListingTeaserFactory
{
    use CreatesTeasers;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function forResult(Result $result, string $heading = null) : ListingTeasers
    {
        return $this->forItems($result['items'], $heading);
    }

    public function forItems(array $items, string $heading = null) : ListingTeasers
    {
        $teasers = [];
        foreach ($items as $item) {
            $teasers[] = $this->createTeaser($item);
        }

        return ListingTeasers::basic($teasers, $heading);
    }

    private function teaserForArticle(array $article) : Teaser
    {
        if (false === empty($article['image'])) {
            $image = TeaserImage::big(
                $article['image']['sizes']['16:9'][250],
                $article['image']['alt'],
                [
                    500 => $article['image']['sizes']['16:9'][500],
                    250 => $article['image']['sizes']['16:9'][250],
                ]
            );
        } else {
            $image = null;
        }

        if (empty($article['titlePrefix'])) {
            $title = $article['title'];
        } else {
            $title = sprintf('%s: %s', $article['titlePrefix'], $article['title']);
        }

        return Teaser::main(
            $title,
            $this->urlGenerator->generate('article', ['volume' => $article['volume'], 'id' => $article['id']]),
            $article['impactStatement'] ?? null,
            $article['authorLine'],
            $this->createContextLabel($article),
            $image,
            TeaserFooter::forArticle(
                Meta::withText(
                    ucfirst(str_replace('-', ' ', $article['type'])),
                    new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $article['statusDate']))
                ),
                'vor' === $article['status']
            )
        );
    }

    private function teaserForBlogArticle(array $article) : Teaser
    {
        return Teaser::main(
            $article['title'],
            $this->urlGenerator->generate('inside-elife-article', ['id' => $article['id']]),
            $article['impactStatement'] ?? null,
            null,
            $this->createContextLabel($article),
            null,
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                    new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $article['published']))
                )
            )
        );
    }

    private function teaserForCollection(array $collection) : Teaser
    {
        $curatedBy = 'Curated by '.$collection['selectedCurator']['name']['preferred'];
        if (false === empty($collection['selectedCurator']['etAl'])) {
            $curatedBy .= ' et al';
        }
        $curatedBy .= '.';

        return Teaser::main(
            $collection['title'],
            $this->urlGenerator->generate('collection', ['id' => $collection['id']]),
            $collection['impactStatement'] ?? null,
            $curatedBy,
            $this->createContextLabel($collection),
            TeaserImage::big(
                $collection['image']['sizes']['16:9'][250],
                $collection['image']['alt'],
                [
                    500 => $collection['image']['sizes']['16:9'][500],
                    250 => $collection['image']['sizes']['16:9'][250],
                ]
            ),
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new Link('Collection', $this->urlGenerator->generate('collections')),
                    new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $collection['updated']))
                )
            )
        );
    }

    private function teaserForEvent(array $event) : Teaser
    {
        return Teaser::event(
            $event['title'],
            $this->urlGenerator->generate('event', ['id' => $event['id']]),
            null,
            new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $event['starts']), true)
        );
    }

    private function teaserForInterview(array $interview) : Teaser
    {
        return Teaser::main(
            $interview['title'],
            $this->urlGenerator->generate('interview', ['id' => $interview['id']]),
            $interview['impactStatement'] ?? null,
            'An interview with '.$interview['interviewee']['name']['preferred'],
            null,
            null,
            TeaserFooter::forNonArticle(
                Meta::withText(
                    'Interview',
                    new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $interview['published']))
                )
            )
        );
    }

    private function teaserForLabsExperiment(array $experiment) : Teaser
    {
        return Teaser::main(
            $experiment['title'],
            $this->urlGenerator->generate('labs-experiment', ['number' => $experiment['number']]),
            $experiment['impactStatement'] ?? null,
            null,
            null,
            TeaserImage::big(
                $experiment['image']['sizes']['16:9'][250],
                $experiment['image']['alt'],
                [
                    500 => $experiment['image']['sizes']['16:9'][500],
                    250 => $experiment['image']['sizes']['16:9'][250],
                ]
            ),
            TeaserFooter::forNonArticle(
                Meta::withText(
                    'Experiment: '.str_pad($experiment['number'], 3, '0', STR_PAD_LEFT),
                    new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $experiment['published']))
                )
            )
        );
    }

    private function teaserForMediumArticle(array $article) : Teaser
    {
        if (false === empty($article['image'])) {
            $image = TeaserImage::big(
                $article['image']['sizes']['16:9'][250],
                $article['image']['alt'],
                [
                    500 => $article['image']['sizes']['16:9'][500],
                    250 => $article['image']['sizes']['16:9'][250],
                ]
            );
        } else {
            $image = null;
        }

        return Teaser::main(
            $article['title'],
            $article['uri'],
            $article['impactStatement'] ?? null,
            null,
            $image,
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new Link('Medium', 'https://medium.com/@elife'),
                    new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $article['published']))
                )
            )
        );
    }

    private function teaserForPodcastEpisode(array $episode) : Teaser
    {
        return Teaser::main(
            $episode['title'],
            $this->urlGenerator->generate('podcast-episode', ['number' => $episode['number']]),
            $episode['impactStatement'] ?? null,
            'Episode '.$episode['number'],
            $this->createContextLabel($episode),
            TeaserImage::big(
                $episode['image']['sizes']['16:9'][250],
                $episode['image']['alt'],
                [
                    500 => $episode['image']['sizes']['16:9'][500],
                    250 => $episode['image']['sizes']['16:9'][250],
                ]
            ),
            TeaserFooter::forNonArticle(
                Meta::withText(
                    'Podcast',
                    new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $episode['published']))
                )
            )
        );
    }
}
