<?php

namespace eLife\Journal\ViewModel;

use DateTimeImmutable;
use eLife\ApiClient\Result;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\SeeMoreLink;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use eLife\Patterns\ViewModel\TeaserImage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SecondaryListingTeaserFactory
{
    use CreatesTeasers;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function forResult(
        Result $result,
        string $heading = null,
        SeeMoreLink $seeMoreLink = null
    ) : ListingTeasers {
        return $this->forItems($result['items'], $heading, $seeMoreLink);
    }

    public function forItems(array $items, string $heading = null, SeeMoreLink $seeMoreLink = null) : ListingTeasers
    {
        $teasers = [];
        foreach ($items as $item) {
            $teasers[] = $this->createTeaser($item);
        }

        if ($seeMoreLink) {
            return ListingTeasers::withSeeMore($teasers, $seeMoreLink, $heading);
        }

        return ListingTeasers::basic($teasers, $heading);
    }

    private function teaserForArticle(array $article) : Teaser
    {
        if (false === empty($article['image'])) {
            $image = TeaserImage::small(
                $article['image']['sizes']['1:1'][70],
                $article['image']['alt'],
                [
                    140 => $article['image']['sizes']['1:1'][140],
                    70 => $article['image']['sizes']['1:1'][70],
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

        return Teaser::secondary(
            $title,
            $this->urlGenerator->generate('article', ['volume' => $article['volume'], 'id' => $article['id']]),
            $article['authorLine'],
            $this->createContextLabel($article),
            $image,
            TeaserFooter::forNonArticle(
                Meta::withText(
                    ucfirst(str_replace('-', ' ', $article['type'])),
                    new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $article['statusDate']))
                )
            )
        );
    }

    private function teaserForBlogArticle(array $article) : Teaser
    {
        return Teaser::secondary(
            $article['title'],
            $this->urlGenerator->generate('inside-elife-article', ['id' => $article['id']]),
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

        return Teaser::secondary(
            $collection['title'],
            $this->urlGenerator->generate('collection', ['id' => $collection['id']]),
            $curatedBy,
            $this->createContextLabel($collection),
            TeaserImage::small(
                $collection['image']['sizes']['1:1'][70],
                $collection['image']['alt'],
                [
                    140 => $collection['image']['sizes']['1:1'][140],
                    70 => $collection['image']['sizes']['1:1'][70],
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
            new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $event['starts']), true),
            true
        );
    }

    private function teaserForInterview(array $interview) : Teaser
    {
        return Teaser::secondary(
            $interview['title'],
            $this->urlGenerator->generate('interview', ['id' => $interview['id']]),
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
        return Teaser::secondary(
            $experiment['title'],
            $this->urlGenerator->generate('labs-experiment', ['number' => $experiment['number']]),
            null,
            null,
            TeaserImage::small(
                $experiment['image']['sizes']['1:1'][70],
                $experiment['image']['alt'],
                [
                    140 => $experiment['image']['sizes']['1:1'][140],
                    70 => $experiment['image']['sizes']['1:1'][70],
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
            $image = TeaserImage::small(
                $article['image']['sizes']['1:1'][70],
                $article['image']['alt'],
                [
                    140 => $article['image']['sizes']['1:1'][140],
                    70 => $article['image']['sizes']['1:1'][70],
                ]
            );
        } else {
            $image = null;
        }

        return Teaser::secondary(
            $article['title'],
            $article['uri'],
            null,
            null,
            $image,
            TeaserFooter::forNonArticle(
                Meta::withDate(
                    new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $article['published']))
                )
            )
        );
    }

    private function teaserForPodcastEpisode(array $episode) : Teaser
    {
        return Teaser::secondary(
            $episode['title'],
            $this->urlGenerator->generate('podcast-episode', ['number' => $episode['number']]),
            'Episode '.$episode['number'],
            $this->createContextLabel($episode),
            TeaserImage::small(
                $episode['image']['sizes']['1:1'][70],
                $episode['image']['alt'],
                [
                    140 => $episode['image']['sizes']['1:1'][140],
                    70 => $episode['image']['sizes']['1:1'][70],
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
