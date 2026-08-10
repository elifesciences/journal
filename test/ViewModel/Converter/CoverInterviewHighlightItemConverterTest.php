<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\CoverCollectionHighlightItemConverter;
use eLife\Journal\ViewModel\Converter\CoverInterviewHighlightItemConverter;
use eLife\Patterns\ViewModel\HighlightItem;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;

final class CoverInterviewHighlightItemConverterTest extends ModelConverterTestCase
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

        $this->converter = new CoverInterviewHighlightItemConverter(
            $urlGenerator
        );
    }

    /**
     * @param Cover $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof Interview) {
            yield $model;
        }
    }
}
