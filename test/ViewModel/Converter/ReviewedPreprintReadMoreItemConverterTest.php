<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ReviewedPreprintReadMoreItemConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\ReadMoreItem;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ReviewedPreprintReadMoreItemConverterTest extends ModelConverterTestCase
{
    protected $models = ['reviewed-preprint'];
    protected $viewModelClasses = [ReadMoreItem::class];

    #[Before]
    public function setUpConverter(): void
    {
        $viewModelConverter = $this->createMock(ViewModelConverter::class);
        $viewModelConverter
            ->method('convert')
            ->willReturn(new Paragraph('foo'));

        $patternRenderer = $this->createMock(PatternRenderer::class);
        $patternRenderer
            ->method('render')
            ->willReturn('...');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturn('/');

        $this->converter = new ReviewedPreprintReadMoreItemConverter($viewModelConverter, $patternRenderer, $urlGenerator);
    }
}
