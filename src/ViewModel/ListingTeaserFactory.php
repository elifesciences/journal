<?php

namespace eLife\Journal\ViewModel;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\SubjectsClient;
use eLife\ApiSdk\MediaType;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\ContextLabel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use eLife\Patterns\ViewModel\TeaserImage;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Uri;
use Puli\UrlGenerator\Api\UrlGenerator as PuliUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UnexpectedValueException;
use function GuzzleHttp\Promise\all;

final class ListingTeaserFactory
{
    private $urlGenerator;
    private $puliUrlGenerator;
    private $subjects;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        PuliUrlGenerator $puliUrlGenerator,
        SubjectsClient $subjects
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->puliUrlGenerator = $puliUrlGenerator;
        $this->subjects = $subjects;
    }

    public function forResult(Result $result, string $heading = null) : PromiseInterface
    {
        $teasers = [];
        foreach ($result['items'] as $item) {
            $teasers[] = $this->createTeaser($item);
        }

        return all($teasers)
            ->then(function (array $teasers) use ($heading) {
                return ListingTeasers::basic($teasers, $heading);
            });
    }

    private function createTeaser(array $item): PromiseInterface
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
            case 'labs-experiment':
                return $this->teaserForLabsExperiment($item);
            case 'podcast-episode':
                return $this->teaserForPodcastEpisode($item);
        }

        throw new UnexpectedValueException('Unknown type '.$type);
    }

    private function teaserForArticle(array $article) : PromiseInterface
    {
        if (false === empty($article['image'])) {
            $image = TeaserImage::big(
                $article['image']['sizes']['16:9'][250],
                $article['image']['alt'],
                null,
                [
                    500 => $article['image']['sizes']['16:9'][500],
                    250 => $article['image']['sizes']['16:9'][250],
                ]
            );
        } else {
            $image = null;
        }

        return $this->createContextLabel($article)
            ->then(function ($contextLabel) use ($article, $image) {
                return Teaser::main(
                    $article['title'],
                    null,
                    $article['impactStatement'] ?? null,
                    null,
                    $contextLabel,
                    $image,
                    TeaserFooter::forArticle(
                        Meta::withText(
                            ucfirst(str_replace('-', ' ', $article['type'])),
                            new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $article['published']))
                        ),
                        'vor' === $article['status'],
                        (new Uri($this->puliUrlGenerator->generateUrl('/elife/patterns/assets')))->withQuery('')
                    )
                );
            })
            ;
    }

    private function teaserForBlogArticle(array $article) : PromiseInterface
    {
        return new FulfilledPromise(Teaser::main(
            $article['title'],
            null,
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
        ));
    }

    private function teaserForCollection(array $collection) : PromiseInterface
    {
        return $this->createContextLabel($collection)
            ->then(function ($contextLabel) use ($collection) {
                return Teaser::main(
                    $collection['title'],
                    null,
                    $collection['impactStatement'] ?? null,
                    null,
                    $contextLabel,
                    TeaserImage::big(
                        $collection['image']['sizes']['16:9'][250],
                        $collection['image']['alt'],
                        null,
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
            })
            ;
    }

    private function teaserForLabsExperiment(array $experiment) : PromiseInterface
    {
        return new FulfilledPromise(Teaser::main(
            $experiment['title'],
            $this->urlGenerator->generate('labs-experiment', ['number' => $experiment['number']]),
            $experiment['impactStatement'] ?? null,
            null,
            null,
            TeaserImage::big(
                $experiment['image']['sizes']['16:9'][250],
                $experiment['image']['alt'],
                $this->urlGenerator->generate('labs-experiment', ['number' => $experiment['number']]),
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
        ));
    }

    private function teaserForPodcastEpisode(array $episode) : PromiseInterface
    {
        return $this->createContextLabel($episode)
            ->then(function ($contextLabel) use ($episode) {
                return Teaser::main(
                    $episode['title'],
                    null,
                    $episode['impactStatement'] ?? null,
                    null,
                    $contextLabel,
                    TeaserImage::big(
                        $episode['image']['sizes']['16:9'][250],
                        $episode['image']['alt'],
                        null,
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
            })
            ;
    }

    private function createContextLabel(array $item) : PromiseInterface
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
