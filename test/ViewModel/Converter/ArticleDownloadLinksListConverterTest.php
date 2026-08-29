<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\ArticleDownloadLinksListConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\HttpFoundation\UriSigner;

final class ArticleDownloadLinksListConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ViewModel\ArticleDownloadLinksList::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ArticleDownloadLinksListConverter(
            $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class),
            $this->stubUrlGenerator(),
            new DownloadLinkUriGenerator($this->stubUrlGenerator(), new UriSigner('secret'))
        );
    }
}
