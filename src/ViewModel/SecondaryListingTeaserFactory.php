<?php

namespace eLife\Journal\ViewModel;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\Result;
use eLife\Patterns\ViewModel\ContextLabel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\SeeMoreLink;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use eLife\Patterns\ViewModel\TeaserImage;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function GuzzleHttp\Promise\all;

final class SecondaryListingTeaserFactory
{
    use CreatesTeasers;

    private $urlGenerator;
    private $subjects;

    public function __construct(UrlGeneratorInterface $urlGenerator, SubjectsClient $subjects)
    {
        $this->urlGenerator = $urlGenerator;
        $this->subjects = $subjects;
    }

    public function forResult(
        Result $result,
        string $heading = null,
        SeeMoreLink $seeMoreLink = null
    ) : PromiseInterface {
        return $this->forItems($result['items'], $heading, $seeMoreLink);
    }

    public function forItems(array $items, string $heading = null, SeeMoreLink $seeMoreLink = null) : PromiseInterface
    {
        $teasers = [];
        foreach ($items as $item) {
            $teasers[] = $this->createTeaser($item);
        }

        return all($teasers)
            ->then(function (array $teasers) use ($heading, $seeMoreLink) {
                if ($seeMoreLink) {
                    return ListingTeasers::withSeeMore($teasers, $seeMoreLink, $heading);
                }

                return ListingTeasers::basic($teasers, $heading);
            });
    }

    private function teaserForArticle(array $article) : PromiseInterface
    {
        if (false === empty($article['image'])) {
            $image = TeaserImage::small(
                $article['image']['sizes']['1:1'][70],
                $article['image']['alt'],
                null,
                [
                    140 => $article['image']['sizes']['1:1'][140],
                    70 => $article['image']['sizes']['1:1'][70],
                ]
            );
        } else {
            $image = null;
        }

        return $this->createContextLabel($article)
            ->then(function ($contextLabel) use ($article, $image) {
                return Teaser::secondary(
                    $article['title'],
                    null,
                    null,
                    $contextLabel,
                    $image,
                    TeaserFooter::forNonArticle(
                        Meta::withText(
                            ucfirst(str_replace('-', ' ', $article['type'])),
                            new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $article['published']))
                        )
                    )
                );
            })
            ;
    }

    private function teaserForBlogArticle(array $article) : PromiseInterface
    {
        return $this->createContextLabel($article)
            ->then(function ($contextLabel) use ($article) {
                return Teaser::secondary(
                    $article['title'],
                    $this->urlGenerator->generate('inside-elife-article', ['id' => $article['id']]),
                    null,
                    $contextLabel,
                    null,
                    TeaserFooter::forNonArticle(
                        Meta::withLink(
                            new Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                            new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $article['published']))
                        )
                    )
                );
            })
            ;
    }

    private function teaserForCollection(array $collection) : PromiseInterface
    {
        return $this->createContextLabel($collection)
            ->then(function ($contextLabel) use ($collection) {
                $curatedBy = 'Curated by '.$collection['selectedCurator']['name']['preferred'];
                if (false === empty($collection['selectedCurator']['etAl'])) {
                    $curatedBy .= ' et al';
                }
                $curatedBy .= '.';

                return Teaser::secondary(
                    $collection['title'],
                    $this->urlGenerator->generate('collection', ['id' => $collection['id']]),
                    $curatedBy,
                    $contextLabel,
                    TeaserImage::small(
                        $collection['image']['sizes']['1:1'][70],
                        $collection['image']['alt'],
                        $this->urlGenerator->generate('collection', ['id' => $collection['id']]),
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
            })
            ;
    }

    private function teaserForEvent(array $event) : PromiseInterface
    {
        return new FulfilledPromise(Teaser::event(
            $event['title'],
            $this->urlGenerator->generate('event', ['id' => $event['id']]),
            null,
            new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $event['starts']), true),
            true
        ));
    }

    private function teaserForInterview(array $interview) : PromiseInterface
    {
        return new FulfilledPromise(Teaser::secondary(
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
        ));
    }

    private function teaserForLabsExperiment(array $experiment) : PromiseInterface
    {
        return new FulfilledPromise(Teaser::secondary(
            $experiment['title'],
            $this->urlGenerator->generate('labs-experiment', ['number' => $experiment['number']]),
            null,
            null,
            TeaserImage::small(
                $experiment['image']['sizes']['1:1'][70],
                $experiment['image']['alt'],
                null,
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
        ));
    }

    private function teaserForMediumArticle(array $article) : PromiseInterface
    {
        if (false === empty($article['image'])) {
            $image = TeaserImage::small(
                $article['image']['sizes']['1:1'][70],
                $article['image']['alt'],
                null,
                [
                    140 => $article['image']['sizes']['1:1'][140],
                    70 => $article['image']['sizes']['1:1'][70],
                ]
            );
        } else {
            $image = null;
        }

        return new FulfilledPromise(Teaser::secondary(
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
        ));
    }

    private function teaserForPodcastEpisode(array $episode) : PromiseInterface
    {
        return $this->createContextLabel($episode)
            ->then(function (ContextLabel $contextLabel = null) use ($episode) {
                return Teaser::secondary(
                    $episode['title'],
                    $this->urlGenerator->generate('podcast-episode', ['number' => $episode['number']]),
                    'Episode '.$episode['number'],
                    $contextLabel,
                    TeaserImage::small(
                        $episode['image']['sizes']['1:1'][70],
                        $episode['image']['alt'],
                        $this->urlGenerator->generate('podcast-episode', ['number' => $episode['number']]),
                        [
                            140 => $episode['image']['sizes']['1:1'][140],
                            70 => $episode['image']['sizes']['1:1'][70],
                        ]
                    ),
                    TeaserFooter::forNonArticle(
                        Meta::withText(
                            'Podcast',
                            new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $episode['published']))
                        ),
                        $episode['mp3']
                    )
                );
            });
    }
}
