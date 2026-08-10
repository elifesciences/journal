<?php

namespace test\eLife\Journal\ViewModel\Converter;

use ComposerLocator;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Interview;
use eLife\Journal\ViewModel\Converter\HighlightInterviewSecondaryTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use ReflectionClass;
use Traversable;

final class HighlightInterviewSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'secondary'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new HighlightInterviewSecondaryTeaserConverter(
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
     * No highlight-list sample fixture currently contains an interview item, so build one directly
     * instead of going through the shared findSamples()/modelHook() fixture pipeline.
     */
    public static function samples() : Traversable
    {
        $instance = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();

        $interview = self::denormalizeSample(
            ComposerLocator::getPath('elife/api').'/dist/samples/interview/v2/complete.json',
            Interview::class
        );

        $highlight = new Highlight('Highlight title', null, $interview, 'Highlight impact statement.');

        foreach ($instance->viewModelClasses as $viewModelClass) {
            yield [$highlight, $viewModelClass];
        }
    }
}
