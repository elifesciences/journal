<?php

namespace eLife\Journal\ViewModel;

use DateTimeImmutable;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\TeaserNonArticleContent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TeaserNonArticleContentFactory
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function forExperiment(array $experiment) : TeaserNonArticleContent
    {
        return new TeaserNonArticleContent(
            $experiment['impactStatement'] ?? 'Nothing yet',
            new Date(new DateTimeImmutable($experiment['published'])),
            $experiment['title'],
            $this->urlGenerator->generate('labs-experiment', ['number' => $experiment['number']]),
            null,
            new Image(
                $experiment['image']['sizes']['16:9'][250],
                [
                    500 => $experiment['image']['sizes']['16:9'][500],
                    250 => $experiment['image']['sizes']['16:9'][250],
                ],
                $experiment['image']['alt']
            ),
            'Experiment: '.str_pad($experiment['number'], 3, '0', STR_PAD_LEFT)
        );
    }
}
