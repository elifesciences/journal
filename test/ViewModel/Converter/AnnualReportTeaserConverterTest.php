<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\AnnualReportTeaserConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpKernel\UriSigner;

final class AnnualReportTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['annual-report'];
    protected $viewModelClasses = [Teaser::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $patternRenderer = $this->createMock(PatternRenderer::class);
        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));

        $this->converter = new AnnualReportTeaserConverter($patternRenderer, new DownloadLinkUriGenerator($this->stubUrlGenerator(), new UriSigner('secret')));
    }
}
