<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\CoverEventHeroBannerConverter;
use eLife\Patterns\ViewModel\HeroBanner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;

final class CoverEventHeroBannerConverterTest extends ModelConverterTestCase
{
    protected $models = ['cover'];
    protected $viewModelClasses = [HeroBanner::class];

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

        $this->converter = new CoverEventHeroBannerConverter(
            $urlGenerator
        );
    }

    /**
     * @param Cover $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof Event) {
            yield $model;
        }
    }
}
