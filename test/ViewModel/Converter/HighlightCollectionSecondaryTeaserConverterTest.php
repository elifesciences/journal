<?php

namespace test\eLife\Journal\ViewModel\Converter;

use ComposerLocator;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Highlight;
use eLife\Journal\ViewModel\Converter\HighlightCollectionSecondaryTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use ReflectionClass;
use Traversable;

final class HighlightCollectionSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'secondary'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new HighlightCollectionSecondaryTeaserConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator()
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            ));
    }

    /**
     * No highlight-list sample fixture currently contains a collection item, so build one directly
     * instead of going through the shared findSamples()/modelHook() fixture pipeline.
     */
    public static function samples() : Traversable
    {
        $instance = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();

        $collection = self::denormalizeSample(
            ComposerLocator::getPath('elife/api').'/dist/samples/collection/v2/complete.json',
            Collection::class
        );

        $highlight = new Highlight('Highlight title', null, $collection, 'Highlight impact statement.');

        foreach ($instance->viewModelClasses as $viewModelClass) {
            yield [$highlight, $viewModelClass];
        }
    }
}
