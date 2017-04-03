<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\ArticleDownloadLinksListConverter;
use eLife\Patterns\ViewModel\ArticleDownloadLinksList;
use Symfony\Component\HttpKernel\UriSigner;

final class ArticleDownloadLinksListConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ArticleDownloadLinksList::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleDownloadLinksListConverter(
            $this->stubUrlGenerator(),
            new DownloadLinkUriGenerator($this->stubUrlGenerator(), new UriSigner('secret'))
        );
    }
}
