<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Journal\ViewModel\Converter\CollectionReadMoreItemConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\ReadMoreItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CollectionReadMoreItemConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $class = Collection::class;
    protected $viewModelClass = ReadMoreItem::class;

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

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue('/'));

        $this->converter = new CollectionReadMoreItemConverter($patternRenderer, $urlGenerator, $this->stubSlugify());
    }
}
