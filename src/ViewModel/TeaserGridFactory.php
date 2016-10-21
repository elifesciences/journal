<?php

namespace eLife\Journal\ViewModel;

use DateTimeImmutable;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use eLife\Patterns\ViewModel\TeaserImage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TeaserGridFactory
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function forExperiment(array $experiment) : Teaser
    {
        return Teaser::withGrid(
            $experiment['title'],
            $this->urlGenerator->generate('labs-experiment', ['number' => $experiment['number']]),
            $experiment['impactStatement'] ?? null,
            null,
            TeaserImage::prominent(
                $experiment['image']['thumbnail']['sizes']['16:9'][250],
                $experiment['image']['thumbnail']['alt'],
                [
                    500 => $experiment['image']['thumbnail']['sizes']['16:9'][500],
                    250 => $experiment['image']['thumbnail']['sizes']['16:9'][250],
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

    public function forPodcastEpisode(array $episode) : Teaser
    {
        return Teaser::withGrid(
            $episode['title'],
            $this->urlGenerator->generate('podcast-episode', ['number' => $episode['number']]),
            $episode['impactStatement'] ?? null,
            'Episode '.$episode['number'],
            TeaserImage::prominent(
                $episode['image']['thumbnail']['sizes']['16:9'][250],
                $episode['image']['thumbnail']['alt'],
                [
                    500 => $episode['image']['thumbnail']['sizes']['16:9'][500],
                    250 => $episode['image']['thumbnail']['sizes']['16:9'][250],
                ]
            ),
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new Link('Podcast', $this->urlGenerator->generate('podcast')),
                    new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $episode['published']))
                )
            )
        );
    }
}
