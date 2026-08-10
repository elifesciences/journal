<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\CollectionRelatedItemTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class CollectionRelatedItemTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'relatedItem', 'from' => 'insight'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new CollectionRelatedItemTeaserConverter(
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
}
