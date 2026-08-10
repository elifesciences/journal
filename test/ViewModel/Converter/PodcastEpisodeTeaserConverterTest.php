<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PodcastEpisodeTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class PodcastEpisodeTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $viewModelClasses = [ViewModel\Teaser::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new PodcastEpisodeTeaserConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator()
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            ));
    }
}
