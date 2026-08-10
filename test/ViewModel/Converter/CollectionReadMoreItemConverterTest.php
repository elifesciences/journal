<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Journal\ViewModel\Converter\CollectionReadMoreItemConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\ReadMoreItem;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CollectionReadMoreItemConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $viewModelClasses = [ReadMoreItem::class];

    #[Before]
    public function setUpConverter(): void
    {
        $patternRenderer = $this->createMock(PatternRenderer::class);
        $patternRenderer
            ->method('render')
            ->willReturn('...');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturn('/');

        $this->converter = new CollectionReadMoreItemConverter($patternRenderer, $urlGenerator);
    }
}
