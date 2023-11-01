<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ReviewedPreprintReadMoreItemConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\ReadMoreItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ReviewedPreprintReadMoreItemConverterTest extends ModelConverterTestCase
{
    protected $models = ['reviewed-preprint'];
    protected $viewModelClasses = [ReadMoreItem::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $viewModelConverter = $this->createMock(ViewModelConverter::class);
        $viewModelConverter->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(new Paragraph('foo')));

        $patternRenderer = $this->createMock(PatternRenderer::class);
        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue('/'));

        $this->converter = new ReviewedPreprintReadMoreItemConverter($viewModelConverter, $patternRenderer, $urlGenerator);
    }
}
