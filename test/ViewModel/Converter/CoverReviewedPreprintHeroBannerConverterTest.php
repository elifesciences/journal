<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\Journal\ViewModel\Converter\CoverReviewedPreprintHeroBannerConverter;
use eLife\Patterns\ViewModel\HeroBanner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;

final class CoverReviewedPreprintHeroBannerConverterTest extends ModelConverterTestCase
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

        $this->converter = new CoverReviewedPreprintHeroBannerConverter(
            $urlGenerator
        );
    }

    /**
     * @param Cover $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof ReviewedPreprint) {
            yield $model;
        }
    }
}
