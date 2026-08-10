<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PodcastEpisodeAudioPlayerConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\MediaChapterListingItem;
use PHPUnit\Framework\Attributes\Before;

final class PodcastEpisodeAudioPlayerConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $viewModelClasses = [AudioPlayer::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->viewModelConverter = $this->createMock(ViewModelConverter::class);
        $this->viewModelConverter
            ->method('convert')
            ->willReturn($this->aMediaChapterListingItem());

        $this->converter = new PodcastEpisodeAudioPlayerConverter($this->viewModelConverter, $this->stubUrlGenerator());
    }

    private function aMediaChapterListingItem()
    {
        return new MediaChapterListingItem('1', 0, 1);
    }
}
