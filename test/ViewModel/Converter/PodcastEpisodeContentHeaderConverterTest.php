<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\PodcastEpisodeContentHeaderConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\MediaChapterListingItem;
use eLife\Patterns\ViewModel\MediaSource;
use eLife\Patterns\ViewModel\MediaType;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\UriSigner;

final class PodcastEpisodeContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $viewModelClasses = [ContentHeader::class];

    #[Before]
    public function setUpConverter(): void
    {
        $viewModelConverter = $this->createMock(ViewModelConverter::class);
        $viewModelConverter
            ->method('convert')
            ->willReturn($this->anAudioPlayer());

        $this->converter = new PodcastEpisodeContentHeaderConverter(
            $viewModelConverter,
            $this->stubUrlGenerator(),
            new DownloadLinkUriGenerator($this->stubUrlGenerator(), new UriSigner('secret')),
            new ContentHeaderImageFactory(new PictureBuilderFactory(), new Packages())
        );
    }

    private function anAudioPlayer()
    {
        return new AudioPlayer(1, new Link('title'), [new MediaSource('src', new MediaType('audio/mp4'))], [new MediaChapterListingItem('1', 0, 1)]);
    }
}
