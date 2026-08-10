<?php

namespace test\eLife\Journal\ViewModel\Converter;

use ComposerLocator;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\Highlight;
use eLife\Journal\ViewModel\Converter\HighlightEventSecondaryTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use ReflectionClass;
use Traversable;

final class HighlightEventSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'secondary'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new HighlightEventSecondaryTeaserConverter(
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
     * No highlight-list sample fixture currently contains an event item, so build one directly
     * instead of going through the shared findSamples()/modelHook() fixture pipeline.
     */
    public static function samples() : Traversable
    {
        $instance = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();

        $event = self::denormalizeSample(
            ComposerLocator::getPath('elife/api').'/dist/samples/event/v2/complete.json',
            Event::class
        );

        $highlight = new Highlight('Highlight title', null, $event, 'Highlight impact statement.');

        foreach ($instance->viewModelClasses as $viewModelClass) {
            yield [$highlight, $viewModelClass];
        }
    }
}
