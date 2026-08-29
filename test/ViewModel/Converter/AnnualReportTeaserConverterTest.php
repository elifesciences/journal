<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\AnnualReportTeaserConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\Teaser;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\HttpFoundation\UriSigner;

final class AnnualReportTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['annual-report'];
    protected $viewModelClasses = [Teaser::class];

    #[Before]
    public function setUpConverter(): void
    {
        $patternRenderer = $this->createMock(PatternRenderer::class);
        $patternRenderer
            ->method('render')
            ->willReturn('...');

        $this->converter = new AnnualReportTeaserConverter($patternRenderer, new DownloadLinkUriGenerator($this->stubUrlGenerator(), new UriSigner('secret')));
    }
}
