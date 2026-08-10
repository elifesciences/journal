<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\Journal\ViewModel\Converter\CoverReviewedPreprintHighlightItemConverter;
use eLife\Patterns\ViewModel\HighlightItem;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;

final class CoverReviewedPreprintHighlightItemConverterTest extends ModelConverterTestCase
{
    protected $models = ['cover'];
    protected $viewModelClasses = [HighlightItem::class];

    #[Before]
    public function setUpConverter(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturn('/');

        $this->converter = new CoverReviewedPreprintHighlightItemConverter(
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
