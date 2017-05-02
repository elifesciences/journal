<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\PodcastEpisodeContentHeaderConverter;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel\ContentHeader;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpKernel\UriSigner;

final class PodcastEpisodeContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $viewModelClasses = [ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeContentHeaderConverter($this->stubUrlGenerator(), new DownloadLinkUriGenerator($this->stubUrlGenerator(), new UriSigner('secret')), new ContentHeaderImageFactory(new Packages()));
    }
}
