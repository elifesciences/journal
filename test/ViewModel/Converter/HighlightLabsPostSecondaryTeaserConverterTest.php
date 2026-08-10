<?php

namespace test\eLife\Journal\ViewModel\Converter;

use ComposerLocator;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\LabsPost;
use eLife\Journal\ViewModel\Converter\HighlightLabsPostSecondaryTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use ReflectionClass;
use Traversable;

final class HighlightLabsPostSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'secondary'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new HighlightLabsPostSecondaryTeaserConverter(
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
     * No highlight-list sample fixture currently contains a labs-post item, so build one directly
     * instead of going through the shared findSamples()/modelHook() fixture pipeline.
     */
    public static function samples() : Traversable
    {
        $instance = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();

        $labsPost = self::denormalizeSample(
            ComposerLocator::getPath('elife/api').'/dist/samples/labs-post/v2/complete.json',
            LabsPost::class
        );

        $highlight = new Highlight('Highlight title', null, $labsPost, 'Highlight impact statement.');

        foreach ($instance->viewModelClasses as $viewModelClass) {
            yield [$highlight, $viewModelClass];
        }
    }
}
