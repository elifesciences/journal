<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\CoverCollectionCarouselItemConverter;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel\CarouselItem;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;

final class CoverCollectionCarouselItemConverterTest extends ModelConverterTestCase
{
    protected $models = ['cover'];
    protected $viewModelClasses = [CarouselItem::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue('/'));

        $this->converter = new CoverCollectionCarouselItemConverter($urlGenerator, $this->stubSlugify(), new ContentHeaderImageFactory(new Packages()));
    }

    /**
     * @param Cover $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof Collection) {
            yield $model;
        }
    }
}
