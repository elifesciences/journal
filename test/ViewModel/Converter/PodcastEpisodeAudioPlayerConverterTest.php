<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PodcastEpisodeAudioPlayerConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\MediaChapterListingItem;

final class PodcastEpisodeAudioPlayerConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $viewModelClasses = [AudioPlayer::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->viewModelConverter = $this->createMock(ViewModelConverter::class);
        $this->viewModelConverter->expects($this->any())
            ->method('convert')
            ->will($this->returnValue($this->aMediaChapterListingItem()));

        $this->converter = new PodcastEpisodeAudioPlayerConverter($this->viewModelConverter, $this->stubUrlGenerator());
    }

    private function aMediaChapterListingItem()
    {
        return new MediaChapterListingItem('1', 0, 1);
    }
}
